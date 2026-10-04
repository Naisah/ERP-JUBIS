<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-top: 4px solid #10b981; }
        .header { border-bottom: 2px solid #f3f4f6; padding-bottom: 20px; margin-bottom: 20px; text-align: center; }
        .logo { color: #1e3a8a; font-size: 24px; font-weight: bold; text-decoration: none; }
        .content { color: #374151; line-height: 1.6; }
        .success-box { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 6px; text-align: center; margin: 25px 0; border: 1px solid #a7f3d0; font-weight: bold; font-size: 18px; }
        .footer { text-align: center; color: #9ca3af; font-size: 12px; margin-top: 30px; border-top: 1px solid #f3f4f6; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="logo">JUBIS MARKETING ERP</span>
        </div>
        
        <div class="content">
            <h2 style="color: #111827; margin-top: 0;">Delivery Completed!</h2>
            <p>Hi {{ $shipment->invoice->user->name ?? 'Valued Client' }},</p>
            <p>We are pleased to inform you that your order for Invoice <strong>#{{ str_pad($shipment->invoice_id, 5, '0', STR_PAD_LEFT) }}</strong> has been successfully delivered.</p>
            
            <div class="success-box">
                Package Delivered Successfully!
            </div>

            <p><strong>Tracking Reference:</strong> {{ $shipment->tracking_number }}</p>
            <p><strong>Delivered To:</strong> {{ $shipment->shipping_address }}</p>

            <p>Thank you for trusting Jubis Marketing. If you have any concerns regarding your received items, please contact our support team.</p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} Jubis Marketing. All rights reserved.<br>
            This is an automated ERP notification. Please do not reply.
        </div>
    </div>
</body>
</html>
