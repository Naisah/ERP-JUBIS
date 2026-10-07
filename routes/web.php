<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

Route::get('/products', function (Request $request) {
    $query = Product::with(['category', 'variants'])->where('is_active', true)->whereNull('parent_id');

    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('sku', 'like', '%' . $request->search . '%')
              ->orWhere('brand', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->filled('categories')) {
        $categoryIds = explode(',', $request->categories);
        $query->whereIn('category_id', $categoryIds);
    }
    
    if ($request->filled('brands')) {
        $brands = explode(',', $request->brands);
        $query->whereIn('brand', $brands);
    }

    $sort = $request->input('sort', 'relevant');
    if ($sort === 'name') {
        $query->orderBy('name');
    } elseif ($sort === 'brand') {
        $query->orderBy('brand')->orderBy('name');
    }
    $query->orderBy('id');

    return Inertia::render('Products/Index', [
        'products' => $query->paginate(12)->withQueryString(),
        'categories' => Category::all(),
        'brands' => Product::where('is_active', true)
            ->select('brand')
            ->distinct()
            ->orderBy('brand')
            ->pluck('brand')
            ->sortBy(function($brand) {
                if ($brand === 'OMNI') return -2;
                if ($brand === 'Jubis') return -1;
                return 0;
            })->values(),
        'filters' => $request->only(['search', 'categories', 'brands', 'sort'])
    ]);
})->name('products.index');

Route::get('/products/{id}', function ($id) {
    $product = Product::with(['category', 'variants'])->findOrFail($id);
    $related = Product::whereNull('parent_id')->where('id', '!=', $id)->inRandomOrder()->take(4)->get();
    return Inertia::render('Products/Show', [
        'product' => $product,
        'relatedProducts' => $related
    ]);
})->name('products.show');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::post('/contact', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'company' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:50',
        'message' => 'required|string',
    ]);
    \Illuminate\Support\Facades\Mail::to('naisahaspiras24@gmail.com')->send(new \App\Mail\ContactMessageMail($validated));
    return back()->with('success', 'Your message has been sent successfully. We will get back to you shortly.');
})->name('contact.submit');

