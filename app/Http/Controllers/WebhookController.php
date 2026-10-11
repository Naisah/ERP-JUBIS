<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoicePayments;
use App\Services\PayMongoService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handlePayMongo(Request $request)
    {
        $secret = config('services.paymongo.webhook_secret');
        $parts = [];
        foreach (explode(',', $request->header('Paymongo-Signature', '')) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            $parts[$key] = $value;
        }
        $timestamp = $parts['t'] ?? '';
        $live = $request->input('data.attributes.livemode');
        $signature = $parts[$live === true ? 'li' : 'te'] ?? '';
        if (! $secret || ! is_bool($live) || ! ctype_digit($timestamp) || abs(time() - (int) $timestamp) > 300
            || ! hash_equals(hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret), $signature)) {
            return response()->json(['error' => 'Invalid webhook signature'], 401);
        }
        $key = config('services.paymongo.secret_key');
        if ($key && $live !== str_starts_with($key, 'sk_live_')) {
            return response()->json(['error' => 'Webhook mode does not match gateway configuration'], 401);
        }
        if ($request->input('data.attributes.type') !== 'link.payment.paid') {
            return response()->json(['status' => 'ignored']);
        }
        $data = $request->validate([
            'data.id' => 'required|string|max:255',
            'data.attributes.data.id' => 'required|string|max:255',
            'data.attributes.data.attributes.amount' => 'required|integer|min:1',
            'data.attributes.data.attributes.currency' => 'required|in:PHP',
            'data.attributes.data.attributes.status' => 'required|in:paid',
        ]);
        $reference = data_get($data, 'data.attributes.data.id');
        $amount = (int) data_get($data, 'data.attributes.data.attributes.amount');

        return DB::transaction(function () use ($reference, $amount) {
            $link = DB::table('payment_links')->where('reference', $reference)->first();
            $query = $link ? Invoice::whereKey($link->invoice_id) : Invoice::where('payment_reference_id', $reference);
            $invoice = $query->lockForUpdate()->first();
            if (! $invoice) {
                Log::warning('Paid webhook has no matching invoice', ['reference' => $reference]);

                return response()->json(['status' => 'unmatched'], 202);
            }
            $posted = DB::table('payment_events')->where('reference', 'paymongo:'.$reference)->first();
            $expected = Money::cents($link?->amount ?? $posted?->amount ?? $invoice->balance_due);
            abort_unless($amount === $expected, 422, 'Payment amount does not match its invoice checkout.');
            app(InvoicePayments::class)->record($invoice, $amount, 'paymongo', 'paymongo:'.$reference, null);

            return response()->json(['status' => 'success']);
        });
    }

    public function checkout(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);
        if (Money::cents($invoice->balance_due) === 0) {
            return redirect()->route('dashboard')->with('error', 'This invoice has already been paid.');
        }
        $success = DB::transaction(function () use ($invoice) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if (Money::cents($invoice->balance_due) === 0) {
                return false;
            }
            if (! $invoice->payment_url) {
                return app(PayMongoService::class)->syncPaymentLink($invoice);
            }

            return true;
        });
        $invoice->refresh();
        if (! $success) {
            return redirect()->route('dashboard')->with('error', 'Checkout is unavailable. Please contact the finance team.');
        }
        if (str_contains($invoice->payment_url, '/api/mock/payment/')) {
            abort_unless(config('services.paymongo.simulation'), 404);

            return redirect('/api/mock/payment/invoice/'.$invoice->id);
        }

        return redirect()->away($invoice->payment_url);
    }

    public function simulatePayment(Request $request, $id)
    {
        abort_unless(config('services.paymongo.simulation'), 404);
        $invoice = Invoice::findOrFail($id);
        abort_unless($invoice->user_id === $request->user()->id, 403);
        if (Money::cents($invoice->balance_due) === 0) {
            return redirect()->route('dashboard')->with('error', 'This invoice has already been paid and cannot be processed again.');
        }

        return view('mock-checkout', compact('invoice'));
    }

    public function processPayment(Request $request, $id)
    {
        abort_unless(config('services.paymongo.simulation'), 404);
        $invoice = Invoice::findOrFail($id);
        abort_unless($invoice->user_id === $request->user()->id, 403);
        $request->validate(['payment_method' => 'required|in:card,gcash,qrph', 'expected_balance' => 'nullable|numeric|decimal:0,2']);
        DB::transaction(function () use ($invoice, $request) {
            $invoice = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
            if (Money::cents($invoice->balance_due) === 0) {
                return;
            }
            if ($request->filled('expected_balance')) {
                abort_unless(Money::cents($request->expected_balance) === Money::cents($invoice->balance_due), 409, 'Invoice balance has changed. Please reopen checkout.');
            }
            app(InvoicePayments::class)->record(
                $invoice, Money::cents($invoice->balance_due), 'simulation',
                'simulation:'.$invoice->id.':'.Money::cents($invoice->total_amount), $request->user()->id
            );
        });

        return redirect()->route('dashboard')->with('success', 'Demo payment completed. No real money was charged.');
    }
}
