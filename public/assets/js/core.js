
// ============================================================
// STORAGE HELPERS
// ============================================================
const store = {
  get: (key, def = []) => { try { return JSON.parse(localStorage.getItem('mp_' + key)) ?? def; } catch { return def; } },
  set: (key, val) => localStorage.setItem('mp_' + key, JSON.stringify(val)),
};

// ============================================================
// DEMO DATA SEED
// ============================================================
function seedDemoData() {
  if (store.get('seeded', false)) return;

  const categories = [];

  const suppliers = [];

  const products = [
    { id: 1, name: 'iPhone 15 Pro Max', type: 'mobile', condition: 'new', color: 'Titanium', storage: '256GB', imei: '35891010101010', purchase: 300000, sale: 320000, status: 'in_stock', supplierId: 1 },
    { id: 2, name: 'Samsung Galaxy S24 Ultra', type: 'mobile', condition: 'new', color: 'Black', storage: '512GB', imei: '35123456789012', purchase: 280000, sale: 300000, status: 'in_stock', supplierId: 1 },
    { id: 3, name: 'Apple 20W Charger', type: 'accessory', condition: 'new', color: 'White', storage: 'N/A', imei: '', purchase: 4000, sale: 6000, status: 'in_stock', supplierId: 2 },
  ];

  const customers = [
    { id: 1, name: 'Muhammad Usman', phone: '0311-2345678', email: 'usman@email.com', age: 35, gender: 'male', address: 'Block A, DHA Lahore' },
    { id: 2, name: 'Fatima Malik', phone: '0322-3456789', email: 'fatima@email.com', age: 28, gender: 'female', address: 'Gulshan-e-Iqbal, Karachi' },
  ];

  const invoices = [];

  store.set('categories', categories);
  store.set('suppliers', suppliers);
  store.set('products', products);
  store.set('customers', customers);
  store.set('invoices', invoices);
  store.set('nextInvNum', 1001);
  store.set('seeded', true);
}


async function api(url, method = 'GET', body = null) {
  const opts = {
    method,
    cache: 'no-store',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      'Accept': 'application/json'
    }
  };
  if (body) {
    if (body instanceof FormData) {
      opts.body = body;
    } else {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(body);
    }
  }
  const res = await fetch(url, opts);
  if (!res.ok) {
    let errMsg = 'API Error';
    try {
      const errData = await res.json();
      if (errData.errors) {
        errMsg = Object.values(errData.errors).flat().join('<br>');
      } else {
        errMsg = errData.message || errMsg;
      }
    } catch (err) { }
    throw new Error(errMsg);
  }
  if (method !== 'DELETE') return await res.json();
  return true;
}

async function syncData() {
  // Helper to safely fetch so one missing endpoint doesn't crash everything
  const safeFetch = async (url, fallback) => {
    try {
      return await api(url);
    } catch (e) {
      console.warn(`Failed to fetch ${url}, using fallback.`);
      return store.get(fallback);
    }
  };

  try {
    const cats = await safeFetch('/shop/api/categories', 'categories') || [];
    const prods = await safeFetch('/shop/api/products', 'products') || [];
    // Force suppliers to empty array if API fails, so we don't load old offline mocks that violate DB Foreign Keys
    const custs = await safeFetch('/shop/api/customers', 'customers') || [];
    const sales = store.get('invoices') || [];

    // Map DB columns to frontend expected fields if necessary
    const mappedProds = Array.isArray(prods) ? prods.map(p => ({
      ...p,
      sale: p.sale_price,
      purchase: p.purchase_price
    })) : [];

    store.set('categories', cats);
    store.set('products', mappedProds);
    store.set('customers', custs);
    store.set('invoices', sales);

    if (document.getElementById('page-categories')) renderCategories();
    if (document.getElementById('page-products')) renderProducts();
    if (document.getElementById('page-customers')) renderCustomers();
    if (document.getElementById('page-alerts')) renderAlerts();
    if (document.getElementById('page-sales')) renderSales();
    if (document.getElementById('page-invoices')) renderInvoices();
    if (document.getElementById('page-pos')) renderPOS();
    if (document.getElementById('page-dashboard')) renderDashboard();
    if (document.getElementById('page-expenses')) renderExpenses();
    if (document.getElementById('page-purchase-orders')) renderPurchaseOrders();

  } catch (e) {
    console.error('Critical sync failure', e);
    toast('Data sync error', 'danger');
  }
}

