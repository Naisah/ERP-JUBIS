<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 40px 50px 80px 50px; /* Top, Right, Bottom, Left */
        }
        body { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #333; margin: 0; padding: 0; font-size: 13px; }
        
        .header { width: 100%; display: table; border-bottom: 2px solid #0f172a; padding-bottom: 15px; margin-bottom: 20px; }
        .header-left { display: table-cell; vertical-align: bottom; width: 60%; }
        .header-right { display: table-cell; text-align: right; vertical-align: bottom; width: 40%; }
        
        .invoice-title { font-size: 28px; font-weight: bold; margin: 0 0 10px 0; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; }
        .invoice-details { color: #475569; line-height: 1.6; font-size: 13px; }
        
        .billing-grid { width: 100%; display: table; margin-bottom: 30px; }
        .billing-col { display: table-cell; width: 50%; vertical-align: top; }
        
        .section-title { font-size: 11px; text-transform: uppercase; font-weight: bold; color: #64748b; margin-bottom: 8px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; display: inline-block; width: 80%; }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .table th, .table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #e2e8f0; }
        .table th { background-color: #f1f5f9; font-weight: bold; color: #334155; font-size: 12px; text-transform: uppercase; }
        .table td { color: #0f172a; }
        .table .text-right { text-align: right; }
        .table .text-center { text-align: center; }
        
        .totals { width: 100%; display: table; margin-top: 20px; }
        .totals-left { display: table-cell; width: 50%; vertical-align: top; }
        .totals-right { display: table-cell; width: 50%; vertical-align: top; }
        
        .total-row { display: table; width: 100%; margin-bottom: 8px; }
        .total-label { display: table-cell; text-align: right; padding-right: 20px; color: #475569; }
        .total-value { display: table-cell; text-align: right; font-weight: bold; width: 140px; white-space: nowrap; }
        
        .grand-total { font-size: 16px; color: #0f172a; border-top: 2px solid #cbd5e1; padding-top: 10px; margin-top: 5px; }
        
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .badge-paid { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-unpaid { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        
        /* Fixed Footer at the very bottom */
        footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
            height: 50px;
            text-align: center;
            color: #64748b;
            font-size: 11px;
            border-top: 1px solid #e2e8f0;
            padding-top: 15px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('images/logo.png') }}" style="max-height: 80px; margin-bottom: 15px;">
            <div style="color: #64748b; line-height: 1.5; font-size: 12px;">
                <strong>JUBIS MARKETING</strong><br>
                123 Warehouse Avenue<br>
                Makati City, Metro Manila 1200<br>
                VAT Reg. TIN: 123-456-789-000
            </div>
        </div>
        <div class="header-right">
            <h2 class="invoice-title">TAX INVOICE</h2>
            <div class="invoice-details">
                <table style="width: 100%; text-align: right;">
                    <tr>
                        <td style="color: #64748b; padding-bottom: 5px;">Invoice No:</td>
                        <td style="font-weight: bold; color: #0f172a; padding-bottom: 5px;">#{{ str_pad($invoice->id, 5, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding-bottom: 5px;">Date Issued:</td>
                        <td style="font-weight: bold; color: #0f172a; padding-bottom: 5px;">{{ $invoice->created_at->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td style="color: #64748b; padding-bottom: 15px;">Due Date:</td>
                        <td style="font-weight: bold; color: #0f172a; padding-bottom: 15px;">{{ \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            @if($invoice->status === 'paid')
                                <span class="badge badge-paid">PAID IN FULL</span>
                            @else
                                <span class="badge badge-unpaid">PAYMENT PENDING</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="billing-grid">
        <div class="billing-col">
            <div class="section-title">Billed To</div>
            <div style="line-height: 1.5;">
                <strong style="font-size: 14px; color: #0f172a;">{{ $invoice->user->company_name ?? $invoice->user->name }}</strong><br>
                ATTN: {{ $invoice->user->name }}<br>
                {{ $invoice->user->email }}<br>
                {{ $invoice->billing_address ?? 'Address not specified' }}
            </div>
        </div>
        <div class="billing-col" style="text-align: right;">
            @if($invoice->shipments->first())
            <div class="section-title" style="width: 100%;">Shipping Information</div>
            <div style="line-height: 1.5;">
                <strong>Carrier:</strong> {{ strtoupper($invoice->shipments->first()->carrier) }}<br>
                <strong>Tracking:</strong> {{ $invoice->shipments->first()->tracking_number ?? 'Pending' }}<br>
                <div style="margin-top: 5px; color: #475569;">
                    {{ $invoice->shipments->first()->shipping_address }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 45%;">Item Description</th>
                <th class="text-center" style="width: 15%;">Quantity</th>
                <th class="text-right" style="width: 20%;">Unit Price</th>
                <th class="text-right" style="width: 20%;">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->quote->items as $item)
            <tr>
                <td>
                    <strong style="display: block; margin-bottom: 3px;">{{ $item->product->name }}</strong>
                    <span style="color: #64748b; font-size: 11px;">SKU: {{ $item->product->sku }}</span>
                </td>
                <td class="text-center">{{ $item->quantity }} {{ $item->product->unit_of_measure }}</td>
                <td class="text-right">PHP {{ number_format($item->unit_price ?? 0, 2) }}</td>
                <td class="text-right">PHP {{ number_format($item->total_price ?? 0, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-left">
            @if($invoice->status !== 'paid')
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 4px; margin-top: 15px; width: 85%;">
                <strong style="font-size: 11px; text-transform: uppercase; color: #475569;">Payment Instructions</strong>
                <p style="margin: 8px 0 0 0; font-size: 11px; color: #64748b; line-height: 1.6;">
                    Please remit payment via GCash, Maya, or Credit Card using the secure link on your client dashboard.<br>
                    Make all checks payable to <strong>JUBIS MARKETING</strong>.
                </p>
            </div>
            @endif
        </div>
        <div class="totals-right">
            <div class="total-row">
                <div class="total-label">Subtotal (VAT-Exclusive)</div>
                <div class="total-value">PHP {{ number_format($invoice->subtotal ?: ($invoice->total_amount / 1.12), 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">12% VAT</div>
                <div class="total-value">PHP {{ number_format($invoice->vat_amount ?: ($invoice->total_amount - ($invoice->total_amount / 1.12)), 2) }}</div>
            </div>
            @if($invoice->shipments->first() && $invoice->shipments->first()->delivery_fee > 0)
            <div class="total-row">
                <div class="total-label">Shipping & Handling</div>
                <div class="total-value">PHP {{ number_format($invoice->shipments->first()->delivery_fee, 2) }}</div>
            </div>
            @endif
            <div class="total-row grand-total">
                <div class="total-label"><strong>TOTAL DUE</strong></div>
                <div class="total-value"><strong>PHP {{ number_format($invoice->total_amount + ($invoice->shipments->first()->delivery_fee ?? 0), 2) }}</strong></div>
            </div>
            
            @if($invoice->amount_paid > 0)
            <div class="total-row" style="margin-top: 15px;">
                <div class="total-label" style="color: #166534;">Amount Paid</div>
                <div class="total-value" style="color: #166534;">- PHP {{ number_format($invoice->amount_paid, 2) }}</div>
            </div>
            <div class="total-row" style="border-top: 1px solid #e2e8f0; padding-top: 8px;">
                <div class="total-label"><strong>Remaining Balance</strong></div>
                <div class="total-value"><strong>PHP {{ number_format(($invoice->total_amount + ($invoice->shipments->first()->delivery_fee ?? 0)) - $invoice->amount_paid, 2) }}</strong></div>
            </div>
            @endif
        </div>
    </div>

    <footer>
        <strong>Jubis Marketing</strong> &bull; 123 Warehouse Avenue, Makati City &bull; +63 (2) 8123 4567 &bull; sales@jubismarketing.com<br>
        
    </footer>
</body>
</html>

