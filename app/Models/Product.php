<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function parent() { return $this->belongsTo(Product::class, 'parent_id'); }

    public function variants() { return $this->hasMany(Product::class, 'parent_id'); }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}