// ============================================================
// STATE
// ============================================================
let cart = [];
let currentPage = 'dashboard';

// ============================================================
// NAVIGATION
// ============================================================
function navigate(page) { window.location.href = '/' + page; }

function renderPage(page) {
  const r = {
    'dashboard': renderDashboard,
    'products': renderProducts,
    'pos': renderPOS,
    'sales': renderSales,
    'expenses': renderExpenses,
    'purchase-orders': renderPurchaseOrders,
    'reports': renderReports,
    medicines: renderProducts, // keeping the route backward compatible for now or rename to products
    products: renderProducts,
    categories: renderCategories,
    suppliers: renderSuppliers,
    customers: renderCustomers,
    alerts: renderAlerts,
  };
  if (r[page]) r[page]();
}

// ============================================================
// REPORTS
// ============================================================
async function generateReport() {
  const sDate = document.getElementById('reportStartDate').value;
  const eDate = document.getElementById('reportEndDate').value;

  try {
    const res = await api('/shop/api/reports/generate', 'POST', { start_date: sDate, end_date: eDate });

    document.getElementById('repTotalSales').textContent = fmtCur(res.total_sales);
    document.getElementById('repTotalPurchases').textContent = fmtCur(res.total_purchases);
    document.getElementById('repTotalExpenses').textContent = fmtCur(res.total_expenses);
    document.getElementById('repProfit').textContent = fmtCur(res.profit);

    const netProfitEl = document.getElementById('repNetProfit');
    netProfitEl.textContent = fmtCur(res.net_profit);
    if (res.net_profit < 0) {
      netProfitEl.style.color = 'var(--danger)';
    } else {
      netProfitEl.style.color = '#111827';
    }

    const tbody = document.getElementById('repTopProductsTbody');
    if (res.top_products && res.top_products.length > 0) {
      tbody.innerHTML = res.top_products.map(p => `
                <tr>
                    <td>
                        <div style="font-weight:500">${p.name}</div>
                        ${p.imei ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono)">IMEI: ${p.imei}</div>` : ''}
                    </td>
                    <td class="text-right">${p.total_qty}</td>
                    <td class="text-right" style="font-weight:600">${fmtCur(p.total_revenue)}</td>
                </tr>
            `).join('');
    } else {
      tbody.innerHTML = '<tr><td colspan="3" class="empty-cell">No sales found in this period</td></tr>';
    }

    const expTbody = document.getElementById('repExpensesTbody');
    if (res.expenses_list && res.expenses_list.length > 0) {
      expTbody.innerHTML = res.expenses_list.map(e => `
                <tr>
                    <td>${e.title}</td>
                    <td>${e.description || '-'}</td>
                    <td class="text-right" style="font-weight:600; color:var(--danger)">${fmtCur(e.amount)}</td>
                </tr>
            `).join('');
    } else {
      expTbody.innerHTML = '<tr><td colspan="3" class="empty-cell">No expenses found in this period</td></tr>';
    }

    const poTbody = document.getElementById('repPurchaseOrdersTbody');
    if (res.purchase_orders_list && res.purchase_orders_list.length > 0) {
      poTbody.innerHTML = res.purchase_orders_list.map(po => `
                <tr>
                    <td>PO-${po.id}</td>
                    <td>${po.supplier_name || 'Unknown'}</td>
                    <td style="text-transform: capitalize;">
                        ${po.payment_status}
                        ${po.payment_status === 'partial' ? `<br><small style="color:var(--text-muted); text-transform:none;">Rem: ${fmtCur(po.remaining_amount)}</small>` : ''}
                    </td>
                    <td class="text-right" style="font-weight:600;">${fmtCur(po.amount)}</td>
                </tr>
            `).join('');
    } else {
      poTbody.innerHTML = '<tr><td colspan="4" class="empty-cell">No purchase orders found in this period</td></tr>';
    }

    // Set print date range title
    let printRange = 'All Time';
    if (sDate && eDate) {
      printRange = `From: ${fmtDate(sDate)} To: ${fmtDate(eDate)}`;
    } else if (sDate) {
      printRange = `From: ${fmtDate(sDate)} To: Now`;
    } else if (eDate) {
      printRange = `From: Beginning To: ${fmtDate(eDate)}`;
    }
    document.getElementById('printDateRange').textContent = printRange;

  } catch (err) {
    console.error(err);
    toast('Failed to generate report', 'danger');
  }
}

function printReport() {
  const sDate = document.getElementById('reportStartDate').value;
  const eDate = document.getElementById('reportEndDate').value;
  const printUrl = `/shop/reports/print?start_date=${sDate}&end_date=${eDate}`;

  let printFrame = document.getElementById('printFrame');
  if (!printFrame) {
    printFrame = document.createElement('iframe');
    printFrame.id = 'printFrame';
    printFrame.style.position = 'absolute';
    printFrame.style.width = '0px';
    printFrame.style.height = '0px';
    printFrame.style.border = 'none';
    document.body.appendChild(printFrame);
  }

  printFrame.onload = function () {
    printFrame.contentWindow.focus();
    printFrame.contentWindow.print();
  };

  printFrame.src = printUrl;
}

function renderReports() {
  if (document.getElementById('reportStartDate')) {
    document.getElementById('reportStartDate').value = '';
    document.getElementById('reportEndDate').value = '';

    // Automatically generate for all time
    generateReport();
  }
}

// ============================================================
// SIDEBAR TOGGLE
// ============================================================
function openSidebar() {
  if (window.innerWidth <= 992) {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('overlay').classList.add('visible');
  } else {
    document.body.classList.toggle('sidebar-collapsed');
    if (document.body.classList.contains('sidebar-collapsed')) {
      localStorage.setItem('sidebarState', 'collapsed');
    } else {
      localStorage.setItem('sidebarState', 'open');
    }
  }
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('overlay').classList.remove('visible');
}

// ============================================================
// THEME
// ============================================================
function initTheme() {
  const saved = localStorage.getItem('mp_theme') || 'light';
  document.documentElement.setAttribute('data-theme', saved);
  updateThemeIcon(saved);
}
function toggleTheme() {
  const cur = document.documentElement.getAttribute('data-theme');
  const next = cur === 'light' ? 'dark' : 'light';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('mp_theme', next);
  updateThemeIcon(next);
}
function updateThemeIcon(theme) {
  const icon = document.getElementById('themeIcon');
  if (theme === 'dark') {
    icon.innerHTML = '<path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>';
  } else {
    icon.innerHTML = '<circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>';
  }
}

// ============================================================
// TOAST
// ============================================================
function toast(msg, type = 'success') {
  const icons = { success: '✓', warning: '⚠', danger: '✕', info: 'ℹ' };
  const el = document.createElement('div');
  el.className = `toast ${type}`;
  el.innerHTML = `<span class="toast-icon">${icons[type] || '✓'}</span><span class="toast-msg">${msg}</span><button class="toast-close" onclick="this.parentElement.remove()">✕</button>`;
  document.getElementById('toastContainer').appendChild(el);
  setTimeout(() => { el.style.animation = 'fadeOut 0.3s ease forwards'; setTimeout(() => el.remove(), 300); }, 3500);
}

// ============================================================
// CONFIRM MODAL
// ============================================================
let confirmCallback = null;
function confirmDelete(msg, cb) {
  document.getElementById('confirmMsg').textContent = msg || 'Are you sure?';
  confirmCallback = cb;
  document.getElementById('confirmModal').classList.remove('hidden');
  document.getElementById('confirmOkBtn').onclick = () => { cb(); closeConfirmModal(); };
}
function closeConfirmModal() { document.getElementById('confirmModal').classList.add('hidden'); confirmCallback = null; }

// ============================================================
// HELPERS
// ============================================================
function fmtCur(n) { return 'PKR ' + Number(n).toLocaleString('en-PK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function fmtDate(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('en-GB'); }
function fmtDateTime(d) { if (!d) return '-'; return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }); }
function daysDiff(dateStr) { return Math.ceil((new Date(dateStr) - new Date()) / 86400000); }
function isExpired(d) { return new Date(d) < new Date(); }
function isLowStock(m) { return m.stock <= m.lowStock; }
function getCategory(id) { return store.get('categories').find(c => c.id == id) || {}; }

function getProdBadge(p) {
  if (p.status === 'in_repair') return `<span class="badge badge-warning">IN REPAIR</span>`;
  if (p.stock <= 0 || p.status === 'defective') return `<span class="badge badge-danger">OUT OF STOCK</span>`;
  if (p.stock < 10) return `<span class="badge badge-warning">LOW STOCK (${p.stock})</span>`;
  return `<span class="badge badge-success">IN STOCK (${p.stock})</span>`;
}
function getSupplier(id) { return store.get('suppliers').find(s => s.id == id) || {}; }
function getCustomer(id) { return store.get('customers').find(c => c.id == id) || {}; }
function nextId(arr) { return arr.length ? Math.max(...arr.map(x => x.id)) + 1 : 1; }
function svgIcon(path, size = 13) { return `<svg width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${path}</svg>`; }

const EDIT_SVG = svgIcon('<path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>');
const DEL_SVG = svgIcon('<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/>');
const VIEW_SVG = svgIcon('<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>');
const REFUND_SVG = svgIcon('<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>');

// ============================================================
// ALERT BADGE UPDATE
// ============================================================
function updateAlertBadge() {
  const meds = store.get('medicines');
  const count = meds.filter(m => isExpired(m.expiry) || isLowStock(m)).length;
  const badge = document.getElementById('alertBadge');
  badge.textContent = count;
  badge.style.display = count > 0 ? 'inline-flex' : 'none';
}

// ============================================================
// ALERTS
// ============================================================
function renderAlerts() {
  const meds = store.get('medicines');
  const cats = store.get('categories');
  const catName = id => (cats.find(c => c.id == id) || {}).name || '-';

  const expired = meds.filter(m => isExpired(m.expiry));
  const expiringSoon = meds.filter(m => { const d = daysDiff(m.expiry); return d >= 0 && d <= 30; });
  const lowStock = meds.filter(m => isLowStock(m));

  document.getElementById('expiredTbody').innerHTML = expired.length ?
    expired.map(m => `<tr>
      <td style="font-weight:500">${m.name}</td>
      <td>${catName(m.catId)}</td>
      <td>${m.stock}</td>
      <td>${fmtDate(m.expiry)}</td>
      <td><span class="badge badge-danger">${Math.abs(daysDiff(m.expiry))} days ago</span></td>
    </tr>`).join('') :
    '<tr><td colspan="5" class="empty-cell">No expired medicines</td></tr>';

  document.getElementById('expiringSoonTbody').innerHTML = expiringSoon.length ?
    expiringSoon.map(m => `<tr>
      <td style="font-weight:500">${m.name}</td>
      <td>${catName(m.catId)}</td>
      <td>${m.stock}</td>
      <td>${fmtDate(m.expiry)}</td>
      <td><span class="badge badge-warning">${daysDiff(m.expiry)} days</span></td>
    </tr>`).join('') :
    '<tr><td colspan="5" class="empty-cell">No medicines expiring soon</td></tr>';

  document.getElementById('lowStockTbody').innerHTML = lowStock.length ?
    lowStock.map(m => `<tr>
      <td style="font-weight:500">${m.name}</td>
      <td>${catName(m.catId)}</td>
      <td><span class="badge badge-warning">${m.stock} / ${m.lowStock}</span></td>
      <td>${m.rack || '-'}</td>
      <td>${(getSupplier(m.supplierId) || {}).name || '-'}</td>
    </tr>`).join('') :
    '<tr><td colspan="5" class="empty-cell">No low stock medicines</td></tr>';
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  // seedDemoData();
  syncData();
  initTheme();

  // Date display
  const tick = () => {
    document.getElementById('topbarDate').textContent = new Date().toLocaleString('en-GB', {
      weekday: 'short', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
  };
  tick();
  setInterval(tick, 60000);

  // Navigation
  document.querySelectorAll('.nav-item[data-page]').forEach(el => {
    el.addEventListener('click', () => navigate(el.dataset.page));
  });

  // Theme toggle
  document.getElementById('themeToggle').addEventListener('click', toggleTheme);

  // Menu
  document.getElementById('menuBtn').addEventListener('click', openSidebar);
  document.getElementById('sidebarClose').addEventListener('click', closeSidebar);
  document.getElementById('overlay').addEventListener('click', closeSidebar);

  updateAlertBadge();
  // navigate('dashboard'); // Removed to prevent infinite reload loop in MPA mode
});

