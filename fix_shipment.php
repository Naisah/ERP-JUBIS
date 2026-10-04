<?php
$f = "app/Http/Controllers/Admin/ShipmentController.php";
$c = file_get_contents($f);

$old = <<<'EOD'
        Shipment::create([
            'invoice_id' => $request->invoice_id,
            'carrier' => $request->carrier,
            'tracking_number' => $request->tracking_number,
            'shipping_address' => $request->shipping_address,
            'delivery_notes' => $request->delivery_notes,
            'status' => 'processing',
        ]);

        return redirect()->back()->with('success', 'Shipment successfully created for this invoice.');
EOD;

$new = <<<'EOD'
        $shipment = Shipment::create([
            'invoice_id' => $request->invoice_id,
            'carrier' => $request->carrier,
            'tracking_number' => $request->tracking_number,
            'shipping_address' => $request->shipping_address,
            'delivery_notes' => $request->delivery_notes,
            'status' => 'processing',
        ]);
        
        // AUTOMATION: Call Logistics API (Lalamove) if Carrier is set to Auto or Lalamove
        if (stripos($request->carrier, 'lalamove') !== false || stripos($request->carrier, 'api') !== false || stripos($request->carrier, 'auto') !== false) {
            $logistics = app(\App\Services\LalamoveService::class);
            
            // Assume pickup is Jubis Warehouse
            $pickup = 'Jubis Marketing Warehouse, Manila, PH'; 
            $dropoff = $request->shipping_address;
            
            $booking = $logistics->bookDelivery($shipment, $pickup, $dropoff);
            
            if ($booking['success']) {
                $shipment->update([
                    'carrier' => $booking['carrier'],
                    'tracking_number' => $booking['tracking_number'],
                    'tracking_url' => $booking['tracking_url'],
                    'delivery_fee' => $booking['delivery_fee']
                ]);
                return redirect()->back()->with('success', 'Shipment created AND Lalamove Rider successfully booked via API! Tracking available.');
            }
        }

        return redirect()->back()->with('success', 'Shipment successfully created for this invoice.');
EOD;

$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "Done";
