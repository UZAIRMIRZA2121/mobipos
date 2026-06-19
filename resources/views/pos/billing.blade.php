@extends('layouts.app')

@section('content')
<main class="page-content">

<!-- POS -->
    <div class="page" id="page-pos">
      <div class="pos-layout">

        <!-- LEFT: Medicine browser + Cart -->
        <div class="pos-left">

          <!-- Search & Filter bar -->
          <div class="card pos-search-card">
            <div class="pos-topbar">
              <div class="pos-search-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="posSearch" class="pos-search-input" placeholder="Search medicine by name, generic or barcode..."/>
                <button class="pos-search-clear hidden" id="posSearchClear" onclick="clearPosSearch()">×</button>
              </div>
              <select id="posCatFilter" class="input input-sm" style="width:160px;flex-shrink:0">
                <option value="">All Categories</option>
              </select>
            </div>
            <div class="pos-cat-tabs" id="posCatTabs"></div>
          </div>

          <!-- Medicine Grid -->
          <div class="card">
            <div class="pos-med-header">
              <span id="posMedCount" class="pos-med-count">All medicines</span>
              <div class="pos-view-toggle">
                <button class="view-btn active" id="viewGrid" onclick="setPosView('grid')" title="Grid view">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </button>
                <button class="view-btn" id="viewList" onclick="setPosView('list')" title="List view">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
              </div>
            </div>
            <div id="posMedGrid" class="pos-med-grid"></div>
          </div>

          <!-- Cart -->
          <div class="card">
            <div class="card-header">
              <h3>Cart Items</h3>
              <span id="cartBadge" class="badge badge-primary">0 items</span>
            </div>
            <div class="table-wrap">
              <table class="table" id="cartTable">
                <thead><tr><th>Medicine</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
                <tbody id="cartTbody"><tr><td colspan="5" class="empty-cell">Cart is empty — click any medicine above to add</td></tr></tbody>
              </table>
            </div>
          </div>

          <!-- Notes + Customer row -->
          <div class="card">
            <div class="pos-bottom-row">
              <div class="form-group" style="flex:1">
                <label>Customer</label>
                <select id="posCustomer" class="input">
                  <option value="">Walk-in Customer</option>
                </select>
              </div>
              <div class="form-group" style="flex:1">
                <label>Payment Method</label>
                <select id="posPayment" class="input">
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                  <option value="online">Online Payment</option>
                </select>
              </div>
              <div class="form-group" style="flex:2">
                <label>Invoice Notes</label>
                <input type="text" id="posNotes" class="input" placeholder="Optional notes..."/>
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT: Bill Summary -->
        <div class="pos-right">
          <div class="card pos-summary">
            <div class="card-header"><h3>Bill Summary</h3></div>
            <div class="summary-rows">
              <div class="sum-row"><span>Total Items</span><span id="sumItems">0</span></div>
              <div class="sum-row"><span>Subtotal</span><span id="sumSubtotal">PKR 0.00</span></div>
              <div class="sum-row">
                <span>Discount (%)</span>
                <input type="number" id="posDiscount" class="input input-sm" value="0" min="0" max="100" style="width:70px"/>
              </div>
              <div class="sum-row">
                <span>Tax (%)</span>
                <input type="number" id="posTax" class="input input-sm" value="0" min="0" max="100" style="width:70px"/>
              </div>
              <div class="sum-row sum-total"><span>Grand Total</span><span id="sumGrandTotal">PKR 0.00</span></div>
              <div class="sum-row">
                <span>Paid Amount</span>
                <input type="number" id="posPaid" class="input input-sm" value="0" min="0" style="width:100px"/>
              </div>
              <div class="sum-row"><span>Due Amount</span><span id="sumDue">PKR 0.00</span></div>
              <div class="sum-row"><span>Return Amount</span><span id="sumReturn">PKR 0.00</span></div>
            </div>
            <button class="btn btn-primary btn-full" id="checkoutBtn" onclick="checkout()">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><polyline points="20 12 20 22 4 22 4 12"/><rect x="2" y="7" width="20" height="5"/><path d="M12 22V7"/><path d="M12 7H7.5a2.5 2.5 0 010-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 000-5C13 2 12 7 12 7z"/></svg>
              Generate Invoice
            </button>
            <button class="btn btn-ghost btn-full" onclick="clearCart()">Clear Cart</button>
            <label style="display:flex; align-items:center; gap:8px; margin-top:12px; font-size:13px; cursor:pointer; color:var(--text-muted)">
              <input type="checkbox" id="autoPrint" checked> Auto Print Thermal Receipt
            </label>
          </div>
        </div>
      </div>
    </div>

</main>
@endsection
