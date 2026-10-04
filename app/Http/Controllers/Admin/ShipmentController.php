<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['invoice.user'])->latest();

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Shipments/Index', [
            'shipments' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['status'])
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id|unique:shipments,invoice_id',
            'carrier' => 'required|string',
            'tracking_number' => 'nullable|string',
            'shipping_address' => 'required|string',
            'delivery_notes' => 'nullable|string',
        ]);

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
                
                // AUTOMATION: Fire Email Notification (Gmail SMTP)
                $client = $shipment->invoice->user;
                if ($client && $client->email) {
                    try {
                        \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\ShipmentDispatchedMail($shipment));
                        \Illuminate\Support\Facades\Log::info("Email successfully dispatched to " . $client->email);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to send email: " . $e->getMessage());
                    }
                }
                
                return redirect()->back()->with('success', 'Shipment created, Lalamove booked, AND Email Receipt dispatched!');
            }
        }

        return redirect()->back()->with('success', 'Shipment successfully created for this invoice.');
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $request->validate([
            'status' => 'required|in:processing,picking_packing,dispatched,in_transit,delivered',
        ]);

        $updateData = ['status' => $request->status];

        if ($request->status === 'dispatched') {
            $updateData['shipped_at'] = now();
        } elseif ($request->status === 'delivered') {
            $updateData['delivered_at'] = now();
        }

        $shipment->update($updateData);

        // AUTOMATION: Fire 'Delivered' Email Notification if status changed to delivered
        if ($request->status === 'delivered') {
            $client = $shipment->invoice->user;
            if ($client && $client->email) {
                try {
                    \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\ShipmentDeliveredMail($shipment));
                    \Illuminate\Support\Facades\Log::info("Delivery confirmation email successfully dispatched to " . $client->email);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send delivery email: " . $e->getMessage());
                }
            }
            return redirect()->back()->with('success', 'Shipment marked as Delivered AND Confirmation Email sent to client!');
        }

        return redirect()->back()->with('success', 'Shipment status updated.');
    }
}




