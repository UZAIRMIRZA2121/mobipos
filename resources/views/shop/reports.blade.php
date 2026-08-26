@extends('layouts.app')

@section('title', 'Reports')

@section('content')
<style>
    @media print {
        #sidebar, .navbar, .page-header button, .filters {
            display: none !important;
        }
        .page-content {
            margin-left: 0 !important;
            padding: 0 !important;
        }
        body {
            background: white !important;
        }
        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
            margin-bottom: 20px !important;
        }
        .print-title {
            display: block !important;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    }
    .print-title {
        display: none;
    }
</style>

<main class="page-content">
    <div class="page active" id="page-reports">
        <div class="print-title">
            Sales & Performance Report
            <p id="printDateRange" style="font-size: 14px; font-weight: normal; color: #555;"></p>
        </div>

        <div class="page-header" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px;">
            <h2 style="margin-bottom: 4px;">Reports</h2>
            <div class="filters" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                <div class="quick-filters" style="display: flex; gap: 5px;">
                    <button class="btn btn-outline filter-btn" onclick="setReportDateRange('today', this)" style="height: 36px; padding: 0 10px; font-size: 13px;">Today</button>
                    <button class="btn btn-outline filter-btn" onclick="setReportDateRange('yesterday', this)" style="height: 36px; padding: 0 10px; font-size: 13px;">Last Day</button>
                    <button class="btn btn-outline filter-btn" onclick="setReportDateRange('this_month', this)" style="height: 36px; padding: 0 10px; font-size: 13px;">This Month</button>
                    <button class="btn btn-outline filter-btn" onclick="setReportDateRange('this_year', this)" style="height: 36px; padding: 0 10px; font-size: 13px;">This Year</button>
                </div>
                <div class="form-group" style="margin: 0; width: 160px;">
                    <label style="font-size: 12px; margin-bottom: 4px;">Start Date</label>
                    <input type="date" id="reportStartDate" class="input" style="height: 36px;">
                </div>
                <div class="form-group" style="margin: 0; width: 160px;">
                    <label style="font-size: 12px; margin-bottom: 4px;">End Date</label>
                    <input type="date" id="reportEndDate" class="input" style="height: 36px;">
                </div>
                <button class="btn btn-primary" onclick="generateReport()" style="height: 36px; padding: 0 16px;">Generate Report</button>
                <button class="btn btn-secondary" onclick="printReport()" style="height: 36px; padding: 0 16px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:6px; vertical-align:middle;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                    Print
                </button>
            </div>
        </div>

        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
            <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
                <div>
                    <div id="repTotalSales" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                    <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Total Sales</div>
                </div>
            </div>

            <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #ffebee; color: #c62828; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
                <div>
                    <div id="repTotalExpenses" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                    <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Total Expenses</div>
                </div>
            </div>

            <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #e3f2fd; color: #1565c0; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
                <div>
                    <div id="repNetProfit" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                    <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Profit</div>
                </div>
            </div>

            <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #f3e5f5; color: #7b1fa2; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
                <div>
                    <div id="repProfit" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                    <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Profit After Expense</div>
                </div>
            </div>

            <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff3e0; color: #e65100; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
                <div>
                    <div id="repTotalPurchases" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                    <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Total Purchases</div>
                </div>
            </div>
        </div>

        <div class="row" style="display: flex; flex-wrap: wrap; margin-right: -12px; margin-left: -12px;">
            <!-- Top Selling Products -->
            <div class="col-md-4" style="flex: 0 0 33.3333%; max-width: 33.3333%; padding-right: 12px; padding-left: 12px;">
                <div class="card" style="margin-bottom: 24px; height: calc(100% - 24px);">
                    <div class="card-header">
                        <h3>Top Selling Products</h3>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr style="background: #f9fafb;">
                                    <th>Product Name</th>
                                    <th class="text-right">Quantity Sold</th>
                                    <th class="text-right">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody id="repTopProductsTbody">
                                <tr><td colspan="3" class="empty-cell">Generate report to view data</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Expenses List -->
            <div class="col-md-4" style="flex: 0 0 33.3333%; max-width: 33.3333%; padding-right: 12px; padding-left: 12px;">
                <div class="card" style="margin-bottom: 24px; height: calc(100% - 24px);">
                    <div class="card-header">
                        <h3>Expenses List</h3>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr style="background: #f9fafb;">
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="repExpensesTbody">
                                <tr><td colspan="3" class="empty-cell">Generate report to view data</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Purchase Orders List -->
            <div class="col-md-4" style="flex: 0 0 33.3333%; max-width: 33.3333%; padding-right: 12px; padding-left: 12px;">
                <div class="card" style="margin-bottom: 24px; height: calc(100% - 24px);">
                    <div class="card-header">
                        <h3>Purchase Orders List</h3>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr style="background: #f9fafb;">
                                    <th>PO Number</th>
                                    <th>Supplier</th>
                                    <th>Status</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="repPurchaseOrdersTbody">
                                <tr><td colspan="4" class="empty-cell">Generate report to view data</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof renderReports === 'function') {
            renderReports();
        }
    });

    function setReportDateRange(type, btn) {
        if (btn) {
            const buttons = document.querySelectorAll('.filter-btn');
            buttons.forEach(b => {
                b.classList.remove('btn-primary');
                b.classList.add('btn-outline');
            });
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-primary');
        }

        const today = new Date();
        let start = new Date();
        let end = new Date();
        
        if (type === 'today') {
            // keep as today
        } else if (type === 'yesterday') {
            start.setDate(today.getDate() - 1);
            end.setDate(today.getDate() - 1);
        } else if (type === 'this_month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
        } else if (type === 'this_year') {
            start = new Date(today.getFullYear(), 0, 1);
        }
        
        const fmt = (d) => {
            const yr = d.getFullYear();
            const mo = String(d.getMonth() + 1).padStart(2, '0');
            const da = String(d.getDate()).padStart(2, '0');
            return `${yr}-${mo}-${da}`;
        };
        
        document.getElementById('reportStartDate').value = fmt(start);
        document.getElementById('reportEndDate').value = fmt(end);
        
        if (typeof generateReport === 'function') {
            generateReport();
        }
    }
</script>
@endsection
