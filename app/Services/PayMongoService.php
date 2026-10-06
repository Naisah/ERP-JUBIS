<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;

class PayMongoService
{
    /**
     * Create a PayMongo Payment Link for an Invoice
     */
    public function createPaymentLink(Invoice $invoice)
    {
        // Check if API key is configured. If not, simulate the API call for demo purposes.
        $apiKey = env('PAYMONGO_SECRET_KEY');
        
        if (!$apiKey) {
            Log::info("Simulating PayMongo API call for Invoice ID: {$invoice->id}");
            // Simulate PayMongo URL Generation (so the user can test the ERP flow locally without an API key)
            $dummyRef = 'link_' . uniqid();
            return [
                'success' => true,
                'checkout_url' => url("/api/mock/payment/{$dummyRef}"),
                'reference_id' => $dummyRef
            ];
        }

        // REAL PAYMONGO INTEGRATION
        try {
            // PayMongo expects amounts in cents (e.g. 100.00 = 10000)
            $amountInCents = intval($invoice->total_amount * 100);

            $response = Http::withBasicAuth($apiKey, '')
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api.paymongo.com/v1/links', [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountInCents,
                            'description' => 'Payment for Invoice #INV-' . str_pad($invoice->quote_id, 5, '0', STR_PAD_LEFT),
                            'remarks' => 'Jubis Marketing ERP Automation',
                            'payment_method_allowed' => ['card', 'paymaya', 'gcash', 'qrph']
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'checkout_url' => $data['data']['attributes']['checkout_url'],
                    'reference_id' => $data['data']['id']
                ];
            }

            Log::error('PayMongo API Error: ' . $response->body());
            return ['success' => false, 'error' => 'Payment gateway rejected the request.'];

        } catch (\Exception $e) {
            Log::error('PayMongo Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
