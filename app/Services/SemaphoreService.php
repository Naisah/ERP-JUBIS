<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreService
{
    /**
     * Send an SMS using the Semaphore API
     */
    public function sendSms($phoneNumber, $message)
    {
        $apiKey = env('SEMAPHORE_API_KEY');

        // If no API key is provided, simulate it in the logs for the presentation!
        if (!$apiKey) {
            Log::info("=== SMS SIMULATION ===");
            Log::info("To: {$phoneNumber}");
            Log::info("Message: {$message}");
            Log::info("======================");
            return true;
        }

        try {
            $response = Http::post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => $apiKey,
                'number' => $phoneNumber,
                'message' => $message,
                // 'sendername' => 'SEMAPHORE' // You can request a custom sender name like 'JUBIS' later if you upgrade
            ]);

            if ($response->successful()) {
                Log::info("Semaphore SMS sent successfully to {$phoneNumber}");
                return true;
            }

            Log::error("Semaphore API Error: " . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error("Semaphore Exception: " . $e->getMessage());
            return false;
        }
    }
}
