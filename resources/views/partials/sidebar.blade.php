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

      @if(Auth::user()->hasPrivilege('shop.dashboard'))
      <a class="nav-item" href="{{ route('shop.dashboard') }}" onclick="navigate('dashboard')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        Dashboard
      </a>
      @endif
      @if(Auth::user()->hasPrivilege('shop.reports.index'))
      <a class="nav-item" href="{{ route('shop.reports.index') }}" onclick="navigate('reports')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        Reports
      </a>
      @endif
    </div>
    <div class="nav-group">
      <span class="nav-group-label">Sales</span>
      @if(Auth::user()->hasPrivilege('shop.pos.index'))
      <a class="nav-item" href="{{ route('shop.pos.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
        POS / Billing
      </a>
      @endif
      @if(Auth::user()->hasPrivilege('shop.sales.index'))
      <a class="nav-item" href="{{ route('shop.sales.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Sales History
      </a>
      @endif
      @if(Auth::user()->hasPrivilege('shop.expenses.index'))
      <a class="nav-item {{ request()->routeIs('shop.expenses.index') ? 'active' : '' }}" href="{{ route('shop.expenses.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="6" width="20" height="12" rx="2"/><path d="M12 12h.01"/><path d="M17 12h.01"/><path d="M7 12h.01"/></svg>
        Expenses
      </a>
      @endif
    </div>
    <div class="nav-group">
      <span class="nav-group-label">Inventory</span>
      @if(Auth::user()->hasPrivilege('shop.products.index'))
      <a class="nav-item" href="{{ route('shop.products.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
        Mobiles & Products
      </a>
      @endif
      @if(Auth::user()->hasPrivilege('shop.categories.index'))
      <a class="nav-item {{ request()->routeIs('shop.categories.index') ? 'active' : '' }}" href="{{ route('shop.categories.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/></svg>
        Categories
      </a>
      @endif
      @if(Auth::user()->hasPrivilege('shop.purchase_orders.index'))
      <a class="nav-item {{ request()->routeIs('shop.purchase_orders.index') ? 'active' : '' }}" href="{{ route('shop.purchase_orders.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        Purchase Orders
      </a>
      @endif
      @if(Auth::user()->hasPrivilege('shop.settings.print'))
      <a class="nav-item" href="{{ route('shop.settings.print') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
        Print Settings
      </a>
      @endif
      @if(Auth::user()->hasPrivilege('shop.settings.index'))
      <a class="nav-item {{ request()->routeIs('shop.settings.index') ? 'active' : '' }}" href="{{ route('shop.settings.index') }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a,2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        Store Settings
      </a>
      @endif
    </div>
    <div class="nav-group">
      <span class="nav-group-label">Contacts</span>

      @if(Auth::user()->hasPrivilege('shop.customers.index'))
      <a class="nav-item" href="{{ route('shop.customers.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Customers
      </a>
      @endif

      @if(Auth::user()->hasPrivilege('shop.staff.index'))
      <a class="nav-item {{ request()->routeIs('shop.staff.*') ? 'active' : '' }}" href="{{ route('shop.staff.index') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        Staff Management
      </a>
      @endif
    </div>

    <div class="nav-group">
      <span class="nav-group-label">Account</span>
      @if(Auth::user()->hasPrivilege('shop.profile'))
      <a class="nav-item {{ request()->routeIs('shop.profile') ? 'active' : '' }}" href="{{ route('shop.profile') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        Profile Settings
      </a>
      @endif
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