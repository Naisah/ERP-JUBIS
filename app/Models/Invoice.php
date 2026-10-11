<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected $appends = ['balance_due'];

    public function getBalanceDueAttribute(): string
    {
        return Money::decimal(max(0, Money::cents($this->total_amount) - Money::cents($this->amount_paid)));
    }

    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
