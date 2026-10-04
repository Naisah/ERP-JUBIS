<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .header { border-bottom: 2px solid #f3f4f6; padding-bottom: 20px; margin-bottom: 20px; text-align: center; }
        .logo { color: #1e3a8a; font-size: 24px; font-weight: bold; text-decoration: none; }
        .content { color: #374151; line-height: 1.6; }
        .tracking-box { background: #f3f4f6; padding: 15px; border-radius: 6px; text-align: center; margin: 25px 0; border: 1px solid #e5e7eb; }
        .button { display: inline-block; background-color: #f97316; color: #ffffff; text-decoration: none; font-weight: bold; padding: 12px 24px; border-radius: 6px; margin-top: 10px; }
        .footer { text-align: center; color: #9ca3af; font-size: 12px; margin-top: 30px; border-top: 1px solid #f3f4f6; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="logo">JUBIS MARKETING ERP</span>
        </div>
        
        <div class="content">
            <h2 style="color: #111827; margin-top: 0;">Good news, {{ $shipment->invoice->user->name ?? 'Valued Client' }}!</h2>
            <p>Your order for Invoice <strong>#{{ str_pad($shipment->invoice_id, 5, '0', STR_PAD_LEFT) }}</strong> has been successfully booked and is currently processing for delivery.</p>
            
            <p><strong>Courier:</strong> {{ $shipment->carrier }}</p>
            <p><strong>Shipping To:</strong> {{ $shipment->shipping_address }}</p>

            <div class="tracking-box">
                <h3 style="margin-top: 0; color: #374151;">Tracking Number: {{ $shipment->tracking_number }}</h3>
                @if($shipment->tracking_url)
                    <p>You can track your delivery live on the map using the link below:</p>
                    <a href="{{ $shipment->tracking_url }}" class="button">Track My Delivery Live</a>
                @else
                    <p>Please use this tracking number on the courier's website.</p>
                @endif
            </div>

            <p>If you have any questions, please contact our support team. Thank you for doing business with Jubis Marketing!</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Jubis Marketing. All rights reserved.<br>
            This is an automated ERP notification. Please do not reply.
        </div>
    </div>
</body>
</html>

