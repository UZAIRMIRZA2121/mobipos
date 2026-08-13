<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installment Details - Order #{{ $installment->order_id }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0 auto; padding: 10px; color: #000; width: 300px; max-width: 100%; box-sizing: border-box; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        .header p { margin: 3px 0 0; font-size: 12px; }
        .section-title { font-weight: bold; border-bottom: 1px dashed #000; padding-bottom: 3px; margin: 10px 0 5px 0; }
        .row { display: flex; justify-content: space-between; margin-bottom: 3px; }
        .divider { border-bottom: 1px dashed #000; margin: 5px 0; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; width: 100%; }
        }
    </style>
</head>
<body onload="window.print()">

<div class="no-print" style="text-align: right; margin-bottom: 10px;">
    <button onclick="window.print()" style="padding: 5px 10px; font-size: 14px;">Print</button>
</div>

<div class="header">
    <h2>Installment Plan</h2>
    <p>Order #{{ $installment->order_id }}</p>
    <p>{{ $installment->created_at->format('Y-m-d h:i A') }}</p>
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
<div class="row">
    <span>Status:</span>
    <span class="text-right">{{ $installment->status }}</span>
</div>

<div class="section-title">Products</div>
@foreach($installment->order->items as $item)
    <div style="margin-bottom: 5px;">
        <div class="bold">{{ $item->product->name ?? 'Product' }}</div>
        <div class="row">
            <span>{{ $item->qty }} x {{ number_format($item->sell_price, 0) }}</span>
            <span>PKR {{ number_format($item->qty * $item->sell_price, 0) }}</span>
        </div>
    </div>
@endforeach

<div class="section-title">Plan Details</div>
<div class="row">
    <span>Total Amount:</span>
    <span>{{ number_format($installment->total_amount, 0) }}</span>
</div>
<div class="row">
    <span>Down Payment:</span>
    <span>{{ number_format($installment->down_payment, 0) }}</span>
</div>
<div class="row">
    <span>Monthly Inst.:</span>
    <span>{{ number_format($installment->agreed_monthly_amount, 0) }}</span>
</div>
<div class="row">
    <span>Payment Day:</span>
    <span>{{ $installment->payment_day ?? 10 }}th of month</span>
</div>
<div class="divider"></div>
<div class="row">
    <span>Paid So Far:</span>
    <span>{{ number_format($totalPaid, 0) }}</span>
</div>
<div class="row bold">
    <span>Remaining Due:</span>
    <span>{{ number_format($remaining, 0) }}</span>
</div>

<div class="section-title">Payment History</div>
@if($installment->down_payment > 0)
<div style="margin-bottom: 8px;">
    <div class="row bold">
        <span>{{ $installment->created_at->format('d-M-y') }}</span>
        <span>PKR {{ number_format($installment->down_payment, 0) }}</span>
    </div>
    <div class="row">
        <span style="font-size: 11px;">Mthd: {{ ucfirst($installment->order->payment_method ?? 'cash') }}</span>
        <span class="text-right" style="font-size: 11px;">Advance Payment</span>
    </div>
</div>
@endif

@forelse($installment->payments as $payment)
<div style="margin-bottom: 8px;">
    <div class="row bold">
        <span>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-M-y') }}</span>
        <span>PKR {{ number_format($payment->amount, 0) }}</span>
    </div>
    <div class="row">
        <span style="font-size: 11px;">Mthd: {{ ucfirst($payment->payment_method) }}</span>
        <span class="text-right" style="font-size: 11px;">{{ substr($payment->notes, 0, 20) }}</span>
    </div>
</div>
@empty
@if($installment->down_payment <= 0)
<div class="text-center" style="font-size: 11px;">No payments recorded yet.</div>
@endif
@endforelse

<div class="divider" style="margin-top: 15px;"></div>
<div class="text-center" style="margin-top: 10px; font-weight: bold;">
    Thank You!
</div>

</body>
</html>
