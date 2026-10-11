<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\RMA;
use App\Models\RMAItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\CompanySettings;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class QuoteController extends Controller
{
    // 1. View the current Quote Cart
    public function cart(Request $request)
    {
        $quote = Quote::with('items.product')
            ->where('user_id', auth()->id())
            ->where('status', 'draft')
            ->first();

        return Inertia::render('Cart', [
            'cart' => $quote,
        ]);
    }

    // 2. Add an item to the Quote Cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($request) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $product = Product::whereKey($request->product_id)->lockForUpdate()->firstOrFail();
            abort_unless($product->is_active && ! $product->variants()->exists(), 422, 'This product is not available to order.');

            if ($request->quantity > $product->stock_quantity) {
                return redirect()->back()->with('error', 'Cannot add more than available stock ('.$product->stock_quantity.' available).');
            }

            // Find or create a draft quote for the logged-in user
            $quote = Quote::firstOrCreate(
                ['user_id' => auth()->id(), 'status' => 'draft'],
                ['total_amount' => 0]
            );

            // Check if item already exists in cart
            $item = QuoteItem::where('quote_id', $quote->id)
                ->where('product_id', $product->id)
                ->first();

            if ($item) {
                $newQuantity = $item->quantity + $request->quantity;
                if ($newQuantity > $product->stock_quantity) {
                    return redirect()->back()->with('error', 'Cannot add more than available stock ('.$product->stock_quantity.' available).');
                }
                $item->quantity = $newQuantity;
                $item->save();
            } else {
                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'quoted_price' => $product->wholesale_price,
                ]);
            }

            $this->updateTotal($quote);

            return redirect()->back()->with('success', 'Added to quote cart.');
        });
    }

    // 3. Remove an item from the Quote Cart
    public function remove(Request $request, $itemId)
    {
        return DB::transaction(function () use ($itemId) {
            User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
            $item = QuoteItem::whereHas('quote', function ($q) {
                $q->where('user_id', auth()->id())->where('status', 'draft');
            })->findOrFail($itemId);

            $quote = $item->quote;
            $item->delete();

            $this->updateTotal($quote);

            return redirect()->back()->with('success', 'Item removed.');
        });
    }

    // 4. Submit the quote for admin review and securely reserve inventory
    public function submit(Request $request)
    {
        $quote = Quote::where('user_id', auth()->id())
            ->where('status', 'draft')
            ->firstOrFail();

        if ($quote->items()->count() === 0) {
            return redirect()->back()->with('error', 'Cannot submit an empty quote request.');
        }

        try {
            DB::transaction(function () use ($quote, $request) {
                User::whereKey(auth()->id())->lockForUpdate()->firstOrFail();
                $quote = Quote::whereKey($quote->id)->lockForUpdate()->firstOrFail();
                if ($quote->status !== 'draft') {
                    throw new \RuntimeException('This quote has already been submitted.');
                }
                // Lock the products being purchased to prevent race conditions
                $productIds = $quote->items()->pluck('product_id')->toArray();
                $products = Product::whereIn('id', $productIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                foreach ($quote->items as $item) {
                    $product = $products->get($item->product_id);
                    if (! $product || ! $product->is_active || $product->variants()->exists()) {
                        throw new \RuntimeException('An item in this quote is no longer available.');
                    }
                    if ($product->stock_quantity < $item->quantity) {
                        throw new \Exception("Insufficient stock for {$product->name}. Only {$product->stock_quantity} available.");
                    }
                    // Deduct the inventory (reserve it)
                    $product->decrement('stock_quantity', $item->quantity);

                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'quantity' => -$item->quantity, // Negative for sale/reservation
                        'type' => 'sale',
                        'reference_id' => 'QUOTE-'.$quote->id,
                        'notes' => 'Reserved for pending quote',
                    ]);
                }

                $quote->update([
                    'status' => 'pending',
                    'client_notes' => $request->input('client_notes'),
                    'shipping_address' => $request->input('shipping_address'),
                ]);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->route('products.index')->with('success', 'Your quote request has been submitted and inventory has been reserved!');
    }

    // Helper to recalculate total
    private function updateTotal(Quote $quote)
    {
        $subtotal = $quote->items()->get()->sum(fn ($item) => $item->quantity * Money::cents($item->quoted_price));
        $rate = app(CompanySettings::class)->all()['vat_rate'];
        $vat = intdiv($subtotal * Money::cents($rate) + 5000, 10000);

        $quote->update([
            'subtotal' => Money::decimal($subtotal),
            'vat_amount' => Money::decimal($vat),
            'vat_rate' => $rate,
            'total_amount' => Money::decimal($subtotal + $vat),
        ]);
    }

    public function requestRMA(Request $request, $invoiceId)
    {
        return DB::transaction(function () use ($invoiceId) {
            $invoice = Invoice::with('quote.items')->where('id', $invoiceId)->where('user_id', auth()->id())->lockForUpdate()->firstOrFail();

            if ($invoice->status !== 'paid' && $invoice->status !== 'on_terms') {
                return redirect()->back()->with('error', 'Only paid or active credit invoices can be returned.');
            }

            $existing = RMA::where('invoice_id', $invoiceId)->first();
            if ($existing) {
                return redirect()->back()->with('error', 'An RMA request already exists for this order.');
            }

            $rma = RMA::create([
                'user_id' => auth()->id(),
                'invoice_id' => $invoice->id,
                'status' => 'pending',
                'reason' => 'Client requested return via dashboard',
                'refund_amount' => 0,
            ]);

            foreach ($invoice->quote->items as $item) {
                RMAItem::create([
                    'rma_id' => $rma->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);
            }

            return redirect()->back()->with('success', 'RMA Request submitted successfully. Our team will review your return.');
        });
    }
}
