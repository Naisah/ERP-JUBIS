<?php

namespace App\Services;

use App\Models\Shipment;
use Illuminate\Support\Facades\Log;

class LalamoveService
{
    /**
     * Book a delivery with Lalamove (or J&T Express) via API
     */
    public function bookDelivery(Shipment $shipment, $pickupAddress, $dropoffAddress, $weight = 'medium')
    {
        $apiKey = config('services.lalamove.api_key');
        $apiSecret = config('services.lalamove.api_secret');

        if (! $apiKey || ! $apiSecret) {
            if (! config('services.lalamove.simulation')) {
                return ['success' => false, 'error' => 'Courier integration is not configured. Use a manually booked carrier.'];
            }
            Log::info("Simulating Lalamove API call for Shipment ID: {$shipment->id}");
            // Return fake tracking link for testing/presentation
            $dummyTracking = 'LALA-'.strtoupper(bin2hex(random_bytes(16)));

            return [
                'success' => true,
                'carrier' => 'Lalamove (API Simulated)',
                'tracking_number' => $dummyTracking,
                'tracking_url' => url("/api/mock/tracking/{$dummyTracking}"),
                'delivery_fee' => rand(120, 450),
            ];
        }

        // A quotation is not a booking. Do not fabricate order IDs or tracking links.
        return ['success' => false, 'error' => 'Automatic live courier booking is not available. Book with the courier and enter its tracking number manually.'];

    }
}
