<?php

namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShipmentDeliveredMail extends Mailable
{
    use Queueable, SerializesModels;

    public $shipment;

    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Delivered: Your Jubis Marketing Order (#' . $this->shipment->invoice_id . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.shipments.delivered',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
