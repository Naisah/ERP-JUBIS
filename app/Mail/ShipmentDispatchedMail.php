<?php

namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShipmentDispatchedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $shipment;

    /**
     * Create a new message instance.
     */
    public function __construct(Shipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: (str_contains($this->shipment->carrier ?? '', 'Simulated') ? 'Demo shipment created' : 'Shipment update').' — Jubis Marketing (#'.$this->shipment->invoice_id.')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.shipments.dispatched',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
