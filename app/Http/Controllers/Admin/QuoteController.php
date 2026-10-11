<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\StockMovement;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Quote::with(['user', 'items.product'])->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Quotes/Index', [
            'quotes' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Quote $quote)
    {
        $quote->load(['user', 'items.product']);

        return Inertia::render('Admin/Quotes/Show', [
            'quote' => $quote,
        ]);
    }

    public function update(Request $request, Quote $quote)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,reviewed,approved,rejected',
        ]);

        DB::transaction(function () use ($quote, $validated) {
            $quote = Quote::whereKey($quote->id)->lockForUpdate()->firstOrFail();
            if ($quote->status === $validated['status']) {
                return;
            }
            abort_unless(in_array($quote->status, ['pending', 'reviewed'], true), 409, 'Approved and rejected quotes cannot be reopened.');
            if ($validated['status'] === 'rejected') {
                $products = Product::withTrashed()->whereIn('id', $quote->items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
                foreach ($quote->items as $item) {
                    $products[$item->product_id]->increment('stock_quantity', $item->quantity);
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'quantity' => $item->quantity,
                        'type' => 'adjustment',
                        'reference_id' => 'QUOTE-REJECT-'.$quote->id,
                        'notes' => 'Stock restored due to rejected quote',
                    ]);
                }
            }
            $quote->update(['status' => $validated['status']]);
            if ($validated['status'] === 'approved') {
                $invoice = Invoice::firstOrCreate(['quote_id' => $quote->id], [
                    'quote_id' => $quote->id,
                    'user_id' => $quote->user_id,
                    'subtotal' => $quote->subtotal,
                    'vat_amount' => $quote->vat_amount,
                    'vat_rate' => $quote->vat_rate,
                    'total_amount' => $quote->total_amount,
                    'amount_paid' => 0,
                    'status' => 'unpaid',
                    'due_date' => now()->timezone(config('company.timezone'))->addDays(30)->toDateString(),
                    'billing_address' => $quote->user->billing_address ?? 'Not specified',
                ]);
                app(PayMongoService::class)->syncPaymentLink($invoice);
            }
        });

        return redirect()->back()->with('success', 'Quote status updated.');
    }
}
