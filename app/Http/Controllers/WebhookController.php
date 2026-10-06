<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Handle PayMongo Webhook
     */
    public function handlePayMongo(Request $request)
    {
        $payload = $request->all();
        $signatureHeader = $request->header('Paymongo-Signature');
        
        Log::info('PayMongo Webhook Received: ', $payload);
        Log::info('Signature Header: ' . $signatureHeader);

        if ($signatureHeader && env('PAYMONGO_WEBHOOK_SECRET')) {
            $secret = env('PAYMONGO_WEBHOOK_SECRET');
            $parts = explode(',', $signatureHeader);
            $timestamp = str_replace('t=', '', $parts[0] ?? '');
            
            // Extract the test or live signature depending on the environment
            $signatureToMatch = '';
            foreach ($parts as $part) {
                if (str_starts_with($part, 'te=') || str_starts_with($part, 'li=')) {
                    $signatureToMatch = substr($part, 3);
                    break;
                }
            }

            $computedSignature = hash_hmac('sha256', $timestamp . '.' . $request->getContent(), $secret);

            if (!hash_equals($computedSignature, $signatureToMatch)) {
                Log::error('PayMongo Webhook Signature Verification Failed');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
        }

        $event = $payload['data']['attributes']['type'] ?? null;
        
        if ($event === 'link.payment.paid') {
            // PayMongo stores the link ID here in the webhook payload
            $referenceId = $payload['data']['attributes']['data']['id'] ?? null;
            
            if ($referenceId) {
                $invoice = Invoice::where('payment_reference_id', $referenceId)->first();
                if ($invoice && $invoice->status !== 'paid') {
                    $invoice->update([
                        'status' => 'paid',
                        'amount_paid' => $invoice->total_amount
                    ]);
                    Log::info("Invoice #{$invoice->id} marked as PAID automatically.");
                }
            }
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Mock Local Simulator for Testing without real API Keys
     */
    public function simulatePayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->status === 'paid') {
            return redirect()->route('dashboard')->with('error', 'SECURITY ALERT: This invoice has already been paid and cannot be processed again.');
        }

        return view('mock-checkout', compact('invoice'));
    }

    public function processPayment(Request $request, $id)
    {
        $invoice = Invoice::findOrFail($id);
        
        if ($invoice->status === 'paid') {
            return redirect()->route('dashboard')->with('error', 'SECURITY ALERT: This invoice has already been paid and cannot be processed again.');
        }
        
        $method = $request->input('payment_method');
        
        if ($method === 'qrph') {
            if ($invoice->payment_url) {
                return redirect()->away($invoice->payment_url);
            }
        }
        
        $invoice->update([
            'status' => 'paid',
            'amount_paid' => $invoice->total_amount
        ]);

        return redirect()->route('dashboard')->with('success', 'AUTOMATION SUCCESS: Payment Gateway callback processed. Your invoice has been marked as paid!');
    }
}
