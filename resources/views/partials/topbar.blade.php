<header class="topbar">
    <div class="topbar-left">
      <button class="menu-btn" id="menuBtn">
        <span></span><span></span><span></span>
      </button>
      <div class="page-title" id="pageTitle">Dashboard</div>
    </div>
    <div class="topbar-right">
      @if(Auth::check() && Auth::user()->type === 'store')
      <a href="{{ route('pos.index') }}" class="btn btn-primary" style="margin-right: 15px; display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; font-weight: 600; text-decoration: none;">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
        POS
      </a>
      @endif
      <button class="icon-btn theme-toggle" id="themeToggle" title="Toggle theme">
        <svg id="themeIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
      </button>
      <div class="topbar-date" id="topbarDate"></div>
    </div>
  </header>