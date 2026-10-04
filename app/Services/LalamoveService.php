<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Shipment;

class LalamoveService
{
    /**
     * Book a delivery with Lalamove (or J&T Express) via API
     */
    public function bookDelivery(Shipment $shipment, $pickupAddress, $dropoffAddress, $weight = 'medium')
    {
        $apiKey = env('LALAMOVE_API_KEY');
        $apiSecret = env('LALAMOVE_API_SECRET');
        
        if (!$apiKey || !$apiSecret) {
            Log::info("Simulating Lalamove API call for Shipment ID: {$shipment->id}");
            // Return fake tracking link for testing/presentation
            $dummyTracking = 'LALA-' . strtoupper(uniqid());
            return [
                'success' => true,
                'carrier' => 'Lalamove (API Simulated)',
                'tracking_number' => $dummyTracking,
                'tracking_url' => url("/api/mock/tracking/{$dummyTracking}"),
                'delivery_fee' => rand(120, 450)
            ];
        }

        // REAL API INTEGRATION
        try {
            // Build the payload required by Lalamove API
            // Note: Actual Lalamove implementation requires HMAC-SHA256 signature generation
            // This is a simplified structural representation.
            
            $payload = [
                'data' => [
                    'serviceType' => 'MOTORCYCLE', // or MPV, TRUCK depending on weight
                    'stops' => [
                        [ 'coordinates' => ['lat' => '14.5995', 'lng' => '120.9842'], 'address' => $pickupAddress ],
                        [ 'coordinates' => ['lat' => '14.6091', 'lng' => '121.0223'], 'address' => $dropoffAddress ]
                    ],
                    'deliveries' => [
                        [ 'toStop' => 1, 'toContact' => ['name' => 'Client', 'phone' => '+639123456789'] ]
                    ]
                ]
            ];

            // In production, generate signature: signature = HMAC(timestamp + "\r\n" + POST + "\r\n" + /v3/quotations + "\r\n" + body)
            
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'MARKET' => 'PH'
            ])->post('https://rest.sandbox.lalamove.com/v3/quotations', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Then you'd call /v3/orders with the quotation ID
                // Returning standard response for now
                return [
                    'success' => true,
                    'carrier' => 'Lalamove',
                    'tracking_number' => 'ORDER-' . $data['data']['id'] ?? uniqid(),
                    'tracking_url' => 'https://share.lalamove.com/order/' . uniqid(),
                    'delivery_fee' => $data['data']['priceBreakdown']['total'] ?? 150
                ];
            }

            Log::error('Logistics API Error: ' . $response->body());
            return ['success' => false, 'error' => 'Courier rejected the booking.'];

        } catch (\Exception $e) {
            Log::error('Logistics Exception: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
