<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Product;
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
            'cart' => $quote
        ]);
    }

    // 2. Add an item to the Quote Cart
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $product = Product::findOrFail($request->product_id);

        if ($request->quantity > $product->stock_quantity) {
            return redirect()->back()->with('error', 'Cannot add more than available stock (' . $product->stock_quantity . ' available).');
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
                return redirect()->back()->with('error', 'Cannot add more than available stock (' . $product->stock_quantity . ' available).');
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
    }

    // 3. Remove an item from the Quote Cart
    public function remove(Request $request, $itemId)
    {
        $item = QuoteItem::whereHas('quote', function($q) {
            $q->where('user_id', auth()->id())->where('status', 'draft');
        })->findOrFail($itemId);

        $quote = $item->quote;
        $item->delete();

        $this->updateTotal($quote);

        return redirect()->back()->with('success', 'Item removed.');
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
            \Illuminate\Support\Facades\DB::transaction(function () use ($quote, $request) {
                // Lock the products being purchased to prevent race conditions
                $productIds = $quote->items()->pluck('product_id')->toArray();
                $products = Product::whereIn('id', $productIds)->lockForUpdate()->get()->keyBy('id');

                foreach ($quote->items as $item) {
                    $product = $products[$item->product_id];
                    if ($product->stock_quantity < $item->quantity) {
                        throw new \Exception("Insufficient stock for {$product->name}. Only {$product->stock_quantity} available.");
                    }
                    // Deduct the inventory (reserve it)
                    $product->decrement('stock_quantity', $item->quantity);

                    \App\Models\StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'quantity' => -$item->quantity, // Negative for sale/reservation
                        'type' => 'sale',
                        'reference_id' => 'QUOTE-' . $quote->id,
                        'notes' => 'Reserved for pending quote'
                    ]);
                }

                $quote->update([
                    'status' => 'pending', 
                    'client_notes' => $request->input('client_notes'), 
                    'shipping_address' => $request->input('shipping_address')
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
        $subtotal = $quote->items()->sum(\DB::raw('quantity * quoted_price'));
        $vat_amount = $subtotal * 0.12;
        $total = $subtotal + $vat_amount;
        
        $quote->update([
            'subtotal' => $subtotal,
            'vat_amount' => $vat_amount,
            'total_amount' => $total
        ]);
    }
}


