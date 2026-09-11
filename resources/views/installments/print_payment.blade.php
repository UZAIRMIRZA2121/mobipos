<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installment Receipt - Order #{{ $installment->order_id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Mono', 'Courier New', monospace; font-size: 11px; margin: 0 auto; padding: 10px; color: #000; width: 300px; max-width: 100%; line-height: 1.5; background: #fff; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: 500; }
        .header { text-align: center; margin-bottom: 10px; }
        .store-name { font-family: 'DM Sans', sans-serif; font-size: 15px; font-weight: 700; letter-spacing: 0.5px; margin-bottom: 2px; }
        .store-sub { font-size: 9.5px; color: #000; line-height: 1.6; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: bold; font-family: 'DM Sans', sans-serif; }
        .header p { margin: 3px 0 0; font-size: 11px; }
        .section-title { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; font-family: 'DM Sans', sans-serif; border-bottom: 1px dashed #999; padding-bottom: 3px; margin: 10px 0 5px 0; }
        .row { display: flex; justify-content: space-between; font-size: 10.5px; padding: 1px 0; }
        .row .label { color: #000; }
        .row .val { font-weight: 500; }
        .divider { border: none; border-top: 1px dashed #999; margin: 6px 0; }
        .footer { text-align: center; font-size: 9.5px; color: #000; margin-top: 4px; line-height: 1.7; }
        .footer-thanks { font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 700; color: #000; }
        @media print {
            .no-print { display: none; }
            @page { size: 80mm auto; margin: 4mm 3mm; }
            body { padding: 0; margin: 0; width: 100%; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print" style="text-align: right; margin-bottom: 10px;">
    <button onclick="window.print()" style="padding: 5px 10px; font-size: 14px;">Print</button>
</div>

<div class="header">
    @if(isset($printSettings))
        @if($printSettings->logo)
            <div style="text-align: center; margin-bottom: 10px;">
                <img src="{{ url($printSettings->logo) }}" alt="Logo" style="max-width: {{ $printSettings->logo_size ?? 120 }}px; max-height: 200px; object-fit: contain;">
            </div>
        @endif
        <div class="store-name">{{ $printSettings->store_name ?? 'MobiPOS' }}</div>
        
        <div class="store-sub">
            @if($printSettings->header_text)
                {!! nl2br(e($printSettings->header_text)) !!}<br>
            @endif
            @if($printSettings->address)
                {!! nl2br(e($printSettings->address)) !!}<br>
            @endif
            @if(isset($storeSetting) && $storeSetting->phone)
                {{ $storeSetting->phone }}
            @endif
        </div>
        <div class="divider" style="margin: 8px 0;"></div>
    @endif
    <h2>Installment Receipt</h2>
    <p>Order #{{ $installment->order_id }}</p>
    <p>{{ date('Y-m-d h:i A') }}</p>
</div>

<div class="section-title">Customer</div>
<div class="row">
    <span>Name:</span>
    <span class="text-right">{{ $installment->customer->name ?? 'N/A' }}</span>
</div>
<div class="row">
    <span>Phone:</span>
    <span class="text-right">{{ $installment->customer->phone ?? 'N/A' }}</span>
</div>

<div class="section-title">Current Payment</div>
<div class="row">
    <span>Date:</span>
    <span class="text-right">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-M-y') }}</span>
</div>
<div class="row bold" style="font-size: 12px; margin-top: 2px;">
    <span>Amount Paid:</span>
    <span class="text-right">PKR {{ number_format($payment->amount, 0) }}</span>
</div>
<div class="row">
    <span>Method:</span>
    <span class="text-right">{{ ucfirst($payment->payment_method) }}</span>
</div>
@if($payment->notes)
<div class="row">
    <span>Notes:</span>
    <span class="text-right">{{ $payment->notes }}</span>
</div>
@endif

<div class="section-title">Installment Plan Summary</div>
<div class="row">
    <span>Total Amount:</span>
    <span>{{ number_format($installment->total_amount, 0) }}</span>
</div>
<div class="divider"></div>
<div class="row">
    <span>Total Paid:</span>
    <span>{{ number_format($totalPaid, 0) }}</span>
</div>
<div class="row bold">
    <span>Remaining Balance:</span>
    <span>{{ number_format($remaining, 0) }}</span>
</div>

<div class="divider" style="margin-top: 15px;"></div>
<div class="footer">
    @if(isset($printSettings) && $printSettings->footer_text)
        {!! nl2br(e($printSettings->footer_text)) !!}<br>
    @endif
    
    @if(!isset($printSettings) || $printSettings->barcode_print)
    <div style="text-align: center; margin: 10px 0;">
        <svg id="barcode"></svg>
    </div>
    @endif
    
    <div class="footer-thanks" style="margin-top: 4px;"></div>
</div>

@if(!isset($printSettings) || $printSettings->barcode_print)
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    // Generate barcode
    try {
        if (typeof JsBarcode !== 'undefined') {
            JsBarcode("#barcode", "P{{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}", {
                format: "CODE128",
                displayValue: true,
                fontSize: 16,
                height: 40,
                margin: 0
            });
        }
    } catch (e) {
        console.error("Barcode generation failed", e);
    }
</script>
@endif

</body>
</html>
