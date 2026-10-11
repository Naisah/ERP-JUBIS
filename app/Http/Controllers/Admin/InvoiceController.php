<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoicePayments;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['user', 'quote'])->latest();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['user', 'quote.items.product', 'shipments']);

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function updatePayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount_paid' => 'required|numeric|decimal:0,2|gt:0|max:9999999999.99',
            'payment_token' => 'nullable|uuid',
        ]);
        DB::transaction(function () use ($request, $invoice) {
            $locked = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            app(InvoicePayments::class)->record(
                $locked, Money::cents($request->amount_paid), 'manual',
                'manual:'.($request->payment_token ?? Str::uuid()), $request->user()->id
            );
            // An existing checkout amount is stale after a partial/manual payment.
            $locked->update(['payment_url' => null, 'payment_reference_id' => null]);
        });

        return redirect()->back()->with('success', 'Payment logged successfully.');
    }

    public function acceptTerms(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);

        return DB::transaction(function () use ($invoice) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if ($invoice->status === 'on_terms') {
                return back()->with('success', 'Credit terms are already active.');
            }
            abort_unless($invoice->status === 'unpaid' && Money::cents($invoice->amount_paid) === 0, 409, 'Credit terms cannot reopen a paid or partially paid invoice.');
            abort_if($invoice->quote->items->sum('quantity') < 1000, 400, 'Not eligible for credit terms.');
            $invoice->update(['status' => 'on_terms', 'due_date' => now()->timezone(config('company.timezone'))->addDays(30)->toDateString()]);

            return back()->with('success', 'Credit Terms (Net-30) successfully activated.');
        });
    }
}
