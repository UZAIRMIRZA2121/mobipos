<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon" style="background: transparent;">
      <img src="{{ asset('assets/logo/main-logo.png') }}" style="max-height: 36px; max-width: 36px; object-fit: contain;" alt="Logo">
    </div>
    <div class="brand-text">
      <span class="brand-name">MobiPOS</span>
      <span class="brand-sub">POS System</span>
    </div>
    <button class="sidebar-close" id="sidebarClose">×</button>
  </div>

  <nav class="sidebar-nav">
    @if(Auth::check() && Auth::user()->type === 'seller')
    <div class="nav-group">
      <span class="nav-group-label">Seller Portal</span>
      <a class="nav-item {{ request()->routeIs('seller.dashboard') ? 'active' : '' }}" href="{{ route('seller.dashboard') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
    </div>
    @elseif(Auth::check() && Auth::user()->type === 'admin')
    <div class="nav-group">
        <span class="nav-group-label">Admin Portal</span>
        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
            <span>Dashboard</span>
        </a>
    </div>
    @elseif(Auth::check())
    <div class="nav-group">
      <span class="nav-group-label">Overview</span>
      <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
    </div>
    <div class="nav-group">
      <span class="nav-group-label">Sales</span>
      <a class="nav-item" href="{{ route('shop.pos.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
        POS / Billing
      </a>
      <a class="nav-item" href="{{ route('shop.sales.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Sales History
      </a>
    </div>
    <div class="nav-group">
      <span class="nav-group-label">Inventory</span>
      <a class="nav-item" href="{{ route('shop.products.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
        Mobiles & Products
      </a>
    </div>
    <div class="nav-group">
      <span class="nav-group-label">Contacts</span>

      <a class="nav-item" href="{{ route('shop.customers.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Customers
      </a>
    </div>
    @endif
  </nav>

  <div class="sidebar-footer" style="display:flex; align-items:center; justify-content:space-between;">
    <div class="user-info">
      <div class="user-avatar">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
      <div class="user-meta">
        <span class="user-name">{{ Auth::user()->name ?? 'Admin' }}</span>
        <span class="user-role">{{ ucfirst(Auth::user()->type ?? 'Admin') }}</span>
      </div>
    </div>
    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
      @csrf
      <button type="submit" class="btn btn-ghost" style="padding:8px; color:var(--danger);" title="Logout">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
          <polyline points="16 17 21 12 16 7"></polyline>
          <line x1="21" y1="12" x2="9" y2="12"></line>
        </svg>
      </button>
    </form>
  </div>
</aside>