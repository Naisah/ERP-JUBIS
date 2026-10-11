<?php

namespace App\Services;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoicePayments
{
    // The caller holds the invoice row lock in the surrounding transaction.
    public function record(Invoice $invoice, int $cents, string $source, string $reference, ?int $actor): void
    {
        $existing = DB::table('payment_events')->where('reference', $reference)->first();
        if ($existing) {
            if ($existing->invoice_id != $invoice->id || Money::cents($existing->amount) !== $cents) {
                throw ValidationException::withMessages(['amount_paid' => 'This payment reference has already been used.']);
            }

            return;
        }

        $balance = Money::cents($invoice->balance_due);
        if ($cents <= 0 || $cents > $balance) {
            throw ValidationException::withMessages(['amount_paid' => 'Payment must be positive and cannot exceed the remaining balance.']);
        }
        $paid = Money::cents($invoice->amount_paid) + $cents;
        DB::table('payment_events')->insert([
            'invoice_id' => $invoice->id, 'user_id' => $actor, 'source' => $source,
            'reference' => $reference, 'amount' => Money::decimal($cents), 'created_at' => now(),
        ]);
        $invoice->update([
            'amount_paid' => Money::decimal($paid),
            'status' => $paid === Money::cents($invoice->total_amount) ? 'paid' : 'partially_paid',
        ]);
    }
}
