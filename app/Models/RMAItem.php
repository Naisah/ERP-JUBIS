<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RMAItem extends Model
{
    protected $fillable = ['rma_id', 'product_id', 'quantity'];

    public function rma() { return $this->belongsTo(RMA::class, 'rma_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}
