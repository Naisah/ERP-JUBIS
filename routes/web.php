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

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\RMAController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\WebhookController;
use App\Mail\ContactMessageMail;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Shipment;
use App\Services\CompanySettings;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

Route::get('/products', function (Request $request) {
    $query = Product::with(['category', 'variants'])->where('is_active', true)->whereNull('parent_id');

    if ($request->filled('search')) {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'like', '%'.$request->search.'%')
                ->orWhere('sku', 'like', '%'.$request->search.'%')
                ->orWhere('brand', 'like', '%'.$request->search.'%');
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
            ->sortBy(function ($brand) {
                if ($brand === 'OMNI') {
                    return -2;
                }
                if ($brand === 'Jubis') {
                    return -1;
                }

                return 0;
            })->values(),
        'filters' => $request->only(['search', 'categories', 'brands', 'sort']),
    ]);
})->name('products.index');

Route::get('/products/{id}', function ($id) {
    $product = Product::with(['category', 'variants'])->findOrFail($id);
    $related = Product::whereNull('parent_id')->where('id', '!=', $id)->inRandomOrder()->take(4)->get();

    return Inertia::render('Products/Show', [
        'product' => $product,
        'relatedProducts' => $related,
    ]);
})->name('products.show');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::get('/contact', function () {
    return Inertia::render('Contact');
})->name('contact');

Route::post('/contact', function (Request $request) {
    $validated = $request->validate([
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'company' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:50',
        'message' => 'required|string',
    ]);
    try {
        Mail::to(config('company.contact_recipient'))->send(new ContactMessageMail($validated));
    } catch (Throwable $exception) {
        report($exception);

        return back()->with('error', 'Your message could not be sent. Please try again or use our contact email.')->withInput();
    }

    return back()->with('success', 'Your message was accepted for sending. We will get back to you shortly.');
})->middleware('throttle:5,1')->name('contact.submit');

