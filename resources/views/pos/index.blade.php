@extends('layouts.pos')

@section('content')
<main class="page-content" style="padding: 0; height: 100vh; width: 100vw; overflow: hidden; background: var(--surface2);">

    <!-- PREMIUM POS -->
    <div class="page" id="page-pos" style="height: 100%; width: 100%; display: flex; flex-direction: column;">
      
      <!-- Top Header for POS -->
      <div class="pos-premium-header">
        <div class="pos-header-left">
          <div class="brand-badge">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M2 12h20"/><circle cx="12" cy="12" r="10"/></svg>
            <span>MobiPOS Pro</span>
          </div>
          <div class="pos-clock" id="posClock">Loading time...</div>
        </div>
        <div class="pos-header-right">
            <a href="{{ url('/') }}" class="btn btn-ghost btn-sm" title="Back to Dashboard">
                <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                Back
            </a>
            <button class="btn btn-ghost btn-sm" onclick="toggleFullScreen()" title="Full Screen">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
            </button>
            <button class="btn btn-danger btn-sm" onclick="clearCart()" title="Clear Cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                Clear
            </button>
        </div>
      </div>

      <div class="pos-premium-layout">

          <!-- LEFT: Product browser -->
        <div class="pos-premium-left">

          <!-- Search & Filter bar -->
          <div class="pos-search-wrapper glass-panel">
            <div class="pos-search-inner">
                <svg viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="posSearch" class="pos-premium-search" placeholder="Scan barcode or search products..."/>
                <button class="pos-search-clear hidden" id="posSearchClear" onclick="clearPosSearch()">✕</button>
            </div>
            <select id="posCatFilter" class="pos-premium-filter">
                <option value="">All Categories</option>
            </select>
          </div>

          <!-- Product Grid -->
          <div class="pos-grid-wrapper glass-panel">
            <div class="pos-med-header" style="border-bottom: none; padding-bottom: 4px;">
              <span id="posProdCount" class="pos-med-count">All products</span>
              <div class="pos-view-toggle">
                <button class="view-btn active" id="viewImageToggle" onclick="togglePosImage()" title="Toggle Images">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                </button>
                <button class="view-btn active" id="viewGrid" onclick="setPosView('grid')" title="Grid view">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                </button>
                <button class="view-btn" id="viewList" onclick="setPosView('list')" title="List view">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </button>
              </div>
            </div>
            <div id="posProdGrid" class="pos-premium-grid"></div>
          </div>

        </div>

        <!-- RIGHT: Cart & Checkout -->
        <div class="pos-premium-right glass-panel">
          
          <!-- Cart Header -->
          <div class="cart-premium-header">
            <h3>Current Order</h3>
            <span id="cartBadge" class="cart-pulse-badge">0 items</span>
          </div>

          <!-- Cart Items -->
          <div class="cart-premium-items-wrap scrollbar-thin">
            <table class="table cart-premium-table" id="cartTable">
              <thead><tr><th>Item</th><th style="text-align:center">Qty</th><th style="text-align:right">Price</th><th></th></tr></thead>
              <tbody id="cartTbody">
                <tr><td colspan="4" class="empty-cart-state">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                  <p>Cart is empty</p>
                  <span>Scan or click items to add</span>
                </td></tr>
              </tbody>
            </table>
          </div>

          <!-- Checkout Controls -->
          <div class="checkout-premium-controls">
            
            <div class="checkout-inputs">
                <div class="form-group">
                    <label>Customer</label>
                    <div style="display:flex; gap:4px;">
                        <select id="posCustomer" class="input input-sm" style="flex:1;">
                            <option value="">Walk-in Customer</option>
                        </select>
                        <button class="btn btn-primary btn-sm" onclick="openCustModal()" title="Add Customer" style="padding:0 8px;font-size:16px;line-height:1;">+</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Payment</label>
                    <select id="posPayment" class="input input-sm">
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="online">Online</option>
                    </select>
                </div>
            </div>

            <div class="checkout-summary-box">
                <div class="sum-row">
                    <span class="sum-label">Subtotal</span>
                    <span id="sumSubtotal" class="sum-val">PKR 0.00</span>
                </div>
                <div class="sum-row">
                    <span class="sum-label">Discount (%)</span>
                    <input type="number" id="posDiscount" class="input input-xs sum-input" value="0" min="0" max="100"/>
                </div>
                <div class="sum-row">
                    <span class="sum-label">Tax (%)</span>
                    <input type="number" id="posTax" class="input input-xs sum-input" value="0" min="0" max="100"/>
                </div>
                <div class="sum-row grand-total-row">
                    <span class="sum-label">Total</span>
                    <span id="sumGrandTotal" class="sum-val highlight">PKR 0.00</span>
                </div>
                <div class="sum-row">
                    <span class="sum-label">Paid Amount</span>
                    <input type="number" id="posPaid" class="input input-sm sum-input-large" value="0" min="0"/>
                </div>
                <div class="sum-row change-row">
                    <span class="sum-label">Change Due</span>
                    <span id="sumReturn" class="sum-val due">PKR 0.00</span>
                </div>
            </div>

            <button class="btn btn-primary btn-checkout" id="checkoutBtn" onclick="checkout()">
                <span>Pay Now</span>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </button>
            
          </div>

        </div>

      </div>
    </div>

    <!-- INVOICES HAVE BEEN MOVED TO THEIR OWN FILE -->
</main>

<script>
    function toggleFullScreen() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                console.log(`Error attempting to enable fullscreen: ${err.message}`);
            });
        } else {
            document.exitFullscreen();
        }
    }

    setInterval(() => {
        const el = document.getElementById('posClock');
        if(el) {
            const now = new Date();
            el.innerText = now.toLocaleDateString() + ' ' + now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'});
        }
    }, 1000);
</script>
@endsection
