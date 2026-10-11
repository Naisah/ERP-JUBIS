<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ShipmentDeliveredMail;
use App\Mail\ShipmentDispatchedMail;
use App\Models\Invoice;
use App\Models\Shipment;
use App\Services\CompanySettings;
use App\Services\LalamoveService;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with('invoice.user')->latest();
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return Inertia::render('Admin/Shipments/Index', [
            'shipments' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only('status'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id|unique:shipments,invoice_id',
            'carrier' => 'required|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
            'shipping_address' => 'required|string|max:2000',
            'delivery_notes' => 'nullable|string|max:5000',
        ]);
        $shipment = DB::transaction(function () use ($validated) {
            $invoice = Invoice::whereKey($validated['invoice_id'])->lockForUpdate()->firstOrFail();
            if ($invoice->shipments()->exists()) {
                throw ValidationException::withMessages(['invoice_id' => 'A shipment already exists for this invoice.']);
            }
            $shipment = Shipment::create($validated + ['status' => 'processing']);
            if (empty($validated['tracking_number']) && preg_match('/lalamove|api|auto/i', $validated['carrier'])) {
                $booking = app(LalamoveService::class)->bookDelivery($shipment, app(CompanySettings::class)->all()['company_address'], $shipment->shipping_address);
                if (! ($booking['success'] ?? false)) {
                    throw ValidationException::withMessages(['carrier' => $booking['error'] ?? 'Courier booking failed.']);
                }
                $shipment->update(Arr::only($booking, ['carrier', 'tracking_number', 'tracking_url', 'delivery_fee']));
                $fee = Money::cents($shipment->delivery_fee);
                $total = Money::cents($invoice->total_amount) + $fee;
                $paid = Money::cents($invoice->amount_paid);
                $invoice->update([
                    'shipping_amount' => Money::decimal($fee), 'total_amount' => Money::decimal($total),
                    'status' => $paid === $total ? 'paid' : ($paid > 0 ? 'partially_paid' : $invoice->status),
                    'payment_url' => null, 'payment_reference_id' => null,
                ]);
            }

            return $shipment;
        });
        $accepted = $this->sendNotification($shipment, new ShipmentDispatchedMail($shipment));
        $message = 'Shipment created. '.($accepted ? 'Notification accepted for sending.' : 'The email notification could not be sent; please check the mail configuration.');
        if (str_contains($shipment->carrier, 'Simulated')) {
            $message = 'Demo shipment created; no courier was dispatched. '.($accepted ? 'Notification accepted for sending.' : 'The email notification could not be sent.');
        }

        return back()->with($accepted ? 'success' : 'error', $message);
    }

    public function updateStatus(Request $request, Shipment $shipment)
    {
        $validated = $request->validate(['status' => 'required|in:processing,picking_packing,dispatched,in_transit,delivered']);
        $changed = DB::transaction(function () use ($shipment, $validated) {
            $shipment = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            if ($shipment->status === $validated['status']) {
                return false;
            }
            $states = ['processing', 'picking_packing', 'dispatched', 'in_transit', 'delivered'];
            abort_if(array_search($validated['status'], $states) < array_search($shipment->status, $states), 409, 'Shipment status cannot move backwards.');
            $updates = ['status' => $validated['status']];
            if (in_array($validated['status'], ['dispatched', 'in_transit', 'delivered'], true) && ! $shipment->shipped_at) {
                $updates['shipped_at'] = now()->timezone(config('company.timezone'))->toDateString();
            }
            if ($validated['status'] === 'delivered') {
                $updates['delivered_at'] = now()->timezone(config('company.timezone'))->toDateString();
            }
            $shipment->update($updates);

            return true;
        });
        if ($changed && $validated['status'] === 'delivered') {
            $shipment->refresh();
            $accepted = $this->sendNotification($shipment, new ShipmentDeliveredMail($shipment));

            return back()->with($accepted ? 'success' : 'error', $accepted
                ? 'Shipment delivered. Confirmation accepted for sending.'
                : 'Shipment delivered, but the confirmation email could not be sent.');
        }

        return back()->with('success', $changed ? 'Shipment status updated.' : 'Shipment status is already up to date.');
    }

    private function sendNotification(Shipment $shipment, Mailable $mail): bool
    {
        try {
            $email = $shipment->invoice->user?->email;
            if (! $email) {
                return false;
            }
            Mail::to($email)->send($mail);

            return true;
        } catch (\Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