Route::get('/dashboard', function () {
    if (auth()->user()->role !== 'client') {
        return redirect()->route('admin.dashboard');
    }

    $recentQuotes = Quote::with(['items.product', 'invoice.shipments'])
        ->where('user_id', auth()->id())
        ->where('status', '!=', 'draft') // Exclude active cart
        ->orderBy('created_at', 'desc')
        ->get();

    return Inertia::render('Dashboard', [
        'recentQuotes' => $recentQuotes,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

// ADMIN ERP ROUTES
Route::prefix('admin')->middleware(['auth'])->group(function () {

    // Everyone with an admin-level role can see the dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('role:super_admin,admin,finance,purchasing,warehouse,sales')
        ->name('admin.dashboard');

    // INVENTORY & LOGISTICS (Warehouse, Purchasing, Super Admin)
    Route::middleware('role:super_admin,admin,warehouse,purchasing')->group(function () {
        Route::get('inventory-export', [ProductController::class, 'exportCsv'])->name('admin.products.export');
        Route::resource('inventory', ProductController::class)
            ->except(['show'])
            ->parameters(['inventory' => 'product'])
            ->names('admin.products');

        Route::resource('shipments', ShipmentController::class)->names('admin.shipments')->only(['index', 'store']);
        Route::post('shipments/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('admin.shipments.status');
    });

    // PROCUREMENT (Purchasing, Super Admin)
    Route::middleware('role:super_admin,admin,purchasing')->group(function () {
        Route::resource('suppliers', SupplierController::class)->names('admin.suppliers')->except(['create', 'edit', 'show']);
        Route::resource('purchase-orders', PurchaseOrderController::class)->names('admin.purchase-orders')->except(['edit', 'update', 'destroy']);
        Route::post('purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])->name('admin.purchase-orders.status');
    });

    // SALES & QUOTES (Sales, Finance, Super Admin)
    Route::middleware('role:super_admin,admin,sales,finance')->group(function () {
        Route::resource('quotes', App\Http\Controllers\Admin\QuoteController::class)->names('admin.quotes')->only(['index', 'show', 'update']);
    });

    // FINANCE & REPORTS (Finance, Super Admin)
    Route::middleware('role:super_admin,admin,finance')->group(function () {
        Route::resource('invoices', InvoiceController::class)->names('admin.invoices')->only(['index', 'show']);
        Route::resource('rmas', RMAController::class)->names('admin.rmas')->only(['index']);
        Route::post('invoices/{invoice}/payment', [InvoiceController::class, 'updatePayment'])->name('admin.invoices.payment');

        Route::get('/reports', [ReportController::class, 'index'])->name('admin.reports.index');
    });

    // SYSTEM ADMIN (Super Admin Only)
    Route::middleware('role:super_admin,admin')->group(function () {
        Route::get('/settings', function () {
            return Inertia::render('Admin/Settings/Index', [
                'settings' => app(CompanySettings::class)->all(),
            ]);
        })->name('admin.settings');

        Route::post('/settings', function (Request $request) {
            $validated = $request->validate([
                'company_name' => 'sometimes|required|string|max:255',
                'company_address' => 'sometimes|required|string|max:1000',
                'contact_email' => 'sometimes|required|email|max:255',
                'contact_phone' => 'sometimes|required|string|max:100',
                'tax_id' => 'nullable|string|max:100',
                'vat_rate' => 'sometimes|required|numeric|decimal:0,2|between:0,100',
                'about_us_tagline' => 'sometimes|required|string|max:1000',
                'about_us_history' => 'sometimes|required|string|max:10000',
            ]);
            app(CompanySettings::class)->save($validated);

            return redirect()->back()->with('success', 'Settings saved successfully.');
        })->name('admin.settings.update');

        Route::resource('users', UserController::class)->names('admin.users')->only(['index', 'store', 'update', 'destroy']);
    });
});

Route::middleware('auth')->group(function () {
    // Cart / Quote Routes
    Route::get('/cart', [QuoteController::class, 'cart'])->name('cart.index');
    Route::post('/cart/add', [QuoteController::class, 'add'])->name('cart.add');
    Route::delete('/cart/{item}', [QuoteController::class, 'remove'])->name('cart.remove');
    Route::post('/cart/submit', [QuoteController::class, 'submit'])->name('cart.submit');
    Route::post('/invoices/{id}/rma', [QuoteController::class, 'requestRMA'])->name('cart.rma');

    Route::get('/invoices/{invoice}/pdf', function (Invoice $invoice) {
        if ($invoice->user_id !== auth()->id() && ! in_array(auth()->user()->role, ['super_admin', 'admin', 'finance'], true)) {
            abort(403);
        }
        $invoice->load(['quote.items.product', 'user', 'shipments']);
        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);

        return $pdf->download('Jubis_Marketing_Invoice_'.str_pad($invoice->id, 5, '0', STR_PAD_LEFT).'.pdf');
    })->name('invoices.pdf');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// API Webhooks & Automations
Route::post('/webhooks/paymongo', [WebhookController::class, 'handlePayMongo']);
Route::get('/api/mock/payment/invoice/{id}', [WebhookController::class, 'simulatePayment'])->middleware('auth');
Route::post('/api/mock/payment/process/invoice/{id}', [WebhookController::class, 'processPayment'])->middleware(['auth', 'throttle:10,1']);
Route::get('/invoices/{invoice}/checkout', [WebhookController::class, 'checkout'])->middleware(['auth', 'throttle:10,1'])->name('invoices.checkout');

Route::post('/api/invoices/{invoice}/accept-terms', [InvoiceController::class, 'acceptTerms'])->middleware('auth');

Route::get('/api/mock/tracking/{tracking}', function ($tracking) {
    abort_unless(config('services.lalamove.simulation'), 404);
    $shipment = Shipment::where('tracking_number', $tracking)->firstOrFail();

    return view('mock-tracking', compact('tracking', 'shipment'));
})->middleware('throttle:60,1');
