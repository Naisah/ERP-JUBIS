<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RMA extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = ['user_id', 'invoice_id', 'status', 'reason', 'refund_amount'];

    public function user() { return $this->belongsTo(User::class); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function items() { return $this->hasMany(RMAItem::class, 'rma_id'); }
}
