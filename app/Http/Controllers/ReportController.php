<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Order;
use App\Models\PurchaseOrder;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        return view('shop.reports');
    }

    public function generate(Request $request)
    {
        $data = $this->getReportData($request);
        return response()->json($data);
    }

    public function print(Request $request)
    {
        $data = $this->getReportData($request);
        return view('shop.report-print', $data);
    }

    private function getReportData(Request $request)
    {
        $startDate = $request->input('start_date') ?: '2000-01-01';
        $endDate = $request->input('end_date') ?: '2099-12-31';

        // Add time to cover the entire end date
        $endDateTime = $endDate . ' 23:59:59';
        $startDateTime = $startDate . ' 00:00:00';

        // 1. Total Sales (excluding refunded)
        $totalSales = Order::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->where('payment_status', '!=', 'refunded')
            ->sum('total');

        // 2. Total Purchases
        $totalPurchases = PurchaseOrder::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->sum('amount');

        // 3. Total Expenses
        $totalExpenses = Expense::whereBetween('expense_date', [$startDateTime, $endDateTime])
            ->sum('amount');

        // 4. Current Profit (Sales - Purchases - Expenses)
        $profit = $totalSales - ($totalPurchases + $totalExpenses);

        // 5. Cost of Goods Sold (buy_price * qty of sold items)
        $totalCogs = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
            ->where('orders.payment_status', '!=', 'refunded')
            ->sum(DB::raw('order_items.buy_price * order_items.qty'));

        // 6. True Net Profit
        $netProfit = $profit - $totalCogs;

        // 7. Top Selling Products
        $topProducts = DB::table('order_items')
            ->select('product_id', DB::raw('SUM(qty) as total_qty'), DB::raw('SUM(sell_price * qty) as total_revenue'))
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('product_id')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        foreach ($topProducts as $tp) {
            $prod = DB::table('products')->where('id', $tp->product_id)->first();
            $tp->name = $prod ? $prod->name : 'Unknown';
            $tp->imei = $prod ? $prod->imei_serial : '';
        }

        // 8. Expense List
        $expensesList = Expense::whereBetween('expense_date', [$startDateTime, $endDateTime])
            ->orderBy('expense_date', 'desc')
            ->get();

        // 9. Purchase Orders List
        $purchaseOrdersList = PurchaseOrder::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->orderBy('created_at', 'desc')
            ->get();

        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'total_sales' => $totalSales,
            'total_purchases' => $totalPurchases,
            'total_expenses' => $totalExpenses,
            'profit' => $profit,
            'net_profit' => $netProfit,
            'top_products' => $topProducts,
            'expenses_list' => $expensesList,
            'purchase_orders_list' => $purchaseOrdersList,
        ];
    }
}
