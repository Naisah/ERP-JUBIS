<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Product extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'specs' => 'array',
        'in_stock' => 'boolean',
        'is_active' => 'boolean',
        'wholesale_price' => 'decimal:2',
    ];

    public function parent()
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    public function variants()
    {
        return $this->hasMany(Product::class, 'parent_id');
    }

    public function scopeStockItems($query)
    {
        return $query->where('is_active', true)->whereDoesntHave('variants');
    }

    public function scopeLowStock($query)
    {
        return $query->stockItems()->where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'reorder_level');
    }

    public function scopeOutOfStock($query)
    {
        return $query->stockItems()->where('stock_quantity', '<=', 0);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function getImagePathAttribute($value)
    {
        return $value ?? ($this->parent ? $this->parent->image_path : null);
    }

    protected static function booted()
    {
        static::updated(function ($product) {
            if ($product->isDirty('stock_quantity') && $product->stock_quantity <= ($product->reorder_level ?? 10) && $product->stock_quantity > 0) {
                // Check if there is already a pending PO for this product to avoid spam
                $existingPO = PurchaseOrder::where('status', 'draft')
                    ->whereHas('items', function ($q) use ($product) {
                        $q->where('product_id', $product->id);
                    })->exists();

                if (! $existingPO) {
                    // Find a supplier who carries this brand
                    $supplier = Supplier::where('brands_carried', 'like', '%'.$product->brand.'%')->first();
                    if ($supplier) {
                        $po = PurchaseOrder::create([
                            'supplier_id' => $supplier->id,
                            'status' => 'draft',
                            'total_cost' => Money::decimal(Money::cents($product->wholesale_price) * 50),
                        ]);
                        PurchaseOrderItem::create([
                            'purchase_order_id' => $po->id,
                            'product_id' => $product->id,
                            'quantity' => 50,
                            'unit_cost' => $product->wholesale_price,
                        ]);
                        Log::info("Automation: Draft PO #{$po->id} generated for low stock product: {$product->name}");
                    }
                }
            }
        });
    }
}