Route::get('/dashboard', function () {
    if (auth()->user()->role !== 'client') {
        return redirect()->route('admin.dashboard');
    }
    
    $recentQuotes = \App\Models\Quote::with(['items.product', 'invoice.shipments'])
        ->where('user_id', auth()->id())
        ->where('status', '!=', 'draft') // Exclude active cart
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Dashboard', [
        'recentQuotes' => $recentQuotes
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// ADMIN ERP ROUTES
Route::prefix('admin')->middleware(['auth'])->group(function () {
    
    // Everyone with an admin-level role can see the dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
        ->middleware('role:super_admin,admin,finance,purchasing,warehouse,sales')
        ->name('admin.dashboard');

    // INVENTORY & LOGISTICS (Warehouse, Purchasing, Super Admin)
    Route::middleware('role:super_admin,admin,warehouse,purchasing')->group(function() {
        Route::get('inventory-export', [\App\Http\Controllers\Admin\ProductController::class, 'exportCsv'])->name('admin.products.export');
        Route::resource('inventory', \App\Http\Controllers\Admin\ProductController::class)
            ->parameters(['inventory' => 'product'])
            ->names('admin.products');
            
        Route::resource('shipments', \App\Http\Controllers\Admin\ShipmentController::class)->names('admin.shipments')->only(['index', 'store']);
        Route::post('shipments/{shipment}/status', [\App\Http\Controllers\Admin\ShipmentController::class, 'updateStatus'])->name('admin.shipments.status');
    });

    // PROCUREMENT (Purchasing, Super Admin)
    Route::middleware('role:super_admin,admin,purchasing')->group(function() {
        Route::resource('suppliers', \App\Http\Controllers\Admin\SupplierController::class)->names('admin.suppliers')->except(['create', 'edit', 'show']);
        Route::resource('purchase-orders', \App\Http\Controllers\Admin\PurchaseOrderController::class)->names('admin.purchase-orders')->except(['edit', 'update', 'destroy']);
        Route::post('purchase-orders/{purchase_order}/status', [\App\Http\Controllers\Admin\PurchaseOrderController::class, 'updateStatus'])->name('admin.purchase-orders.status');
    });

    // SALES & QUOTES (Sales, Finance, Super Admin)
    Route::middleware('role:super_admin,admin,sales,finance')->group(function() {
        Route::resource('quotes', \App\Http\Controllers\Admin\QuoteController::class)->names('admin.quotes');
    });

    // FINANCE & REPORTS (Finance, Super Admin)
    Route::middleware('role:super_admin,admin,finance')->group(function() {
        Route::resource('invoices', \App\Http\Controllers\Admin\InvoiceController::class)->names('admin.invoices')->only(['index', 'show']);
        Route::resource('rmas', \App\Http\Controllers\Admin\RMAController::class)->names('admin.rmas')->only(['index']);
        Route::post('invoices/{invoice}/payment', [\App\Http\Controllers\Admin\InvoiceController::class, 'updatePayment'])->name('admin.invoices.payment');
        
        Route::get('/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
    });

    // SYSTEM ADMIN (Super Admin Only)
    Route::middleware('role:super_admin,admin')->group(function() {
        Route::get('/settings', function () {
            return Inertia::render('Admin/Settings/Index', [
                'settings' => \Illuminate\Support\Facades\Cache::get('global_settings', [])
            ]);
        })->name('admin.settings');
        
        Route::post('/settings', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Cache::forever('global_settings', $request->all());
            return redirect()->back();
        })->name('admin.settings.update');

        Route::resource('users', \App\Http\Controllers\Admin\UserController::class)->names('admin.users')->only(['index', 'store', 'update', 'destroy']);
    });
});

Route::middleware('auth')->group(function () {
    // Cart / Quote Routes
    Route::get('/cart', [\App\Http\Controllers\QuoteController::class, 'cart'])->name('cart.index');
    Route::post('/cart/add', [\App\Http\Controllers\QuoteController::class, 'add'])->name('cart.add');
    Route::delete('/cart/{item}', [\App\Http\Controllers\QuoteController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/submit', [\App\Http\Controllers\QuoteController::class, 'submit'])->name('cart.submit');
    Route::post('/invoices/{id}/rma', [\App\Http\Controllers\QuoteController::class, 'requestRMA'])->name('cart.rma');

    Route::get('/invoices/{invoice}/pdf', function(\App\Models\Invoice $invoice) {
        if ($invoice->user_id !== auth()->id() && auth()->user()->role !== 'admin') {
            abort(403);
        }
        $invoice->load(['quote.items.product', 'user', 'shipments']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
        return $pdf->download('Jubis_Marketing_Invoice_' . str_pad($invoice->id, 5, '0', STR_PAD_LEFT) . '.pdf');
    })->name('invoices.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// API Webhooks & Automations
Route::post('/webhooks/paymongo', [\App\Http\Controllers\WebhookController::class, 'handlePayMongo']);
Route::get('/api/mock/payment/invoice/{id}', [\App\Http\Controllers\WebhookController::class, 'simulatePayment']);
Route::post('/api/mock/payment/process/invoice/{id}', [\App\Http\Controllers\WebhookController::class, 'processPayment']);

Route::post('/api/invoices/{id}/accept-terms', function ($id) {
    $invoice = \App\Models\Invoice::with('quote.items')->findOrFail($id);
    if ($invoice->user_id !== auth()->id()) abort(403);
    
    $totalQty = $invoice->quote->items->sum('quantity');
    if ($totalQty < 1000) abort(400, 'Not eligible for credit terms.');
    
    $invoice->update(['status' => 'on_terms']);
    
    // Create a 30-day due date from today
    $invoice->update(['due_date' => now()->addDays(30)]);
    
    return redirect()->back()->with('success', 'Credit Terms (Net-30) successfully activated. Your order is cleared for shipping!');
})->middleware('auth');


Route::get('/api/mock/tracking/{tracking}', function($tracking) { $shipment = \App\Models\Shipment::where('tracking_number', $tracking)->first(); return view('mock-tracking', ['tracking' => $tracking, 'shipments' => $shipment]); });





