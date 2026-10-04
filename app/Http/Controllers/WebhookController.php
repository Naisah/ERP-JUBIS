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
        Log::info('PayMongo Webhook Received: ', $payload);

        // Verify the webhook signature here in production

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
    public function simulatePayment(Request $request, $referenceId)
    {
        $invoice = Invoice::where('payment_reference_id', $referenceId)->firstOrFail();
        
        $invoice->update([
            'status' => 'paid',
            'amount_paid' => $invoice->total_amount
        ]);

        return redirect()->route('dashboard')->with('success', 'AUTOMATION SUCCESS: Payment Gateway callback processed. Your invoice has been marked as paid!');
    }
}
