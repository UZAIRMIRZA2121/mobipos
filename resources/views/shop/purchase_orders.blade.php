@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page active" id="page-purchase-orders">
      <!-- Stats Cards -->
      <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0; padding: 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
            <div>
                <div id="poGrandTotal" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Total Amount</div>
            </div>
        </div>
        
        <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0; padding: 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #fff3e0; color: #e65100; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
            <div>
                <div id="poTotalPaid" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Total Paid</div>
            </div>
        </div>

        <div class="stat-card card" style="display: flex; align-items: center; gap: 15px; margin-bottom: 0; padding: 20px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #e0f2f1; color: #00695c; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 500;">$</div>
            <div>
                <div id="poTotalDue" style="font-size: 20px; font-weight: 700; color: #111827;">PKR 0.00</div>
                <div style="color: #6b7280; font-size: 13px; font-weight: 500;">Unpaid Amount</div>
            </div>
        </div>
      </div>

        <div class="card">
            <div class="card-header">
                <h3>Purchase Orders</h3>
                <div class="header-actions">
                    <input type="date" class="input input-sm" id="poStartDate" title="Start Date" onchange="renderPurchaseOrders()"/>
                    <input type="date" class="input input-sm" id="poEndDate" title="End Date" onchange="renderPurchaseOrders()"/>
                    <input type="text" class="input input-sm" id="poSearch" placeholder="Search purchase orders..."/>
                    <button class="btn btn-sm btn-outline po-status-filter" data-status="paid" onclick="setPOStatusFilter('paid', this)">Paid</button>
                    <button class="btn btn-sm btn-outline po-status-filter" data-status="partial" onclick="setPOStatusFilter('partial', this)">Partial</button>
                    <button class="btn btn-sm btn-outline po-status-filter" data-status="unpaid" onclick="setPOStatusFilter('unpaid', this)">Unpaid</button>
                    <button class="btn btn-primary btn-sm" onclick="openPOModal()">+ Add to Purchase</button>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Remaining</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="poTbody">
                        <tr><td colspan="9" class="empty-cell">Loading purchase orders...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/purchases.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/products.js') }}?v={{ time() }}"></script>
@endsection
