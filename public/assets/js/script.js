
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
            errMsg = errData.message || errMsg;
        } catch(err) {}
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
        } catch(e) {
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
            purchase: p.purchase_price, 
            imei: p.imei_serial 
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

    } catch(e) {
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
    dashboard: renderDashboard,
    pos: renderPOS,
    invoices: renderInvoices,
    sales: renderSales,
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
// SIDEBAR TOGGLE
// ============================================================
function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('overlay').classList.add('visible');
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
function fmtDateTime(d) { if (!d) return '-'; return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }); }
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
// DASHBOARD
// ============================================================
function renderDashboard() {
  const prods = store.get('products') || [];
  const sales = store.get('invoices') || [];
  
  if (document.getElementById('dashTotalProds')) {
      document.getElementById('dashTotalProds').textContent = prods.length;
      
      const totalVal = prods.reduce((sum, p) => sum + (Number(p.sale) || 0), 0);
      document.getElementById('dashTotalValue').textContent = fmtCur(totalVal);
      
      document.getElementById('dashTotalSales').textContent = sales.length;
      
      // Get 5 most recent products
      const recent = [...prods].reverse().slice(0, 5);
      
      document.getElementById('dashRecentProdsTbody').innerHTML = recent.length ?
        recent.map(p => `
          <tr>
            <td>
              <div style="font-weight:500">${p.name}</div>
              ${p.imei ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono)">IMEI/SN: ${p.imei}</div>` : ''}
            </td>
            <td style="text-transform:capitalize">${p.type}</td>
            <td style="text-transform:capitalize">${p.condition}</td>
            <td style="font-weight:600">${fmtCur(p.sale)}</td>
            <td>
              ${getProdBadge(p)}
            </td>
          </tr>
        `).join('') : '<tr><td colspan="5" class="empty-cell">No products added yet</td></tr>';
  }
}

// ============================================================
// POS
// ============================================================
function renderPOS() {
    // Populate customer select
    const custSel = document.getElementById('posCustomer');
    if (custSel) {
        const currentCust = custSel.value;
        custSel.innerHTML = '<option value="">Walk-in Customer</option>' +
          store.get('customers').map(c => `<option value="${c.id}">${c.name} — ${c.phone}</option>`).join('');
        if (currentCust) custSel.value = currentCust;
        if (window.lastCreatedCustomerId) {
            custSel.value = window.lastCreatedCustomerId;
            window.lastCreatedCustomerId = null;
        }
    }

  // Populate category tabs (if exists)
  const cats = store.get('categories');
  const tabs = document.getElementById('posCatTabs');
  if (tabs) {
    tabs.innerHTML = `<button class="cat-tab active" data-cat="" onclick="filterPosCat(this,'')">All</button>` +
      cats.map(c => `<button class="cat-tab" data-cat="${c.id}" onclick="filterPosCat(this,${c.id})" style="--cat-color:${c.color}">${c.name}</button>`).join('');
  }

  // Populate category select (dropdown)
  const catSel = document.getElementById('posCatFilter');
  catSel.innerHTML = '<option value="">All Categories</option>' +
    cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

  renderProdGrid();
  renderCart();
}

// State for POS filters
let posFilter = { q: '', catId: '', view: 'grid', showImage: true };

function filterPosCat(btn, catId) {
  posFilter.catId = catId;
  document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
  btn.classList.add('active');
  const sel = document.getElementById('posCatFilter');
  if (sel) sel.value = catId;
  renderProdGrid();
}

function setPosView(v) {
  posFilter.view = v;
  document.getElementById('viewGrid').classList.toggle('active', v === 'grid');
  document.getElementById('viewList').classList.toggle('active', v === 'list');
  const grid = document.getElementById('posProdGrid');
  if(grid) grid.classList.toggle('list-view', v === 'list');
  renderProdGrid();
}

function togglePosImage() {
  posFilter.showImage = !posFilter.showImage;
  const btn = document.getElementById('viewImageToggle');
  if(btn) btn.classList.toggle('active', posFilter.showImage);
  renderProdGrid();
}

function clearPosSearch() {
  document.getElementById('posSearch').value = '';
  posFilter.q = '';
  document.getElementById('posSearchClear').classList.add('hidden');
  renderProdGrid();
}

function renderProdGrid() {
  let prods = store.get('products');
  const q = posFilter.q.toLowerCase();
  if (q) prods = prods.filter(p =>
    p.name.toLowerCase().includes(q) ||
    (p.imei || '').toLowerCase().includes(q) ||
    (p.color || '').toLowerCase().includes(q) ||
    (p.storage || '').toLowerCase().includes(q)
  );
  // We don't have categories mapped exactly yet, skipping category filter unless implemented
  // if (posFilter.catId) prods = prods.filter(p => p.catId == posFilter.catId);

  const countEl = document.getElementById('posProdCount');
  if(countEl) countEl.textContent = q ? `${prods.length} result${prods.length !== 1 ? 's' : ''}` : `${prods.length} products`;

  const grid = document.getElementById('posProdGrid');
  if(!grid) return;
  grid.classList.toggle('list-view', posFilter.view === 'list');

  if (!prods.length) {
    grid.innerHTML = `<div class="pos-no-results">
      <div class="pos-no-results-icon">ðŸ” </div>
      <div style="font-weight:600;margin-bottom:4px">No products found</div>
      <div style="font-size:12px">Try a different search</div>
    </div>`;
    return;
  }

  if (posFilter.view === 'list') {
    grid.innerHTML = prods.map(p => buildProdRow(p)).join('');
  } else {
    grid.innerHTML = prods.map(p => buildProdCard(p)).join('');
  }
}

function buildProdCard(p) {
  const oos = p.stock <= 0 || p.status === 'defective' || p.status === 'in_repair';
  const inCart = cart.find(c => c.prodId == p.id);
  const cartQty = inCart ? inCart.qty : 0;

  let stockBg = '#d1fae5'; let stockColor = '#065f46'; let stockText = 'In Stock (' + p.stock + ')';
  if (oos) { stockBg = '#fee2e2'; stockColor = '#991b1b'; stockText = 'Out of Stock'; }
  else if (p.stock < 10) { stockBg = '#fef3c7'; stockColor = '#92400e'; stockText = 'Low Stock (' + p.stock + ')'; }

  return `<div class="med-card${oos ? ' out-of-stock' : ''}${inCart ? ' in-cart' : ''}" onclick="addToCart(${p.id})" style="position:relative; overflow:hidden;">
    ${(posFilter.showImage && p.image) ? `<img src="/storage/${p.image}" alt="${p.name}" style="width:calc(100% + 24px); height:80px; object-fit:cover; margin:-12px -12px 12px -12px; display:block;">` : ''}
    ${inCart ? `<div class="med-card-incart" style="position:absolute; right:8px; top:8px; background:var(--success); color:white; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; z-index:2; box-shadow:0 2px 4px rgba(0,0,0,0.2);">${cartQty}</div>` : ''}
    <div class="med-card-cat">${p.type || 'Phone'} - ${p.condition || 'Used'}</div>
    <div class="med-card-name" style="margin-bottom:2px; display:flex; justify-content:space-between; align-items:flex-start; gap:4px;">
      <span>${p.name}</span>
      <span style="font-weight:800; font-size:14px; color:var(--primary);">${fmtCur(p.sale)}</span>
    </div>
    ${p.storage || p.color || p.imei ? `<div class="med-card-generic" style="font-size:10.5px; color:var(--text-muted); line-height:1.2; margin-bottom:auto; padding-bottom:8px;">
      ${p.storage ? `<b>${p.storage}</b> ` : ''} ${p.color ? `· ${p.color}` : ''}
      ${p.imei ? `<br><span style="font-family:monospace; font-size:9.5px;">SN: ${p.imei}</span>` : ''}
    </div>` : ''}
    <div class="med-card-footer" style="margin-top:auto; padding-top:6px; border-top:1px dashed var(--border-light); display:flex; justify-content:flex-end; align-items:center;">
      <span class="med-stock" style="font-size:10px; font-weight:600; padding:2px 6px; border-radius:10px; background:${stockBg}; color:${stockColor};">${stockText}</span>
    </div>
  </div>`;
}

function buildProdRow(p) {
  const oos = p.status === 'sold' || p.status === 'defective';
  const inCart = cart.find(c => c.prodId == p.id);

  return `<div class="med-row${oos ? ' out-of-stock' : ''}${inCart ? ' in-cart' : ''}" onclick="addToCart(${p.id})">
    <div class="med-row-info">
      <div class="med-row-name">${p.name} ${inCart ? `<span class="badge badge-success" style="font-size:10px">In cart ✕${inCart.qty}</span>` : ''}
      </div>
      <div class="med-row-meta">
        ${p.storage ? p.storage + ' Â· ' : ''}
        ${p.color ? p.color + ' Â· ' : ''}
        Status: <strong>${p.status.replace('_', ' ').toUpperCase()}</strong>
      </div>
    </div>
    <div class="med-row-price">${fmtCur(p.sale)}</div>
  </div>`;
}

// POS Search
let posSearchTimeout = null;
document.addEventListener('DOMContentLoaded', () => {
  const posSearchEl = document.getElementById('posSearch');
  if (posSearchEl) {
    posSearchEl.addEventListener('input', function () {
      posFilter.q = this.value;
      const clearBtn = document.getElementById('posSearchClear');
      if (clearBtn) clearBtn.classList.toggle('hidden', !this.value);
      clearTimeout(posSearchTimeout);
      posSearchTimeout = setTimeout(renderProdGrid, 150);
    });
  }

  const catSel = document.getElementById('posCatFilter');
  if (catSel) {
    catSel.addEventListener('change', function () {
      posFilter.catId = this.value;
      // Sync tab highlight
      document.querySelectorAll('.cat-tab').forEach(t => {
        t.classList.toggle('active', t.dataset.cat == this.value || (!this.value && t.dataset.cat === ''));
      });
      renderProdGrid();
    });
  }

  // Live summary updates
  ['posDiscount', 'posTax', 'posPaid'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.addEventListener('input', renderCartSummary);
  });
});

function addToCart(prodId) {
  const p = store.get('products').find(m => m.id == prodId);
  if (!p) return;
    if (p.stock <= 0 || p.status === 'defective' || p.status === 'in_repair') { toast('Product is unavailable!', 'danger'); return; }

  const existing = cart.find(c => c.prodId == prodId);
  if (existing) {
    if (existing.qty >= p.stock) { toast('Not enough stock!', 'warning'); return; }
    existing.qty++;
    existing.sub = existing.qty * existing.price;
  } else {
    if (p.stock <= 0) { toast('Out of stock!', 'danger'); return; }
    cart.push({ prodId: p.id, name: p.name, price: parseFloat(p.sale), qty: 1, sub: parseFloat(p.sale), maxStock: p.stock });
  }
  renderCart();
  renderProdGrid();
  toast(`${p.name} added`, 'success');
}

function changeQty(prodId, delta) {
  const item = cart.find(c => c.prodId == prodId);
  if (!item) return;
  const newQty = item.qty + delta;
  if (newQty <= 0) { removeFromCart(prodId); return; }
  if (newQty > item.maxStock) { toast('Not enough stock!', 'warning'); return; }
  item.qty = newQty;
  item.sub = item.qty * item.price;
  renderCart();
  renderProdGrid();
}

function changePrice(prodId, newPrice) {
  const item = cart.find(c => c.prodId == prodId);
  if (!item) return;
  const p = parseFloat(newPrice);
  if (isNaN(p) || p < 0) return;
  item.price = p;
  item.sub = item.qty * item.price;
  renderCart();
  renderProdGrid();
}

function removeFromCart(prodId) {
  cart = cart.filter(c => c.prodId != prodId);
  renderCart();
  renderProdGrid();
}

function clearCart() {
  cart = [];
  renderCart();
  renderProdGrid();
  const d = document.getElementById('posDiscount'); if(d) d.value = 0;
  const t = document.getElementById('posTax'); if(t) t.value = 0;
  const p = document.getElementById('posPaid'); if(p) p.value = 0;
  const n = document.getElementById('posNotes'); if(n) n.value = '';
}

function renderCart() {
    const tbody = document.getElementById('cartTbody');
    const badge = document.getElementById('cartBadge');
    
    let sumSub = 0, totalItems = 0;
    cart.forEach(item => {
      sumSub += item.price * item.qty;
      totalItems += item.qty;
    });
    
    if (badge) badge.textContent = totalItems + ' item' + (totalItems !== 1 ? 's' : '');
    if(document.getElementById('sumItems')) document.getElementById('sumItems').textContent = totalItems;
    if(document.getElementById('sumSubtotal')) document.getElementById('sumSubtotal').textContent = fmtCur(sumSub);
    
    if (!cart.length) {
      if (tbody) tbody.innerHTML = `<tr><td colspan="4" class="empty-cart-state">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
        <p>Cart is empty</p>
        <span>Scan or click items to add</span>
      </td></tr>`;
      renderCartSummary();
      return;
    }
  
    tbody.innerHTML = cart.map(item =>
      `<tr>
        <td>
          <div class="cart-item-name">${item.name}</div>
        </td>
        <td style="text-align:center">
          <div class="qty-control">
            <button class="qty-btn" onclick="changeQty(${item.prodId}, -1)">−</button>
            <span class="qty-display">${item.qty}</span>
            <button class="qty-btn" onclick="changeQty(${item.prodId}, 1)">+</button>
          </div>
        </td>
          <td style="text-align:right;">
             <input type="number" class="input input-sm" style="width: 75px; text-align: right; padding: 2px 4px; height: 26px; font-size: 13px; display: inline-block; font-weight: 600; color: var(--primary);" value="${item.price}" onchange="changePrice(${item.prodId}, this.value)" title="Edit Price">
          </td>
          <td style="text-align:right"><button class="action-btn del" style="width:24px;height:24px;padding:4px;" onclick="removeFromCart(${item.prodId})">${DEL_SVG}</button></td>
        </tr>`
    ).join('');
    renderCartSummary();
}

function renderCartSummary() {
  const subtotal = cart.reduce((s, c) => s + c.sub, 0);
  const discount = parseFloat(document.getElementById('posDiscount')?.value || 0);
  const tax = parseFloat(document.getElementById('posTax')?.value || 0);
  const paid = parseFloat(document.getElementById('posPaid')?.value || 0);

  const discAmt = subtotal * discount / 100;
  const taxAmt = (subtotal - discAmt) * tax / 100;
  const grand = subtotal - discAmt + taxAmt;
  const due = Math.max(0, grand - paid);
  const ret = Math.max(0, paid - grand);

  const elItems = document.getElementById('sumItems'); if(elItems) elItems.textContent = cart.reduce((s, c) => s + c.qty, 0);
  const elSub = document.getElementById('sumSubtotal'); if(elSub) elSub.textContent = fmtCur(subtotal);
  const elGrand = document.getElementById('sumGrandTotal'); if(elGrand) elGrand.textContent = fmtCur(grand);
  const elDue = document.getElementById('sumDue'); if(elDue) elDue.textContent = fmtCur(due);
  const elRet = document.getElementById('sumReturn'); if(elRet) elRet.textContent = fmtCur(ret);
}

async function checkout() {
    if (!cart.length) { toast('Cart is empty!', 'warning'); return; }
  
    const discount = parseFloat(document.getElementById('posDiscount')?.value || 0);
    const tax = parseFloat(document.getElementById('posTax')?.value || 0);
    const paid = parseFloat(document.getElementById('posPaid')?.value || 0);
    const custId = document.getElementById('posCustomer')?.value || null;
    const payment = document.getElementById('posPayment')?.value || 'Cash';
    const notes = document.getElementById('posNotes')?.value || '';
  
    let subtotal = 0;
    cart.forEach(c => { subtotal += c.qty * c.price; });
    const taxAmt = subtotal * (tax / 100);
    const discAmt = subtotal * (discount / 100);
    const total = subtotal + taxAmt - discAmt;

    const due = Math.abs(total - paid);
    const paymentStatus = paid >= total ? 'paid' : (paid > 0 ? 'partial' : 'unpaid');

    const payload = {
        buyer_id: custId,
        subtotal: subtotal,
        tax: taxAmt,
        discount: discAmt,
        total: total,
        paid_amount: paid,
        due_amount: due,
        payment_status: paymentStatus,
        payment_method: payment,
        items: cart.map(c => ({
            product_id: c.prodId,
            qty: c.qty,
            price: c.price
        }))
    };
  
    try {
        const btn = document.getElementById('checkoutBtn');
        const origText = btn.innerHTML;
        btn.innerHTML = 'Processing...';
        btn.disabled = true;
  
        const response = await api('/shop/api/orders', 'POST', payload);
        
        clearCart();
        await syncData(); // Refresh product grid
        
        toast(`Order generated successfully!`, 'success');
        
        // Open Invoice in new tab or popup
        if (response.order_id) {
            window.open(`/shop/orders/${response.order_id}/invoice`, 'InvoicePopup', 'width=400,height=600');
        }
  
        btn.innerHTML = origText;
        btn.disabled = false;
    } catch(e) {
        toast(e.message || 'Error occurred', 'danger');
        const btn = document.getElementById('checkoutBtn');
        btn.innerHTML = '<span>Pay Now</span> <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
        btn.disabled = false;
    }
}

// ============================================================
// INVOICE MODAL
// ============================================================
function showInvoiceModal(invoice) {
  document.getElementById('invoicePreview').innerHTML = buildInvoiceHTML(invoice);
  document.getElementById('invoiceModal').classList.remove('hidden');
}
function closeInvoiceModal() { document.getElementById('invoiceModal').classList.add('hidden'); }

// Thermal receipt CSS â€” 80mm roll width (~302px usable)
const THERMAL_CSS = `
  @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;700&display=swap');
  * { box-sizing: border-box; margin: 0; padding: 0; }
  @page { size: 80mm auto; margin: 4mm 3mm; }
  body { background: #fff; }
  .receipt {
    width: 76mm;
    font-family: 'DM Mono', 'Courier New', monospace;
    font-size: 11px;
    color: #000;
    background: #fff;
    padding: 0;
    line-height: 1.5;
  }
  .r-center { text-align: center; }
  .r-logo {
    width: 42px; height: 42px;
    background: #000;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 6px;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
  }
  .r-store-name {
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.5px;
    margin-bottom: 2px;
  }
  .r-store-sub { font-size: 9.5px; color: #444; line-height: 1.6; }
  .r-divider { border: none; border-top: 1px dashed #999; margin: 6px 0; }
  .r-divider-solid { border: none; border-top: 1px solid #000; margin: 5px 0; }
  .r-divider-double { border: none; border-top: 2px solid #000; margin: 5px 0; }
  .r-row { display: flex; justify-content: space-between; font-size: 10.5px; padding: 1px 0; }
  .r-row .label { color: #555; }
  .r-row .val { font-weight: 500; }
  .r-inv-num { font-size: 12px; font-weight: 700; letter-spacing: 1px; }
  /* Items table */
  .r-items { width: 100%; border-collapse: collapse; margin: 4px 0; }
  .r-items thead tr { border-bottom: 1px solid #000; border-top: 1px solid #000; }
  .r-items th {
    font-size: 9.5px;
    font-weight: 700;
    padding: 3px 2px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    font-family: 'DM Sans', sans-serif;
  }
  .r-items th:last-child, .r-items td:last-child { text-align: right; }
  .r-items th:nth-child(2), .r-items td:nth-child(2) { text-align: center; }
  .r-items th:nth-child(3), .r-items td:nth-child(3) { text-align: right; }
  .r-items td {
    font-size: 10px;
    padding: 3px 2px;
    vertical-align: top;
    border-bottom: 1px dashed #ddd;
  }
  .r-items tbody tr:last-child td { border-bottom: none; }
  .r-item-name { font-size: 10.5px; font-weight: 500; line-height: 1.3; }
  /* Totals */
  .r-totals { width: 100%; font-size: 10.5px; margin-top: 2px; }
  .r-totals td { padding: 2px 0; }
  .r-totals td:last-child { text-align: right; font-weight: 500; }
  .r-grand { font-size: 13px; font-weight: 700; font-family: 'DM Sans', sans-serif; }
  .r-grand td:last-child { font-size: 13px; font-weight: 700; }
  /* Paid / Due */
  .r-paid { font-size: 11px; }
  .r-due { color: #c00; font-weight: 700; }
  .r-ret { color: #080; font-weight: 700; }
  /* Notes */
  .r-notes { font-size: 9.5px; color: #444; border-left: 2px solid #999; padding-left: 5px; margin: 4px 0; }
  /* Footer */
  .r-footer { text-align: center; font-size: 9.5px; color: #555; margin-top: 4px; line-height: 1.7; }
  .r-footer .r-thanks { font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 700; color: #000; }
  .r-barcode { font-family: 'DM Mono', monospace; font-size: 9px; letter-spacing: 2px; color: #888; }
  @media print {
    @page { size: 80mm auto; margin: 3mm; }
    body { margin: 0; }
  }
`;

function buildInvoiceHTML(inv) {
  // Build item rows â€” wrap long name to second line
  const itemRows = inv.items.map(it => `
    <tr>
      <td><div class="r-item-name">${it.name}</div></td>
      <td style="text-align:center">${it.qty}</td>
      <td style="text-align:right">${parseFloat(it.price).toFixed(0)}</td>
      <td style="text-align:right">${parseFloat(it.sub).toFixed(0)}</td>
    </tr>`).join('');

  const custPhone = inv.custId ? (getCustomer(inv.custId).phone || '') : '';

  const sName = 'MobiPos';
  const sDesc = "Shop #12, Main Market";
  const sAddress = "Faisalabad, Punjab, Pakistan Ph: 041-1234567";
  const sHeading = 'INVOICE';
  const sFooter = "*** Thank You! ***.";
  const sLogo = `<div class="r-logo">${sName.charAt(0)}</div>`;

  return `<div class="receipt" id="invoicePrintArea">

    <!-- Header -->
    <div class="r-center">
      ${sLogo}
      <div class="r-store-name">${sName}</div>
      <div class="r-store-sub">
        ${sDesc}
        ${sDesc && sAddress ? '<br>' : ''}
        ${sAddress}
      </div>
      <div style="font-weight:bold; font-size:14px; margin-top:5px;">${sHeading}</div>
    </div>

    <hr class="r-divider-solid"/>

    <!-- Invoice meta -->
    <div class="r-row"><span class="label">Invoice#</span><span class="val r-inv-num">${inv.id}</span></div>
    <div class="r-row"><span class="label">Date</span><span class="val">${fmtDateTime(inv.date)}</span></div>
    <div class="r-row"><span class="label">Cashier</span><span class="val">${inv.cashier}</span></div>
    <div class="r-row"><span class="label">Payment</span><span class="val" style="text-transform:capitalize">${inv.payment}</span></div>

    <hr class="r-divider"/>

    <!-- Customer -->
    <div class="r-row"><span class="label">Customer</span><span class="val">${inv.custName}</span></div>
    ${custPhone ? `<div class="r-row"><span class="label">Phone</span><span class="val">${custPhone}</span></div>` : ''}

    <hr class="r-divider-solid"/>

    <!-- Items -->
    <table class="r-items">
      <thead>
        <tr>
          <th style="width:42%">Item</th>
          <th style="width:10%;text-align:center">Qty</th>
          <th style="width:22%;text-align:right">Price</th>
          <th style="width:26%;text-align:right">Amt</th>
        </tr>
      </thead>
      <tbody>${itemRows}</tbody>
    </table>

    <hr class="r-divider-solid"/>

    <!-- Totals -->
    <table class="r-totals">
      <tr><td>Subtotal</td><td>${fmtCur(inv.subtotal)}</td></tr>
      ${inv.discount > 0 ? `<tr><td>Discount (${inv.discount}%)</td><td>- ${fmtCur(inv.discAmt)}</td></tr>` : ''}
      ${inv.tax > 0 ? `<tr><td>Tax (${inv.tax}%)</td><td>+ ${fmtCur(inv.taxAmt)}</td></tr>` : ''}
    </table>

    <hr class="r-divider-double"/>

    <table class="r-totals">
      <tr class="r-grand"><td>GRAND TOTAL</td><td>${fmtCur(inv.grand)}</td></tr>
    </table>

    <hr class="r-divider"/>

    <table class="r-totals">
      <tr class="r-paid"><td>Paid (${inv.payment})</td><td>${fmtCur(inv.paid)}</td></tr>
      ${inv.due > 0 ? `<tr class="r-due"><td>Due Amount</td><td>${fmtCur(inv.due)}</td></tr>` : ''}
      ${inv.ret > 0 ? `<tr class="r-ret"><td>Return</td><td>${fmtCur(inv.ret)}</td></tr>` : ''}
    </table>

    ${inv.notes ? `<hr class="r-divider"/><div class="r-notes"><strong>Note:</strong> ${inv.notes}</div>` : ''}

    <hr class="r-divider"/>

    <!-- Footer -->
    <div class="r-footer">
      ${sFooter}<br>
      <br>
      <span class="r-barcode">${inv.id}</span>
    </div>

    <div style="height:12px"></div>
  </div>`;
}

function printInvoice() {
  const content = document.getElementById('invoicePrintArea').outerHTML;
  const win = window.open('', '_blank', 'width=340,height=700');
  win.document.write(`<!DOCTYPE html><html><head><title>Receipt â€” ${document.getElementById('invoicePrintArea')?.querySelector?.('.r-inv-num')?.textContent || 'Invoice'}</title><style>${THERMAL_CSS}</style></head><body>${content}</body></html>`);
  win.document.close();
  setTimeout(() => win.print(), 400);
}

// ============================================================
// INVOICES PAGE
// ============================================================
function renderInvoices() {
  const q = (document.getElementById('invoiceSearch')?.value || '').toLowerCase();
  const list = store.get('invoices').filter(i =>
    !q || i.id.toLowerCase().includes(q) || i.custName.toLowerCase().includes(q)
  ).sort((a, b) => new Date(b.date) - new Date(a.date));

  document.getElementById('invoicesTbody').innerHTML = list.length ?
    list.map(i => `<tr>
      <td><span class="badge badge-primary" style="font-family:var(--mono)">${i.id}</span></td>
      <td>${i.custName}</td>
      <td>${i.items.length}</td>
      <td style="font-weight:600">${fmtCur(i.grand)}</td>
      <td style="color:var(--success)">${fmtCur(i.paid)}</td>
      <td style="color:${i.due > 0 ? 'var(--danger)' : 'var(--text-muted)'}">${fmtCur(i.due)}</td>
      <td><span class="badge badge-gray" style="text-transform:capitalize">${i.payment}</span></td>
      <td>${fmtDateTime(i.date)}</td>
      <td>
        <button class="action-btn view" onclick="viewInvoice('${i.id}')" title="View">${VIEW_SVG}</button>
        <button class="action-btn del" onclick="deleteInvoice('${i.id}')" title="Delete">${DEL_SVG}</button>
      </td>
    </tr>`).join('') :
    '<tr><td colspan="9" class="empty-cell">No invoices found</td></tr>';
}

function viewInvoice(invId) {
  const inv = store.get('invoices').find(i => i.id === invId);
  if (inv) showInvoiceModal(inv);
}

function deleteInvoice(invId) {
  confirmDelete('Delete invoice ' + invId + '? This cannot be undone.', () => {
    let invoices = store.get('invoices').filter(i => i.id !== invId);
    store.set('invoices', invoices);
    toast('Invoice deleted', 'danger');
    renderInvoices();
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const invSearch = document.getElementById('invoiceSearch');
  if (invSearch) invSearch.addEventListener('input', renderInvoices);
});

// ============================================================
// SALES PAGE
// ============================================================
function renderSales() {
  const q = (document.getElementById('salesSearch')?.value || '').toLowerCase();
  const dateFilter = document.getElementById('salesDateFilter')?.value;
  let list = store.get('invoices');
  if (q) list = list.filter(i => i.custName.toLowerCase().includes(q));
  if (dateFilter) list = list.filter(i => i.date.startsWith(dateFilter));
  list.sort((a, b) => new Date(b.date) - new Date(a.date));

  document.getElementById('salesTbody').innerHTML = list.length ?
    list.map(i => `<tr>
      <td><span class="badge badge-primary" style="font-family:var(--mono)">${i.id}</span></td>
      <td>${i.custName}</td>
      <td>${i.items.reduce((s, x) => s + x.qty, 0)}</td>
      <td>${fmtCur(i.subtotal)}</td>
      <td>${i.discount}%</td>
      <td>${i.tax}%</td>
      <td style="font-weight:700;color:var(--primary)">${fmtCur(i.grand)}</td>
      <td>${fmtCur(i.paid)}</td>
      <td><span class="badge badge-gray" style="text-transform:capitalize">${i.payment}</span></td>
      <td>${fmtDateTime(i.date)}</td>
      <td>
        <button class="action-btn view" onclick="viewInvoice('${i.id}')">${VIEW_SVG}</button>
      </td>
    </tr>`).join('') :
    '<tr><td colspan="11" class="empty-cell">No sales found</td></tr>';
}

function exportSalesCSV() {
  const list = store.get('invoices');
  const rows = [['Invoice#', 'Customer', 'Grand Total', 'Paid', 'Due', 'Payment', 'Date']];
  list.forEach(i => rows.push([i.id, i.custName, i.grand, i.paid, i.due, i.payment, fmtDateTime(i.date)]));
  const csv = rows.map(r => r.join(',')).join('\n');
  const a = document.createElement('a');
  a.href = 'data:text/csv,' + encodeURIComponent(csv);
  a.download = 'sales_export.csv';
  a.click();
  toast('Sales exported as CSV', 'success');
}

document.addEventListener('DOMContentLoaded', () => {
  const ss = document.getElementById('salesSearch');
  if (ss) ss.addEventListener('input', renderSales);
  const sd = document.getElementById('salesDateFilter');
  if (sd) sd.addEventListener('input', renderSales);
});

// ============================================================
// MEDICINES
// ============================================================
function renderProducts() {
  const q = (document.getElementById('prodSearch')?.value || '').toLowerCase();
  
  let prods = store.get('products');
  if (q) prods = prods.filter(p => p.name.toLowerCase().includes(q) || (p.imei || '').toLowerCase().includes(q));

  document.getElementById('prodTbody').innerHTML = prods.length ?
    prods.map(p => {
      return `<tr>
        <td>
          ${p.image ? `<img src="/storage/${p.image}" alt="Product" style="width:40px; height:40px; border-radius:4px; object-fit:cover;">` : '<div style="width:40px; height:40px; background:#f3f4f6; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:10px;">No Img</div>'}
        </td>
        <td>
          <div style="font-weight:500">${p.name}</div>
          ${p.imei ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono)">IMEI/SN: ${p.imei}</div>` : ''}
        </td>
        <td style="text-transform:capitalize">${p.type}</td>
        <td style="text-transform:capitalize">${p.condition}</td>
        <td>${p.storage || '-'} / ${p.color || '-'}</td>
        <td style="font-weight:600">${fmtCur(p.sale)}</td>
        <td>
          ${getProdBadge(p)}
        </td>
        <td>
          ${p.category_id ? (store.get('categories').find(c => c.id == p.category_id)?.name || '-') : '-'}
        </td>
        <td>
          <button class="action-btn edit" onclick="openProductModal(${p.id})" title="Edit">${EDIT_SVG}</button>
          <button class="action-btn del" onclick="deleteProduct(${p.id})" title="Delete">${DEL_SVG}</button>
        </td>
      </tr>`;
    }).join('') :
    '<tr><td colspan="9" class="empty-cell">No products found</td></tr>';
}

function editProduct(id = null) {
  document.getElementById('prodId').value = id || '';
  
  const cats = store.get('categories') || [];
  document.getElementById('prodCategory').innerHTML = '<option value="">Select category</option>' + cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');

  const custs = store.get('customers') || [];
  document.getElementById('prodBuyer').innerHTML = '<option value="">Select Customer (Optional)</option>' + custs.map(c => `<option value="${c.id}">${c.name} (${c.phone})</option>`).join('');

  if (id) {
    const p = store.get('products').find(x => x.id == id);
    if (!p) return;
    document.getElementById('prodModalTitle').textContent = 'Edit Product';
    document.getElementById('prodName').value = p.name;
    document.getElementById('prodType').value = p.type;
    document.getElementById('prodCondition').value = p.condition;
    document.getElementById('prodImei').value = p.imei_serial || '';
    document.getElementById('prodColor').value = p.color || '';
    document.getElementById('prodStorage').value = p.storage || '';
    document.getElementById('prodPurchase').value = p.purchase;
    document.getElementById('prodSale').value = p.sale;
    document.getElementById('prodStatus').value = p.status;
    document.getElementById('prodStock').value = p.stock !== undefined ? p.stock : 1;
    document.getElementById('prodCategory').value = p.category_id || '';
    document.getElementById('prodBuyer').value = p.buyer_id || '';
  } else {
    document.getElementById('prodModalTitle').textContent = 'Add Product';
    document.getElementById('prodId').value = '';
    ['prodName','prodImei','prodColor','prodStorage','prodPurchase','prodSale','prodImage','prodBuyer'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('prodType').value = 'mobile';
    document.getElementById('prodCondition').value = 'new';
    document.getElementById('prodStatus').value = 'in_stock';
    document.getElementById('prodStock').value = '1';
    document.getElementById('prodCategory').value = '';
  }
  document.getElementById('prodModal').classList.remove('hidden');
}

function openProductModal(id) {
  editProduct(id);
}
function closeProductModal() { document.getElementById('prodModal').classList.add('hidden'); }

async function saveProduct() {
  const name = document.getElementById('prodName').value.trim();
  const type = document.getElementById('prodType').value;
  const condition = document.getElementById('prodCondition').value;
  const sale = document.getElementById('prodSale').value;
  
  if (!name || !type || !condition || !sale) { toast('Name, Type, Condition and Sale Price required!', 'warning'); return; }
  
  const editId = document.getElementById('prodId').value;
  const formData = new FormData();
  formData.append('name', name);
  formData.append('type', type);
  formData.append('condition', condition);
  formData.append('imei_serial', document.getElementById('prodImei').value.trim());
  formData.append('color', document.getElementById('prodColor').value.trim());
  formData.append('storage', document.getElementById('prodStorage').value.trim());
  formData.append('purchase_price', document.getElementById('prodPurchase').value || 0);
  formData.append('sale_price', sale);
  formData.append('status', document.getElementById('prodStatus').value);
  formData.append('stock', document.getElementById('prodStock').value || 1);
  
  const catId = parseInt(document.getElementById('prodCategory').value);
  if (catId) formData.append('category_id', catId);
  
  const buyerId = parseInt(document.getElementById('prodBuyer').value);
  if (buyerId) formData.append('buyer_id', buyerId);
  
  const imgInput = document.getElementById('prodImage');
  if (imgInput.files.length > 0) {
      formData.append('image', imgInput.files[0]);
  }
  
  try {
      if (editId) {
          formData.append('_method', 'PUT');
          await api('/shop/api/products/' + editId, 'POST', formData);
          toast('Product updated!', 'success');
      } else {
          await api('/shop/api/products', 'POST', formData);
          toast('Product added!', 'success');
      }
      closeProductModal();
      await syncData();
  } catch(e) { toast('Error saving product', 'danger'); }
}

function deleteProduct(id) {
  confirmDelete('Delete this product?', async () => {
    try {
        await api('/shop/api/products/' + id, 'DELETE');
        toast('Product deleted', 'danger');
        await syncData();
    } catch(e) { toast('Error deleting', 'danger'); }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const ps = document.getElementById('prodSearch');
  if (ps) ps.addEventListener('input', renderProducts);
});

// ============================================================
// CATEGORIES
// ============================================================
function renderCategories() {
  const q = (document.getElementById('catSearch')?.value || '').toLowerCase();
  let cats = store.get('categories') || [];
  if (q) cats = cats.filter(c => c.name.toLowerCase().includes(q));

  document.getElementById('catTbody').innerHTML = cats.length ?
    cats.map(c => `
      <tr>
        <td style="font-weight:500">${c.name}</td>
        <td style="color:var(--text-muted)">${c.desc || '-'}</td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;">
            <div style="width:16px;height:16px;border-radius:4px;background:${c.color || '#3b82f6'};"></div>
            <span style="font-family:var(--mono);font-size:12px;">${c.color || '#3b82f6'}</span>
          </div>
        </td>
        <td>
          <button class="action-btn edit" onclick="openCatModal(${c.id})" title="Edit">${EDIT_SVG}</button>
          <button class="action-btn del" onclick="deleteCategory(${c.id})" title="Delete">${DEL_SVG}</button>
        </td>
      </tr>
    `).join('') : '<tr><td colspan="4" class="empty-cell">No categories found</td></tr>';
}

function openCatModal(id) {
  if (id) {
    const c = store.get('categories').find(x => x.id == id);
    if (!c) return;
    document.getElementById('catModalTitle').textContent = 'Edit Category';
    document.getElementById('catId').value = c.id;
    document.getElementById('catName').value = c.name;
    document.getElementById('catDesc').value = c.desc || '';
    document.getElementById('catColor').value = c.color || '#3b82f6';
  } else {
    document.getElementById('catModalTitle').textContent = 'Add Category';
    document.getElementById('catId').value = '';
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    document.getElementById('catColor').value = '#3b82f6';
  }
  document.getElementById('catModal').classList.remove('hidden');
}
function closeCatModal() { document.getElementById('catModal').classList.add('hidden'); }

async function saveCategory() {
  const name = document.getElementById('catName').value.trim();
  const desc = document.getElementById('catDesc').value.trim();
  const color = document.getElementById('catColor').value.trim();
  
  if (!name) { toast('Category Name is required', 'warning'); return; }
  
  const editId = document.getElementById('catId').value;
  const data = { name, desc, color };
  
  try {
      if (editId) {
          await api('/shop/api/categories/' + editId, 'PUT', data);
          toast('Category updated!', 'success');
      } else {
          await api('/shop/api/categories', 'POST', data);
          toast('Category added!', 'success');
      }
      closeCatModal();
      await syncData();
  } catch(e) { toast('Error saving category', 'danger'); }
}

function deleteCategory(id) {
  confirmDelete('Delete this category?', async () => {
    try {
        await api('/shop/api/categories/' + id, 'DELETE');
        toast('Category deleted', 'danger');
        await syncData();
    } catch(e) { toast('Error deleting', 'danger'); }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const cs = document.getElementById('catSearch');
  if (cs) cs.addEventListener('input', renderCategories);
});

// ============================================================
// SUPPLIERS
// ============================================================
function renderSuppliers() {
  const q = (document.getElementById('suppSearch')?.value || '').toLowerCase();
  let list = store.get('suppliers');
  if (q) list = list.filter(s => s.name.toLowerCase().includes(q) || (s.company || '').toLowerCase().includes(q));

  document.getElementById('suppTbody').innerHTML = list.length ?
    list.map(s => `<tr>
      <td style="font-weight:500">${s.name}</td>
      <td>${s.company || '-'}</td>
      <td>${s.phone || '-'}</td>
      <td>${s.email || '-'}</td>
      <td>${s.address || '-'}</td>
      <td>
        <button class="action-btn edit" onclick="openSuppModal(${s.id})">${EDIT_SVG}</button>
        <button class="action-btn del" onclick="deleteSupplier(${s.id})">${DEL_SVG}</button>
      </td>
    </tr>`).join('') :
    '<tr><td colspan="6" class="empty-cell">No suppliers found</td></tr>';
}

function openSuppModal(id) {
  if (id) {
    const s = store.get('suppliers').find(x => x.id == id);
    if (!s) return;
    document.getElementById('suppModalTitle').textContent = 'Edit Supplier';
    document.getElementById('suppId').value = s.id;
    document.getElementById('suppName').value = s.name;
    document.getElementById('suppCompany').value = s.company || '';
    document.getElementById('suppPhone').value = s.phone || '';
    document.getElementById('suppEmail').value = s.email || '';
    document.getElementById('suppAddress').value = s.address || '';
    document.getElementById('suppNotes').value = s.notes || '';
  } else {
    document.getElementById('suppModalTitle').textContent = 'Add Supplier';
    document.getElementById('suppId').value = '';
    ['suppName','suppCompany','suppPhone','suppEmail','suppAddress','suppNotes'].forEach(id => document.getElementById(id).value = '');
  }
  document.getElementById('suppModal').classList.remove('hidden');
}
function closeSuppModal() { document.getElementById('suppModal').classList.add('hidden'); }

async function saveSupplier() {
  const name = document.getElementById('suppName').value.trim();
  const phone = document.getElementById('suppPhone').value.trim();
  if (!name || !phone) { toast('Name and phone required!', 'warning'); return; }
  
  const editId = document.getElementById('suppId').value;
  const data = {
    name, phone,
    company_name: document.getElementById('suppCompany').value.trim(),
    email: document.getElementById('suppEmail').value.trim(),
    address: document.getElementById('suppAddress').value.trim(),
    notes: document.getElementById('suppNotes').value.trim(),
  };
  
  try {
      if (editId) {
          await api('/suppliers/' + editId, 'PUT', data);
          toast('Supplier updated!', 'success');
      } else {
          await api('/suppliers', 'POST', data);
          toast('Supplier added!', 'success');
      }
      closeSuppModal();
      await syncData();
  } catch(e) { toast('Error saving supplier', 'danger'); }
}

function deleteSupplier(id) {
  confirmDelete('Delete this supplier?', async () => {
    try {
        await api('/suppliers/' + id, 'DELETE');
        toast('Supplier deleted', 'danger');
        await syncData();
    } catch(e) { toast('Error deleting', 'danger'); }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const ss = document.getElementById('suppSearch');
  if (ss) ss.addEventListener('input', renderSuppliers);
});

// ============================================================
// CUSTOMERS
// ============================================================
function renderCustomers() {
  const q = (document.getElementById('custSearch')?.value || '').toLowerCase();
  let list = store.get('customers');
  if (q) list = list.filter(c => c.name.toLowerCase().includes(q) || (c.phone || '').includes(q));

  document.getElementById('custTbody').innerHTML = list.length ?
    list.map(c => `<tr>
      <td style="font-weight:500">${c.name}</td>
      <td>${c.phone || '-'}</td>
      <td>${c.cnic_number || '-'}</td>
      <td>${c.address || '-'}</td>
      <td>
        ${c.cnic_front ? `<a href="/storage/${c.cnic_front}" target="_blank" class="badge badge-success">Front</a>` : ''}
        ${c.cnic_back ? `<a href="/storage/${c.cnic_back}" target="_blank" class="badge badge-success">Back</a>` : ''}
      </td>
      <td>
        <button class="action-btn edit" onclick="openCustModal(${c.id})">${EDIT_SVG}</button>
        <button class="action-btn del" onclick="deleteCustomer(${c.id})">${DEL_SVG}</button>
      </td>
    </tr>`).join('') :
    '<tr><td colspan="6" class="empty-cell">No customers found</td></tr>';
}

function openCustModal(id) {
  if (id) {
    const c = store.get('customers').find(x => x.id == id);
    if (!c) return;
    document.getElementById('custModalTitle').textContent = 'Edit Customer';
    document.getElementById('custId').value = c.id;
    document.getElementById('custName').value = c.name;
    document.getElementById('custPhone').value = c.phone || '';
    document.getElementById('custCnicNumber').value = c.cnic_number || '';
    document.getElementById('custAddress').value = c.address || '';
  } else {
    document.getElementById('custModalTitle').textContent = 'Add Customer';
    document.getElementById('custId').value = '';
    ['custName','custPhone','custCnicNumber','custAddress'].forEach(id => document.getElementById(id).value = '');
    ['custCnicFront','custCnicBack'].forEach(id => document.getElementById(id).value = '');
  }
  document.getElementById('custModal').classList.remove('hidden');
}
function closeCustModal() { document.getElementById('custModal').classList.add('hidden'); }

async function saveCustomer() {
  const name = document.getElementById('custName').value.trim();
  const phone = document.getElementById('custPhone').value.trim();
  if (!name) { toast('Name is required!', 'warning'); return; }
  
  const editId = document.getElementById('custId').value;
  
  const formData = new FormData();
  formData.append('name', name);
  formData.append('phone', phone);
  formData.append('cnic_number', document.getElementById('custCnicNumber').value.trim());
  formData.append('address', document.getElementById('custAddress').value.trim());

  const cnicFront = document.getElementById('custCnicFront').files[0];
  if (cnicFront) formData.append('cnic_front', cnicFront);

  const cnicBack = document.getElementById('custCnicBack').files[0];
  if (cnicBack) formData.append('cnic_back', cnicBack);
  
  try {
      if (editId) {
          await api('/shop/api/customers/' + editId, 'POST', formData);
          toast('Customer updated!', 'success');
      } else {
          const res = await api('/shop/api/customers', 'POST', formData);
          if (res && res.id) window.lastCreatedCustomerId = res.id;
          toast('Customer added!', 'success');
      }
      closeCustModal();
      await syncData();
  } catch(e) { toast('Error saving customer', 'danger'); }
}

function deleteCustomer(id) {
  confirmDelete('Delete this customer?', async () => {
    try {
        await api('/shop/api/customers/' + id, 'DELETE');
        toast('Customer deleted', 'danger');
        await syncData();
    } catch(e) { toast('Error deleting', 'danger'); }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const cs = document.getElementById('custSearch');
  if (cs) cs.addEventListener('input', renderCustomers);
});

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

