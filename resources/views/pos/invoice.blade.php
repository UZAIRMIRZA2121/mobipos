<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* Thermal Printer Optimized CSS */
        @import url('https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap');
        
        body {
            font-family: 'Space Mono', monospace;
            background: #fff;
            color: #000;
            margin: 0;
            padding: 0;
            font-size: 12px;
        }

        .receipt-container {
            width: 80mm; /* Standard 80mm thermal paper */
            max-width: 100%;
            margin: 0 auto;
            padding: 10px;
            box-sizing: border-box;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }

        .header h1 {
            font-size: 18px;
            margin: 5px 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 2px 0;
            font-size: 11px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11px;
        }

        th, td {
            padding: 4px 0;
        }

        th {
            border-bottom: 1px dashed #000;
            text-align: left;
        }

        .item-name {
            font-weight: bold;
            display: block;
        }
        
        .item-meta {
            font-size: 10px;
            color: #333;
        }

        .item-imei {
            font-size: 12px;
            font-weight: bold;
            color: #000;
            background: #f0f0f0;
            padding: 3px 5px;
            border: 1px dashed #000;
            display: inline-block;
            margin-top: 3px;
            margin-bottom: 2px;
            border-radius: 2px;
        }

        .totals {
            margin-top: 10px;
        }

        .totals .info-row {
            font-size: 12px;
        }

        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin-top: 5px;
        }

        .footer {
            margin-top: 20px;
            font-size: 10px;
        }

        .btn-print {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #000;
            color: #fff;
            text-align: center;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
            cursor: pointer;
            border: none;
            font-family: inherit;
        }

        @media print {
            .btn-print {
                display: none;
            }
            body, .receipt-container {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            @page {
                margin: 0; /* Remove browser margins */
            }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="header text-center">
        @if(isset($invoiceSettings) && $invoiceSettings->logo)
            <img src="{{ url($invoiceSettings->logo) }}" alt="Logo" style="max-width: {{ $invoiceSettings->logo_size ?? 120 }}px; max-height: 200px; object-fit: contain; margin-bottom: 5px; display: block; margin-left: auto; margin-right: auto;">
        @endif
        <h1>{{ $invoiceSettings->store_name ?? 'MobiPOS' }}</h1>
        @if(isset($invoiceSettings) && $invoiceSettings->header_text)
            <p>{!! nl2br(e($invoiceSettings->header_text)) !!}</p>
        @elseif(!isset($invoiceSettings) || !$invoiceSettings->header_text)
            <p>Your Trusted Mobile Shop</p>
        @endif
        
        @if(isset($invoiceSettings) && $invoiceSettings->address)
            <p>{!! nl2br(e($invoiceSettings->address)) !!}</p>
        @elseif(!isset($invoiceSettings) || !$invoiceSettings->address)
            <p>123 Main Street, City</p>
        @endif
        
        @if(isset($invoiceSettings) && $invoiceSettings->phone)
            <p>Tel: {{ $invoiceSettings->phone }}</p>
        @elseif(!isset($invoiceSettings) || !$invoiceSettings->phone)
            <p>Tel: +92 300 1234567</p>
        @endif
    </div>

    <div class="divider"></div>

    <div class="info-row">
        <span>Receipt #:</span>
        <span>{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
    </div>
    <div class="info-row">
        <span>Date:</span>
        <span>{{ $order->created_at->format('d/m/Y h:i A') }}</span>
    </div>
    <div class="info-row">
        <span>Customer:</span>
        <span>{{ $order->buyer ? $order->buyer->name : ($order->customer_name ?? 'Walk-in Customer') }}</span>
    </div>
    <div class="info-row">
        <span>Payment:</span>
        <span>{{ ucfirst($order->payment_method) }}</span>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-center">Qty</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td style="width: 60%">
                    @if($item->product && isset($item->product->meta_data['brand']) && !empty($item->product->meta_data['brand']))
                        <div class="item-meta" style="text-transform: uppercase; font-weight: bold; font-size: 0.85em; margin-bottom: 1px;">{{ $item->product->meta_data['brand'] }}</div>
                    @endif
                    <span class="item-name">
                        {{ $item->product ? $item->product->name : 'Unknown Product' }}
                        @if($item->product && ($item->product->condition || $item->product->color))
                            ({{ collect([$item->product->condition, $item->product->color])->filter()->join(' - ') }})
                        @endif
                    </span>
                    @if($item->product && ($item->product->code || $item->product->barcode))
                        <div class="item-meta">Code: {{ $item->product->code ?? $item->product->barcode }}</div>
                    @endif
                    @if($item->imeis)
                        <div class="item-imei">IMEI: {{ $item->imeis }}</div>
                    @endif
                    <div class="item-meta">@ PKR {{ number_format($item->sell_price) }}</div>
                </td>
                <td class="text-center" style="vertical-align: top;">{{ $item->qty }}</td>
                <td class="text-right" style="vertical-align: top;">PKR {{ number_format($item->sell_price * $item->qty) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals">
        <div class="info-row">
            <span>Subtotal:</span>
            <span>PKR {{ number_format($order->subtotal) }}</span>
        </div>
        @if($order->discount > 0)
        <div class="info-row">
            <span>Discount:</span>
            <span>- PKR {{ number_format($order->discount) }}</span>
        </div>
        @endif
        @if($order->tax > 0)
        <div class="info-row">
            <span>Tax:</span>
            <span>+ PKR {{ number_format($order->tax) }}</span>
        </div>
        @endif
        
        <div class="info-row grand-total">
            <span>TOTAL:</span>
            <span>PKR {{ number_format($order->total) }}</span>
        </div>
        
        <div class="info-row" style="margin-top: 5px;">
            <span>Paid:</span>
            <span>PKR {{ number_format($order->payment_method === 'ledger' ? $order->total : $order->paid_amount) }}</span>
        </div>
        <div class="info-row">
            <span>{{ ($order->payment_method === 'ledger' ? $order->total : $order->paid_amount) > $order->total ? 'Change:' : 'Due:' }}</span>
            <span>PKR {{ number_format($order->payment_method === 'ledger' ? 0 : $order->due_amount) }}</span>
        </div>
        <div class="info-row" style="margin-top: 5px; font-weight: bold;">
            <span>Status:</span>
            <span style="text-transform: uppercase;">{{ $order->payment_status }}</span>
        </div>
    </div>

    <div class="divider"></div>

    @if($order->buyer && $order->buyer->ledgers->count() > 0)
    <div class="customer-ledger" style="margin-top: 10px;">
        <div class="text-center bold" style="margin-bottom: 5px; text-transform: uppercase;">Customer Ledger</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th class="text-right">Dr</th>
                    <th class="text-right">Cr</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->buyer->ledgers()->orderBy('date', 'desc')->orderBy('id', 'desc')->take(3)->get() as $ledger)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($ledger->date)->format('d/m/y h:i A') }}</td>
                    <td>{{ ucfirst($ledger->type) }}</td>
                    <td class="text-right">{{ $ledger->debit > 0 ? number_format($ledger->debit) : '-' }}</td>
                    <td class="text-right">{{ $ledger->credit > 0 ? number_format($ledger->credit) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="info-row bold" style="margin-top: 5px; border-top: 1px dashed #000; padding-top: 5px;">
            <span>
                @if($order->buyer->balance > 0)
                    Customer will pay:
                @elseif($order->buyer->balance < 0)
                    Shop will pay:
                @else
                    Balance:
                @endif
            </span>
            <span>PKR {{ number_format(abs($order->buyer->balance)) }}</span>
        </div>
    </div>

    <div class="divider"></div>
    @endif

    <div class="footer">
        @if(isset($invoiceSettings) && $invoiceSettings->footer_text)
            <p class="bold" style="margin-bottom: 10px; text-align: center;">{!! nl2br(e($invoiceSettings->footer_text)) !!}</p>
        @else
            <p class="bold" style="margin-bottom: 10px; text-align: center;">THANK YOU FOR YOUR SHOPPING!</p>
        @endif
       <p class="mb-0 text-center">
    © <span id="currentYear"></span> All Rights Reserved |
    Developed with <span class="text-danger">❤</span> by
    <strong>MU-Tech Studio</strong> |
    <strong><a href="tel:03086452242" style="color: inherit; text-decoration: none;">0308 6452242</a></strong>
</p>
    </div>
    

    <button class="btn-print" onclick="window.print()">Print Receipt</button>
</div>

<script>
    // Set current year dynamically
    document.getElementById('currentYear').textContent = new Date().getFullYear();

    // Automatically trigger print dialog when the popup loads
    window.onload = function() {
        setTimeout(function() {
            window.print();
        }, 500);
    }
</script>

</body>
</html>
