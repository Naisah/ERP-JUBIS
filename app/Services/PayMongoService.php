<?php

namespace App\Services;

use App\Models\Invoice;
use App\Support\Money;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    /**
     * Create a PayMongo Payment Link for an Invoice
     */
    public function createPaymentLink(Invoice $invoice)
    {
        // Check if API key is configured. If not, simulate the API call for demo purposes.
        $apiKey = config('services.paymongo.secret_key');

        if (! $apiKey) {
            if (! config('services.paymongo.simulation')) {
                return ['success' => false, 'error' => 'Payment gateway is not configured.'];
            }
            Log::info("Simulating PayMongo API call for Invoice ID: {$invoice->id}");
            // Simulate PayMongo URL Generation (so the user can test the ERP flow locally without an API key)
            $dummyRef = 'link_'.uniqid();

            return [
                'success' => true,
                'checkout_url' => url("/api/mock/payment/invoice/{$invoice->id}"),
                'reference_id' => $dummyRef,
            ];
        }

        // REAL PAYMONGO INTEGRATION
        try {
            // PayMongo expects amounts in cents (e.g. 100.00 = 10000)
            $amountInCents = Money::cents($invoice->balance_due);
            if ($amountInCents <= 0) {
                return ['success' => false, 'error' => 'Invoice has no outstanding balance.'];
            }

            $response = Http::withBasicAuth($apiKey, '')->connectTimeout(5)->timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ])
                ->post('https://api.paymongo.com/v1/links', [
                    'data' => [
                        'attributes' => [
                            'amount' => $amountInCents,
                            'description' => 'Payment for Invoice #INV-'.str_pad($invoice->id, 5, '0', STR_PAD_LEFT),
                            'remarks' => 'Jubis Marketing ERP Automation',
                        ],
                    ],
                ]);

            if ($response->successful() && $response->json('data.id') && $response->json('data.attributes.checkout_url')) {
                $data = $response->json();

                return [
                    'success' => true,
                    'checkout_url' => $data['data']['attributes']['checkout_url'],
                    'reference_id' => $data['data']['id'],
                ];
            }

            Log::error('PayMongo API Error: '.$response->body());

            return ['success' => false, 'error' => 'Payment gateway rejected the request.'];

        } catch (\Exception $e) {
            Log::error('PayMongo Exception: '.$e->getMessage());

            return ['success' => false, 'error' => 'Payment gateway is temporarily unavailable.'];
        }
    }

    public function syncPaymentLink(Invoice $invoice): bool
    {
        $link = $this->createPaymentLink($invoice);
        if (! ($link['success'] ?? false)) {
            return false;
        }
        DB::table('payment_links')->insert([
            'invoice_id' => $invoice->id, 'reference' => $link['reference_id'],
            'amount' => $invoice->balance_due, 'created_at' => now(),
        ]);
        $invoice->update(['payment_url' => $link['checkout_url'], 'payment_reference_id' => $link['reference_id']]);

        return true;
    }
}
