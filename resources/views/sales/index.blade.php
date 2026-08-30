@extends('layouts.app')

@section('content')
<main class="page-content">

<!-- SALES -->
    <div class="page" id="page-sales">
      
      <!-- Stats Cards -->
      <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0; padding: 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
            <div>
                <div id="salesGrandTotal" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Total Sales</div>
            </div>
        </div>
        
        <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0; padding: 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff3e0; color: #e65100; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
            <div>
                <div id="salesTotalPaid" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Total Paid</div>
            </div>
        </div>

        <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0; padding: 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2f1; color: #00695c; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
            <div>
                <div id="salesTotalDue" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Unpaid Amount</div>
            </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>Sales History</h3>
          <div class="header-actions">
            <input type="date" class="input input-sm" id="salesStartDate" title="Start Date" onchange="renderSales()"/>
            <input type="date" class="input input-sm" id="salesEndDate" title="End Date" onchange="renderSales()"/>
            <input type="text" class="input input-sm" id="salesSearch" placeholder="Search sale..."/>
            <button class="btn btn-sm btn-outline status-filter" data-status="paid" onclick="setSalesStatusFilter('paid', this)">Paid</button>
            <button class="btn btn-sm btn-outline status-filter" data-status="partial" onclick="setSalesStatusFilter('partial', this)">Partial</button>
            <button class="btn btn-sm btn-outline status-filter" data-status="unpaid" onclick="setSalesStatusFilter('unpaid', this)">Unpaid</button>
            <button class="btn btn-sm btn-ghost" onclick="exportSalesCSV()">Export CSV</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Invoice#</th><th>Customer</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Tax</th><th>Grand Total</th><th>Paid</th><th>Status</th><th>Method</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody id="salesTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/sales.js') }}?v={{ time() }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
      const urlParams = new URLSearchParams(window.location.search);
      
      const searchParam = urlParams.get('search');
      if (searchParam) {
          const searchInput = document.getElementById('salesSearch');
          if (searchInput) {
              searchInput.value = searchParam;
              setTimeout(() => {
                  if (typeof renderSales === 'function') {
                      renderSales(1);
                      setTimeout(() => {
                          let tbody = document.getElementById('salesTbody');
                          if (tbody && tbody.children.length === 1 && tbody.children[0].querySelector('.btn-invoice')) {
                              tbody.children[0].querySelector('.btn-invoice').click();
                          }
                      }, 500); // Wait for API fetch and render
                  }
              }, 100);
          }
      }

      const invoiceId = urlParams.get('invoice');
      if (invoiceId) {
          // Open popup directly for the invoice
          let parsedId = parseInt(invoiceId, 10);
          if (!isNaN(parsedId)) {
              window.open('/shop/orders/' + parsedId + '/invoice', 'InvoicePopup', 'width=400,height=600');
          }
      }
      
      // Initial render if no search param (as renderSales is usually called on load in sales.js or here, wait sales.js doesn't call it on load!)
      // Wait, let's call renderSales if it wasn't called by search param
      if (!searchParam) {
          if (typeof renderSales === 'function') {
              renderSales(1);
          }
      }
  });
</script>
@endsection
