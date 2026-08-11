<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ledger - {{ $customer->name }}</title>
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
            display: block;
            margin-top: 2px;
            font-style: italic;
        }

        .totals {
            margin-top: 10px;
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
        <span>Report:</span>
        <span>Customer Ledger</span>
    </div>
    <div class="info-row">
        <span>Date:</span>
        <span>{{ \Carbon\Carbon::now()->format('d/m/Y h:i A') }}</span>
    </div>
    <div class="info-row">
        <span>Customer:</span>
        <span>{{ $customer->name }}</span>
    </div>
    @if($customer->phone)
    <div class="info-row">
        <span>Phone:</span>
        <span>{{ $customer->phone }}</span>
    </div>
    @endif

    <div class="divider"></div>

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
            @foreach($ledgers as $ledger)
            <tr>
                <td style="width: 25%">
                    {{ \Carbon\Carbon::parse($ledger->date)->format('d/m/y h:i A') }}
                </td>
                <td style="width: 35%">
                    <span class="item-name">{{ ucfirst($ledger->type) }}</span>
                    @if($ledger->note)
                        <span class="item-meta">{{ $ledger->note }}</span>
                    @endif
                </td>
                <td class="text-right" style="vertical-align: top; width: 20%">
                    {{ $ledger->debit > 0 ? number_format($ledger->debit) : '-' }}
                </td>
                <td class="text-right" style="vertical-align: top; width: 20%">
                    {{ $ledger->credit > 0 ? number_format($ledger->credit) : '-' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <div class="totals">
        <div class="info-row bold" style="margin-top: 5px;">
            <span>
                @if($customer->balance > 0)
                    Customer will pay:
                @elseif($customer->balance < 0)
                    Shop will pay:
                @else
                    Balance:
                @endif
            </span>
            <span>PKR {{ number_format(abs($customer->balance)) }}</span>
        </div>
    </div>

    <div class="divider"></div>

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
    

    <button class="btn-print" onclick="window.print()">Print Ledger</button>
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
