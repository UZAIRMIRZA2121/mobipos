<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales & Performance Report</title>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 10px;
            color: #111827;
            font-size: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f3f4f6;
        }
        .header h1 {
            margin: 0 0 10px 0;
            font-size: 18px;
        }
        .header p {
            margin: 0;
            color: #6b7280;
        }
        h3 {
            margin-top: 0;
            margin-bottom: 5px;
            font-size: 14px;
            padding-bottom: 8px;
            display : block;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 12px 10px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        td {
            font-size: 14px;
            font-weight: normal;
        }
        th {
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            color: #6b7280;
            border-bottom: 2px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        @page {
            margin: 5mm;
        }
        @media print {
            body { 
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Sales & Performance Report</h1>
        <p>
            @if($start_date && $end_date)
                From: {{ \Carbon\Carbon::parse($start_date)->format('M d, Y') }} To: {{ \Carbon\Carbon::parse($end_date)->format('M d, Y') }}
            @elseif($start_date)
                From: {{ \Carbon\Carbon::parse($start_date)->format('M d, Y') }} To: Now
            @elseif($end_date)
                From: Beginning To: {{ \Carbon\Carbon::parse($end_date)->format('M d, Y') }}
            @else
                All Time
            @endif
        </p>
    </div>

    <div style="margin-bottom: 30px;">
        <table style="width: 100%; margin: 0; border-collapse: collapse; margin-bottom: 0;">
            <tr>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; font-weight: 600;">Total Sales</td>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: right;">PKR {{ number_format($total_sales, 2) }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; font-weight: 600;">Total Expenses</td>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: right;">PKR {{ number_format($total_expenses, 2) }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; font-weight: 600;">Profit</td>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: right;">PKR {{ number_format($net_profit, 2) }}</td>
            </tr>
            <tr>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; font-weight: 600;">Profit After Expense</td>
                <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: right;">PKR {{ number_format($profit, 2) }}</td>
            </tr>
            <tr>
                <td style="border-bottom: none; padding: 8px 10px; font-weight: 600;">Total Purchases</td>
                <td style="border-bottom: none; padding: 8px 10px; text-align: right;">PKR {{ number_format($total_purchases, 2) }}</td>
            </tr>
        </table>
    </div>

    <h3>Top Selling Products</h3>
    <div style="margin-bottom: 30px;">
        <table style="width: 100%; margin: 0; border-collapse: collapse; margin-bottom: 0;">
            @forelse($top_products as $p)
                <tr>
                    <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; font-weight: 600;">
                        {{ $p->name }} <span style="color: #6b7280; font-weight: normal; font-size: 11px;">(Qty: {{ $p->total_qty }})</span>
                        @if($p->imei) <span style="color: #6b7280; font-weight: normal; font-size: 11px;">[IMEI: {{ $p->imei }}]</span> @endif
                    </td>
                    <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: right;">PKR {{ number_format($p->total_revenue, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: center; color: #6b7280;">No sales found in this period.</td></tr>
            @endforelse
        </table>
    </div>

    <h3>Expenses List</h3>
    <div style="margin-bottom: 30px;">
        <table style="width: 100%; margin: 0; border-collapse: collapse; margin-bottom: 0;">
            @forelse($expenses_list as $e)
                <tr>
                    <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; font-weight: 600;">
                        {{ $e->title }}
                        @if($e->description) <span style="color: #6b7280; font-weight: normal; font-size: 11px;">- {{ $e->description }}</span> @endif
                    </td>
                    <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: right;">PKR {{ number_format($e->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: center; color: #6b7280;">No expenses found in this period.</td></tr>
            @endforelse
        </table>
    </div>

    <h3>Purchase Orders List</h3>
    <div style="margin-bottom: 30px;">
        <table style="width: 100%; margin: 0; border-collapse: collapse; margin-bottom: 0;">
            @forelse($purchase_orders_list as $po)
                <tr>
                    <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; font-weight: 600;">
                        PO-{{ $po->id }} <span style="color: #6b7280; font-weight: normal; font-size: 11px;">({{ $po->supplier_name ?: 'Unknown' }}) - <span style="text-transform: capitalize;">{{ $po->payment_status }}</span></span>
                        @if($po->payment_status === 'partial')
                            <span style="color: #6b7280; font-weight: normal; font-size: 11px;"> [Rem: PKR {{ number_format($po->remaining_amount, 2) }}]</span>
                        @endif
                    </td>
                    <td style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: right;">PKR {{ number_format($po->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="2" style="border-bottom: 1px dashed #e5e7eb; padding: 8px 10px; text-align: center; color: #6b7280;">No purchase orders found in this period.</td></tr>
            @endforelse
        </table>
    </div>

</body>
</html>
