<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with('supplier')->latest();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $pos = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/PurchaseOrders/Index', [
            'purchaseOrders' => $pos,
            'filters' => $request->only(['status']),
        ]);
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        // Just eager load minimal product info for dropdowns
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'wholesale_price', 'stock_quantity', 'unit_of_measure']);

        return Inertia::render('Admin/PurchaseOrders/Create', [
            'suppliers' => $suppliers,
            'products' => $products,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|decimal:0,2|min:0|max:9999999999.99',
        ]);

        DB::transaction(function () use ($validated) {
            $po = PurchaseOrder::create([
                'supplier_id' => $validated['supplier_id'],
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'draft',
                'total_cost' => 0,
            ]);

            $total = 0;
            foreach ($validated['items'] as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                ]);
                $total += ($item['quantity'] * Money::cents($item['unit_cost']));
            }

            $po->update(['total_cost' => Money::decimal($total)]);
        });

        return redirect()->route('admin.purchase-orders.index')->with('success', 'Purchase Order created successfully.');
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'items.product']);

        return Inertia::render('Admin/PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $request->validate([
            'status' => 'required|in:draft,ordered,partially_received,received',
        ]);

        DB::transaction(function () use ($purchaseOrder, $request) {
            $purchaseOrder = PurchaseOrder::whereKey($purchaseOrder->id)->lockForUpdate()->firstOrFail();
            $products = Product::whereIn('id', $purchaseOrder->items->pluck('product_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($purchaseOrder->status === 'received' && $request->status !== 'received') {
                foreach ($purchaseOrder->items->groupBy('product_id') as $productId => $items) {
                    if (! $products->has($productId) || $products[$productId]->stock_quantity < $items->sum('quantity')) {
                        throw ValidationException::withMessages(['status' => 'Cannot reverse receipt: received stock has already been used.']);
                    }
                }
            }
            // If transitioning TO received from a non-received state, increment stock
            if ($request->status === 'received' && $purchaseOrder->status !== 'received') {
                foreach ($purchaseOrder->items as $item) {
                    $products[$item->product_id]->increment('stock_quantity', $item->quantity);
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'quantity' => $item->quantity,
                        'type' => 'purchase',
                        'reference_id' => 'PO-'.$purchaseOrder->id,
                        'notes' => 'Received from Purchase Order',
                    ]);
                }
            }

            // If transitioning FROM received to a non-received state (reversing), decrement stock
            if ($purchaseOrder->status === 'received' && $request->status !== 'received') {
                foreach ($purchaseOrder->items as $item) {
                    $products[$item->product_id]->decrement('stock_quantity', $item->quantity);
                    StockMovement::create([
                        'product_id' => $item->product_id,
                        'user_id' => auth()->id(),
                        'quantity' => -$item->quantity, // Negative for removal
                        'type' => 'adjustment',
                        'reference_id' => 'PO-'.$purchaseOrder->id,
                        'notes' => 'PO Status Reversal',
                    ]);
                }
            }

            $purchaseOrder->update(['status' => $request->status]);
        });

        return redirect()->back()->with('success', 'PO status updated. Inventory synced automatically.');
    }
}
