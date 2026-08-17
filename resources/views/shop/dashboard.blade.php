@extends('layouts.app')

@section('content')
<main class="page-content">

    <div class="page" id="page-dashboard">
      <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <!-- Total Earning -->
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">
            $
          </div>
          <div>
            <div id="dashTotalEarning" style="font-size: 20px; font-weight: 700; color: #111827; line-height: 1.2;">PKR 0.00</div>
            <div style="color: #6b7280; font-size: 13px; font-weight: 500; margin-top: 4px;">Total Sales</div>
          </div>
        </div>
        
        <!-- Total Expense -->
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff3e0; color: #e65100; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">
            $
          </div>
          <div>
            <div id="dashTotalExpense" style="font-size: 20px; font-weight: 700; color: #111827; line-height: 1.2;">PKR 0.00</div>
            <div style="color: #6b7280; font-size: 13px; font-weight: 500; margin-top: 4px;">Total Expense</div>
          </div>
        </div>

        <!-- Actual Earning -->
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2f1; color: #00695c; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">
            $
          </div>
          <div>
            <div id="dashActualEarning" style="font-size: 20px; font-weight: 700; color: #111827; line-height: 1.2;">PKR 0.00</div>
            <div style="color: #6b7280; font-size: 13px; font-weight: 500; margin-top: 4px;">Total Profit</div>
          </div>
        </div>

        <!-- Stock Value (Purchase) -->
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px;">
          <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff9c4; color: #fbc02d; display: flex; align-items: center; justify-content: center; font-size: 22px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
          </div>
          <div>
            <div id="dashStockValue" style="font-size: 20px; font-weight: 700; color: #111827; line-height: 1.2;">PKR 0.00</div>
            <div style="color: #6b7280; font-size: 13px; font-weight: 500; margin-top: 4px;">Stock Value (Purchase)</div>
          </div>
        </div>
      </div>
      </div>

      <!-- Daily Sales Chart -->
      <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="padding-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
          <h3 id="dailySalesTitle" style="font-size: 16px; font-weight: 600; color: #111827;">Daily Sales</h3>
          <div class="btn-group" style="display: flex; gap: 8px;">
              <button onclick="renderDashboard('week')" class="btn btn-outline btn-sm">This Week</button>
              <button onclick="renderDashboard('month')" class="btn btn-outline btn-sm">This Month</button>
              <button onclick="renderDashboard('year')" class="btn btn-outline btn-sm">This Year</button>
          </div>
        </div>
        <div class="card-body" style="padding: 10px;">
          <div id="dailySalesChart"></div>
        </div>
      </div>

      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <div class="card" style="margin-bottom: 0;">
          <div class="card-header" style="padding-bottom: 15px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #111827;">Top Selling Products (Performance)</h3>
          </div>
          <div class="table-wrap">
            <table class="table" style="font-size: 13px;">
              <thead>
                  <tr style="background: #f9fafb;">
                      <th>Product</th>
                      <th class="text-right">Qty Sold</th>
                      <th class="text-right">Revenue</th>
                  </tr>
              </thead>
              <tbody id="dashTopProductsTbody">
                  <tr><td colspan="3" class="empty-cell">No sales data available</td></tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="card" style="margin-bottom: 0;">
          <div class="card-header" style="padding-bottom: 15px;">
            <h3 style="font-size: 16px; font-weight: 600; color: #111827;">Recent Sales Reports</h3>
          </div>
          <div class="table-wrap">
            <table class="table" style="font-size: 13px;">
              <thead>
                  <tr style="background: #f9fafb;">
                      <th>Order ID</th>
                      <th>Customer</th>
                      <th class="text-right">Total</th>
                      <th class="text-right">Status</th>
                  </tr>
              </thead>
              <tbody id="dashRecentSalesTbody">
                  <tr><td colspan="4" class="empty-cell">No recent sales</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      
      <div class="card" style="margin-top: 24px;">
        <div class="card-header" style="padding-bottom: 15px;">
          <h3 style="font-size: 16px; font-weight: 600; color: #111827;">Recently Added Products</h3>
        </div>
        <div class="table-wrap">
          <table class="table" style="font-size: 13px;">
            <thead>
                <tr style="background: #f9fafb;">
                    <th>Product Name</th>
                    <th>Type</th>
                    <th>Condition</th>
                    <th class="text-right">Sale Price</th>
                    <th class="text-right">Status</th>
                </tr>
            </thead>
            <tbody id="dashRecentProdsTbody">
                <tr><td colspan="5" class="empty-cell">No products added yet</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection
