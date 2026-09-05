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
  if (catSel) {
    catSel.innerHTML = '<option value="">All Categories</option>' +
      cats.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
  }

  renderProdGrid();
  renderCart();

  setTimeout(loadEditOrderIfAny, 100);
}

async function loadEditOrderIfAny() {
  const editId = localStorage.getItem('mp_edit_order_id');
  if (!editId) return;

  localStorage.removeItem('mp_edit_order_id');

  try {
    const res = await api(`/shop/api/orders/${editId}`);
    const order = res;

    window.editingOrderId = order.id;
    cart = [];

    const allProducts = store.get('products');
    order.items.forEach(item => {
      const prod = item.product || allProducts.find(p => p.id === item.product_id);
      if (prod) {
        const p = parseFloat(item.sell_price || item.price || prod.sale);
        const q = parseFloat(item.qty);
        cart.push({
          prodId: prod.id,
          name: prod.name,
          price: p,
          qty: q,
          sub: p * q,
          maxStock: prod.stock + q,
          unit: prod.unit || 'pcs',
          type: prod.type,
          image: prod.image,
          brand: prod.meta_data && prod.meta_data.brand ? prod.meta_data.brand : null
        });
      }
    });

    const custField = document.getElementById('posCustomer');
    if (custField) custField.value = order.buyer_id || '';

    const sub = parseFloat(order.subtotal);
    let discPct = 0, taxPct = 0;
    if (sub > 0) {
      discPct = (parseFloat(order.discount) / sub) * 100;
      taxPct = (parseFloat(order.tax) / sub) * 100;
    }

    const discField = document.getElementById('posDiscount');
    if (discField) discField.value = discPct.toFixed(2);

    const taxField = document.getElementById('posTax');
    if (taxField) taxField.value = taxPct.toFixed(2);

    const paidField = document.getElementById('posPaid');
    if (paidField) {
      // Use net paid amount to properly handle previous change given
      const netPaid = Math.min(parseFloat(order.paid_amount), parseFloat(order.total));
      paidField.value = netPaid;
    }

    const methodField = document.getElementById('posPayment');
    if (methodField) methodField.value = order.payment_method;

    const btn = document.getElementById('checkoutBtn');
    if (btn) btn.innerHTML = '<span>Update Order</span> <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>';

    renderCart();
    renderProdGrid();

    toast('Order loaded for editing!', 'info');
  } catch (e) {
    toast('Error loading order for editing', 'danger');
    console.error(e);
  }
}

// State for POS filters
let posFilter = {
  view: 'list',
  showImage: true,
  q: '',
  catId: ''
};

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
  if (grid) grid.classList.toggle('list-view', v === 'list');
  renderProdGrid();
}

function togglePosImage() {
  posFilter.showImage = !posFilter.showImage;
  const btn = document.getElementById('viewImageToggle');
  if (btn) btn.classList.toggle('active', posFilter.showImage);
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
    (p.code || '').toLowerCase().includes(q) ||
    (p.barcode || '').toLowerCase().includes(q)
  );
  // We don't have categories mapped exactly yet, skipping category filter unless implemented
  // if (posFilter.catId) prods = prods.filter(p => p.catId == posFilter.catId);

  const countEl = document.getElementById('posProdCount');
  if (countEl) countEl.textContent = q ? `${prods.length} result${prods.length !== 1 ? 's' : ''}` : `${prods.length} products`;

  const grid = document.getElementById('posProdGrid');
  if (!grid) return;
  grid.classList.toggle('list-view', posFilter.view === 'list');

  if (!prods.length) {
    grid.innerHTML = `<div class="pos-no-results">
      <div class="pos-no-results-icon">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
      </div>
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
  const inCart = cart.find(c => c.prodId == p.id);
  const cartQty = inCart ? parseFloat(Number(inCart.qty).toFixed(3)) : 0;
  const availStock = parseFloat(Number(p.stock - cartQty).toFixed(3));

  const oos = (window.ACTIVE_MODULE !== 'fast_food' && availStock <= 0) || p.status === 'defective' || p.status === 'in_repair';

  let stockBg = '#d1fae5'; let stockColor = '#065f46'; let stockText = 'In Stock (' + availStock + ')';
  if (window.ACTIVE_MODULE === 'fast_food') {
      stockText = 'Available';
  } else {
      if (oos) { stockBg = '#fee2e2'; stockColor = '#991b1b'; stockText = 'Out of Stock'; }
      else if (availStock < 10) { stockBg = '#fef3c7'; stockColor = '#92400e'; stockText = 'Low Stock (' + availStock + ')'; }
  }

  return `<div class="med-card${oos ? ' out-of-stock' : ''}${inCart ? ' in-cart' : ''}" onclick="addToCart(${p.id})" style="position:relative; overflow:hidden;">
    ${(posFilter.showImage && p.image) ? `<img src="/storage/${p.image}" alt="${p.name}" style="width:calc(100% + 24px); height:80px; object-fit:cover; margin:-12px -12px 12px -12px; display:block;">` : ''}
    ${inCart ? `<div class="med-card-incart" style="position:absolute; right:8px; top:8px; background:var(--success); color:white; border-radius:50%; width:24px; height:24px; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; z-index:2; box-shadow:0 2px 4px rgba(0,0,0,0.2);">${cartQty}</div>` : ''}
    <div class="med-card-cat">${p.type || 'Phone'} - ${p.condition || 'Used'}</div>
    <div class="med-card-name" style="margin-bottom:2px; display:flex; justify-content:space-between; align-items:flex-start; gap:4px;">
      <span>${p.name} ${p.condition || p.color ? `<span style="font-size:12px; font-weight:normal; color:var(--primary);">(${[p.condition, p.color].filter(Boolean).join(' - ')})</span>` : ''} ${p.code ? `<span style="font-size:12px; color:var(--text-muted); font-weight:normal;"> (Code: ${p.code})</span>` : (p.barcode ? `<span style="font-size:12px; color:var(--text-muted); font-weight:normal;"> (Barcode: ${p.barcode})</span>` : '')}</span>
      <div style="display:flex; flex-direction:column; align-items:flex-end; line-height:1.1;">
        ${p.discount && p.discount > 0 ? `<span style="text-decoration:line-through; font-size:10px; color:var(--text-muted);">${fmtCur(p.sale)}</span>` : ''}
        <span style="font-weight:800; font-size:14px; color:var(--primary);">${fmtCur(p.sale - (p.discount || 0))}</span>
      </div>
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
  const inCart = cart.find(c => c.prodId == p.id);
  const cartQty = inCart ? parseFloat(Number(inCart.qty).toFixed(3)) : 0;
  const availStock = parseFloat(Number(p.stock - cartQty).toFixed(3));

  const oos = (window.ACTIVE_MODULE !== 'fast_food' && availStock <= 0) || p.status === 'defective' || p.status === 'in_repair';

  let stockBg = '#d1fae5'; let stockColor = '#065f46'; let stockText = 'In Stock (' + availStock + ')';
  if (window.ACTIVE_MODULE === 'fast_food') {
      stockText = 'Available';
  } else {
      if (oos) { stockBg = '#fee2e2'; stockColor = '#991b1b'; stockText = 'Out of Stock'; }
      else if (availStock < 10) { stockBg = '#fef3c7'; stockColor = '#92400e'; stockText = 'Low Stock (' + availStock + ')'; }
  }

  return `<div class="med-row${oos ? ' out-of-stock' : ''}${inCart ? ' in-cart' : ''}" onclick="addToCart(${p.id})">
    <div class="med-row-info" style="display:flex; align-items:center; gap:12px;">
      ${(posFilter.showImage && p.image) ? `<img src="/storage/${p.image}" style="width:40px; height:40px; object-fit:cover; border-radius:6px; flex-shrink:0;">` : `<div style="width:40px; height:40px; background:var(--border-light); border-radius:6px; flex-shrink:0; display:flex; align-items:center; justify-content:center;"><svg width="20" height="20" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`}
      <div>
        <div class="med-row-name">
          ${p.meta_data && p.meta_data.brand ? `<div style="font-size:10px; text-transform:uppercase; color:var(--text-muted); font-weight:700; letter-spacing:0.5px; margin-bottom:2px; line-height:1;">${p.meta_data.brand}</div>` : ''}
          ${p.name} ${p.condition || p.color ? `<span style="font-size:11px; font-weight:normal; color:var(--primary);">(${[p.condition, p.color].filter(Boolean).join(' - ')})</span>` : ''} ${p.code ? `<span style="font-size:12px; color:var(--text-muted); font-weight:normal; margin-left:4px;">(Code: ${p.code})</span>` : (p.barcode ? `<span style="font-size:12px; color:var(--text-muted); font-weight:normal; margin-left:4px;">(Barcode: ${p.barcode})</span>` : '')} ${inCart ? `<span class="badge badge-success" style="font-size:10px">In cart ✕${inCart.qty}</span>` : ''}
        </div>
        <div class="med-row-meta" style="margin-top: 4px;">
        ${p.storage ? p.storage + ' · ' : ''}
        ${p.color ? p.color + ' · ' : ''}
        <span style="font-size:10px; font-weight:600; padding:2px 6px; border-radius:10px; background:${stockBg}; color:${stockColor};">${stockText}</span>
      </div>
      </div>
    </div>
    <div class="med-row-price" style="display:flex; flex-direction:column; align-items:flex-end; line-height:1.1;">
      ${p.discount && p.discount > 0 ? `<span style="text-decoration:line-through; font-size:10px; color:var(--text-muted);">${fmtCur(p.sale)}</span>` : ''}
      <span style="font-weight:800;">${fmtCur(p.sale - (p.discount || 0))}</span>
    </div>
  </div>`;
}

// POS Search
let posSearchTimeout = null;
document.addEventListener('DOMContentLoaded', () => {
  const posSearchEl = document.getElementById('posSearch');
  if (posSearchEl) {
    posSearchEl.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const val = this.value.trim().toLowerCase();
        if (!val) return;
        
        const prods = store.get('products');
        let exactMatch = null;
        let matchedUnit = null;

        for (const p of prods) {
            if ((p.barcode && p.barcode.toLowerCase() === val) || 
                (p.code && p.code.toLowerCase() === val)) {
                exactMatch = p;
                break;
            }
            if (p.stock_units && p.stock_units.length > 0) {
                const foundUnit = p.stock_units.find(u => {
                    if (u.status !== 'available' || !u.imeis) return false;
                    const imeiList = u.imeis.split(',').map(s => s.trim().toLowerCase());
                    return imeiList.includes(val);
                });
                if (foundUnit) {
                    exactMatch = p;
                    matchedUnit = foundUnit;
                    break;
                }
            }
        }
        
        if (exactMatch) {
            if (matchedUnit) {
                const existing = cart.find(c => c.prodId == exactMatch.id);
                if (existing) {
                    if (!existing.stock_units) existing.stock_units = [];
                    if (!existing.stock_units.includes(matchedUnit.id)) {
                        existing.stock_units.push(matchedUnit.id);
                        existing.qty = existing.stock_units.length;
                        existing.sub = existing.qty * existing.price;
                        toast(`IMEI added`, 'success');
                    } else {
                        toast(`IMEI already in cart`, 'warning');
                    }
                } else {
                    const pName = exactMatch.name + (exactMatch.condition || exactMatch.color ? ` (${[exactMatch.condition, exactMatch.color].filter(Boolean).join(' - ')})` : '');
                    const actualPrice = parseFloat(exactMatch.sale) - (parseFloat(exactMatch.discount) || 0);
                    cart.push({ 
                        prodId: exactMatch.id, 
                        name: pName, 
                        price: actualPrice, 
                        qty: 1, 
                        sub: actualPrice, 
                        maxStock: exactMatch.stock,
                        stock_units: [matchedUnit.id],
                        type: exactMatch.type
                    });
                    toast(`IMEI added`, 'success');
                }
                this.value = '';
                posFilter.q = '';
                const clearBtn = document.getElementById('posSearchClear');
                if (clearBtn) clearBtn.classList.add('hidden');
                renderCart();
                renderProdGrid();
            } else {
                addToCart(exactMatch.id);
                this.value = '';
                posFilter.q = '';
                const clearBtn = document.getElementById('posSearchClear');
                if (clearBtn) clearBtn.classList.add('hidden');
                renderProdGrid();
            }
        } else {
            toast('Product or IMEI not found', 'warning');
            this.value = '';
            posFilter.q = '';
            const clearBtn = document.getElementById('posSearchClear');
            if (clearBtn) clearBtn.classList.add('hidden');
            renderProdGrid();
        }
      }
    });

    posSearchEl.addEventListener('input', function () {
      posFilter.q = this.value;
      const clearBtn = document.getElementById('posSearchClear');
      if (clearBtn) clearBtn.classList.toggle('hidden', !this.value);
      clearTimeout(posSearchTimeout);
      posSearchTimeout = setTimeout(renderProdGrid, 150);
    });
  }

  const catSel = document.getElementById('posCategorySel');
  if (catSel) {
    catSel.addEventListener('change', function () {
      filterPosCat(this, this.value);
    });
  }

  const posCustSel = document.getElementById('posCustomer');
  if (posCustSel) {
    posCustSel.addEventListener('change', renderCartSummary);
  }

  const ledgerCheck = document.getElementById('posSaveToLedger');
  if (ledgerCheck) {
    ledgerCheck.addEventListener('change', renderCartSummary);
  }

  const catSelOrig = document.getElementById('posCatFilter');
  if (catSelOrig) {
    catSelOrig.addEventListener('change', function () {
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

  // Fetch Store Settings Defaults
  if (document.getElementById('posDiscount')) {
    fetch('/shop/api/settings')
      .then(res => res.json())
      .then(data => {
        window.globalStoreSettings = data;
        const d = document.getElementById('posDiscount');
        const t = document.getElementById('posTax');
        if (d && (!d.value || d.value == 0)) d.value = data.discount || 0;
        if (t && (!t.value || t.value == 0)) t.value = data.tax || 0;
        renderCartSummary();
      })
      .catch(console.error);
  }

  // Global scanner listener: if typing outside an input, auto-focus posSearch
  document.addEventListener('keydown', function(e) {
      if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
      if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
          const posSearchEl = document.getElementById('posSearch');
          if (posSearchEl) {
              posSearchEl.focus();
          }
      }
  });
});

function addToCart(prodId) {
  const p = store.get('products').find(m => m.id == prodId);
  if (!p) return;
  if ((window.ACTIVE_MODULE !== 'fast_food' && p.stock <= 0) || p.status === 'defective' || p.status === 'in_repair') { toast('Product is unavailable!', 'danger'); return; }

  // IF IT IS SERIALIZED, prompt for IMEI selection
  if (window.ACTIVE_MODULE === 'mobile' && ['mobile', 'tablet', 'laptop'].includes(p.type)) {
     document.getElementById('imeiSelectProdId').value = p.id;
     
     const existingCartItem = cart.find(c => c.prodId == prodId);
     const alreadySelectedIds = existingCartItem && existingCartItem.stock_units ? existingCartItem.stock_units : [];
     
     // Only show available units
     const availableUnits = p.stock_units ? p.stock_units.filter(u => u.status === 'available') : [];

     let html = '';
     if (availableUnits.length > 0) {
        availableUnits.forEach(unit => {
           const isChecked = alreadySelectedIds.includes(unit.id) ? 'checked' : '';
           html += `<label style="display:flex; align-items:center; gap:8px; padding: 10px; background: #fff; border: 1px solid var(--border); border-radius: 6px; cursor: pointer;">
              <input type="checkbox" class="imei-checkbox" value="${unit.id}" data-imei="${unit.imeis || 'No IMEI'}" ${isChecked}>
              <span style="font-family: var(--mono); font-size: 13px;">${unit.imeis || 'Unknown IMEI'}</span>
           </label>`;
        });
     } else {
        html = '<p>No specific IMEI units found in stock. Cannot sell serialized item without IMEI.</p>';
     }
     document.getElementById('imeiSelectList').innerHTML = html;
     
     const searchInput = document.getElementById('imeiSearchInput');
     if (searchInput) searchInput.value = '';
     
     document.getElementById('imeiSelectModal').classList.remove('hidden');
     return;
  }

  // FAST FOOD: Variations & Addons Logic
  if (window.ACTIVE_MODULE === 'fast_food' && p.meta_data && p.meta_data.fast_food && Object.keys(p.meta_data.fast_food.variations || {}).length > 0) {
      openPosVariationModal(p);
      return;
  }

  const bypassStock = (window.ACTIVE_MODULE === 'fast_food' && (!p.meta_data || !p.meta_data.fast_food || p.meta_data.fast_food.prepare_type !== 'readymade'));

  const existing = cart.find(c => c.prodId == prodId && !c.ff_var_id); // Only match base items without variations here
  if (existing) {
    if (!bypassStock && existing.qty >= p.stock) { toast('Not enough stock!', 'warning'); return; }
    existing.qty++;
    existing.sub = existing.qty * existing.price;
  } else {
    if (!bypassStock && p.stock <= 0) { toast('Out of stock!', 'danger'); return; }
    const pName = p.name + (p.condition || p.color ? ` (${[p.condition, p.color].filter(Boolean).join(' - ')})` : '');
    const pBrand = p.meta_data && p.meta_data.brand ? p.meta_data.brand : null;
    const actualPrice = parseFloat(p.sale) - (parseFloat(p.discount) || 0);
    cart.push({ cartItemId: Date.now().toString(), prodId: p.id, name: pName, price: actualPrice, qty: 1, sub: actualPrice, maxStock: p.stock, image: p.image, brand: pBrand, unit: p.unit || 'pcs', type: p.type });
  }
  renderCart();
  renderProdGrid();
  toast(`${p.name} added`, 'success');
}

// FAST FOOD VARIATION MODAL FUNCTIONS
let currentFFProduct = null;

function openPosVariationModal(p) {
    currentFFProduct = p;
    document.getElementById('posVarProdId').value = p.id;
    document.getElementById('posVariationModalTitle').textContent = p.name + " - Options";
    
    const ffData = p.meta_data.fast_food;
    const varList = document.getElementById('posVarList');
    const addonList = document.getElementById('posAddonList');
    
    // Render Variations
    let varHtml = '';
    let isFirst = true;
    for (const [varId, varData] of Object.entries(ffData.variations || {})) {
        const checked = isFirst ? 'checked' : '';
        const vObj = window.globalVariations ? window.globalVariations.find(v => v.id == varId) : null;
        const varName = vObj ? vObj.name : `Variation ${varId}`;
        
        // Handle both older object format and newer string format for base_price
        const varPrice = typeof varData === 'object' ? varData.base_price : varData;
        
        varHtml += `<label class="ff-var-box" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 12px 8px; background: #fff; border-radius: 8px; cursor: pointer; text-align:center; position:relative;">
            <input type="radio" name="ff_variation" value="${varId}" data-name="${varName}" data-price="${varPrice}" ${checked} onchange="updatePosVarTotal()" style="position:absolute; opacity:0; width:0; height:0;">
            <span style="font-weight: 700; font-size:14px; margin-bottom:4px; color:var(--text);">${varName}</span>
            <span style="color:var(--primary); font-weight:800; font-size:12px;">+${fmtCur(varPrice)}</span>
        </label>`;
        isFirst = false;
    }
    varList.innerHTML = varHtml;
    
    updatePosAddons(); // Will render addons based on first selected variation
    document.getElementById('posVariationModal').classList.remove('hidden');
}

function updatePosAddons() {
    if (!currentFFProduct) return;
    
    const selectedVarRadio = document.querySelector('input[name="ff_variation"]:checked');
    if (!selectedVarRadio) return;
    const varId = selectedVarRadio.value;
    
    const ffData = currentFFProduct.meta_data.fast_food;
    const addonList = document.getElementById('posAddonList');
    
    let addonHtml = '';
    for (const [addonId, addonPrices] of Object.entries(ffData.addons || {})) {
        const price = addonPrices[varId];
        if (price !== undefined && price !== null && price !== "") {
            const aObj = window.globalAddons ? window.globalAddons.find(a => a.id == addonId) : null;
            const addonName = aObj ? aObj.name : `Addon ${addonId}`;
            
            addonHtml += `<label class="ff-addon-box" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 12px 8px; background: #fff; border-radius: 8px; cursor: pointer; text-align:center; position:relative;">
                <input type="checkbox" class="ff-addon-checkbox" value="${addonId}" data-name="${addonName}" data-price="${price}" onchange="updatePosVarTotal()" style="position:absolute; opacity:0; width:0; height:0;">
                <span style="font-weight: 700; font-size:14px; margin-bottom:4px; color:var(--text);">${addonName}</span>
                <span style="color:var(--success); font-weight:800; font-size:12px;">+${fmtCur(price)}</span>
            </label>`;
        }
    }
    
    if (addonHtml === '') addonHtml = '<span style="color:var(--text-muted); font-size:13px;">No addons available for this size.</span>';
    addonList.innerHTML = addonHtml;
    updatePosVarTotal();
}

function updatePosVarTotal() {
    const varRadio = document.querySelector('input[name="ff_variation"]:checked');
    let total = 0;
    
    if (varRadio) {
        total += parseFloat(varRadio.dataset.price || 0);
        
        // Ensure addons are updated if variation changed, but only if they don't already match the current var
        // Since updatePosAddons is called on var change (need to bind it properly)
    }
    
    const addonCheckboxes = document.querySelectorAll('.ff-addon-checkbox:checked');
    addonCheckboxes.forEach(cb => {
        total += parseFloat(cb.dataset.price || 0);
    });
    
    document.getElementById('posVarTotalPrice').textContent = fmtCur(total);
}

// Bind updatePosAddons to radio change
document.addEventListener('change', function(e) {
    if (e.target && e.target.name === 'ff_variation') {
        updatePosAddons();
    }
});

function closePosVariationModal() {
    document.getElementById('posVariationModal').classList.add('hidden');
    currentFFProduct = null;
}

function confirmPosVariation() {
    if (!currentFFProduct) return;
    
    const varRadio = document.querySelector('input[name="ff_variation"]:checked');
    if (!varRadio) {
        toast('Please select a size/variation', 'warning');
        return;
    }
    
    const varId = varRadio.value;
    const varName = varRadio.dataset.name;
    const varPrice = parseFloat(varRadio.dataset.price || 0);
    
    const selectedAddons = [];
    let addonsTotal = 0;
    
    const addonCheckboxes = document.querySelectorAll('.ff-addon-checkbox:checked');
    addonCheckboxes.forEach(cb => {
        const aId = cb.value;
        const aName = cb.dataset.name;
        const aPrice = parseFloat(cb.dataset.price || 0);
        selectedAddons.push({ id: aId, name: aName, price: aPrice });
        addonsTotal += aPrice;
    });
    
    const finalPrice = varPrice + addonsTotal;
    const addonsString = selectedAddons.map(a => a.id).sort().join('|'); // for grouping
    
    // Check if this exact combination exists in cart
    const existing = cart.find(c => 
        c.prodId === currentFFProduct.id && 
        c.ff_var_id === varId && 
        (c.ff_addons_string || '') === addonsString
    );
    
    if (existing) {
        existing.qty++;
        existing.sub = existing.qty * existing.price;
    } else {
        cart.push({
            cartItemId: Date.now().toString(),
            prodId: currentFFProduct.id,
            name: currentFFProduct.name,
            price: finalPrice,
            qty: 1,
            sub: finalPrice,
            maxStock: currentFFProduct.stock, // Bypassed anyway
            image: currentFFProduct.image,
            unit: currentFFProduct.unit || 'pcs',
            type: currentFFProduct.type,
            
            // Fast Food Specific Data
            ff_var_id: varId,
            ff_var_name: varName,
            ff_var_price: varPrice,
            ff_addons: selectedAddons,
            ff_addons_string: addonsString
        });
    }
    
    closePosVariationModal();
    renderCart();
    renderProdGrid();
    toast(`${currentFFProduct.name} added`, 'success');
}

function closeImeiSelectModal() {
  document.getElementById('imeiSelectModal').classList.add('hidden');
}

function filterImeiList(query) {
   query = query.toLowerCase();
   const list = document.getElementById('imeiSelectList');
   if (!list) return;
   
   const labels = list.querySelectorAll('label');
   labels.forEach(label => {
      const text = label.textContent.toLowerCase();
      if (text.includes(query)) {
         label.style.display = 'flex';
      } else {
         label.style.display = 'none';
      }
   });
}

function confirmImeiSelection() {
  const prodId = document.getElementById('imeiSelectProdId').value;
  const p = store.get('products').find(m => m.id == prodId);
  if (!p) return;
  
  const checkboxes = document.querySelectorAll('.imei-checkbox:checked');
  const selectedUnitIds = Array.from(checkboxes).map(cb => parseInt(cb.value));
  
  if (selectedUnitIds.length === 0) {
      removeFromCart(prodId);
      closeImeiSelectModal();
      return;
  }
  
  if (selectedUnitIds.length > p.stock) {
      toast('Cannot select more than available stock', 'danger');
      return;
  }
  
  const existing = cart.find(c => c.prodId == prodId);
  if (existing) {
      existing.qty = selectedUnitIds.length;
      existing.sub = existing.qty * existing.price;
      existing.stock_units = selectedUnitIds;
  } else {
      const actualPrice = parseFloat(p.sale) - (parseFloat(p.discount) || 0);
      cart.push({ 
          prodId: p.id, 
          name: p.name + (p.condition || p.color ? ` (${[p.condition, p.color].filter(Boolean).join(' - ')})` : ''), 
          price: actualPrice, 
          qty: selectedUnitIds.length, 
          sub: actualPrice * selectedUnitIds.length, 
          maxStock: p.stock,
          stock_units: selectedUnitIds,
          type: p.type,
          image: p.image,
          brand: p.meta_data && p.meta_data.brand ? p.meta_data.brand : null,
          unit: p.unit || 'pcs'
      });
  }
  
  closeImeiSelectModal();
  renderCart();
  renderProdGrid();
  toast(`${p.name} added`, 'success');
}

function changeQty(identifier, delta, isCartItemId = false) {
  let item = null;
  if(isCartItemId) {
      item = cart.find(c => c.cartItemId === identifier);
  } else {
      item = cart.find(c => c.prodId == identifier);
  }
  if (!item) return;
  
  if (item.type && ['mobile', 'tablet', 'laptop'].includes(item.type)) {
     addToCart(item.prodId); // Re-open IMEI selection modal
     return;
  }

  const p = store.get('products').find(x => x.id == item.prodId);
  const bypassStock = (window.ACTIVE_MODULE === 'fast_food' && (!p || !p.meta_data || !p.meta_data.fast_food || p.meta_data.fast_food.prepare_type !== 'readymade'));

  const newQty = item.qty + delta;
  if (newQty <= 0) { removeFromCart(isCartItemId ? identifier : item.prodId, item.prodId); return; }
  if (!bypassStock && newQty > item.maxStock) { toast('Not enough stock!', 'warning'); return; }
  item.qty = newQty;
  item.sub = item.qty * item.price;
  renderCart();
  renderProdGrid();
}

function setTotal(prodId, newTotal) {
  const item = cart.find(c => c.prodId == prodId);
  if (!item) return;
  
  const total = parseFloat(newTotal);
  if (isNaN(total) || total <= 0) {
      removeFromCart(prodId);
      return;
  }
  
  const p = store.get('products').find(x => x.id == item.prodId);
  const bypassStock = (window.ACTIVE_MODULE === 'fast_food' && (!p || !p.meta_data || !p.meta_data.fast_food || p.meta_data.fast_food.prepare_type !== 'readymade'));

  if (item.price > 0) {
      const q = total / item.price;
      if (!bypassStock && q > item.maxStock) {
          toast('Not enough stock!', 'warning');
          item.qty = item.maxStock;
          item.sub = item.qty * item.price;
      } else {
          item.qty = q;
          item.sub = total;
      }
  } else {
      item.sub = total;
  }
  
  renderCart();
  renderProdGrid();
}

function setQty(prodId, newQty) {
  const item = cart.find(c => c.prodId == prodId);
  if (!item) return;
  
  const q = parseFloat(newQty);
  if (isNaN(q) || q <= 0) {
      removeFromCart(prodId);
      return;
  }
  
  const p = store.get('products').find(x => x.id == item.prodId);
  const bypassStock = (window.ACTIVE_MODULE === 'fast_food' && (!p || !p.meta_data || !p.meta_data.fast_food || p.meta_data.fast_food.prepare_type !== 'readymade'));

  if (!bypassStock && q > item.maxStock) {
      toast('Not enough stock!', 'warning');
      item.qty = item.maxStock;
  } else {
      item.qty = q;
  }
  
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

function removeFromCart(cartItemId, prodId = null) {
  if(cartItemId && typeof cartItemId === 'string') {
      cart = cart.filter(c => c.cartItemId !== cartItemId);
  } else {
      cart = cart.filter(c => c.prodId != (prodId || cartItemId));
  }
  renderCart();
  renderProdGrid();
}

function clearCart() {
  cart = [];
  const d = document.getElementById('posDiscount'); if (d) d.value = window.globalStoreSettings?.discount || 0;
  const t = document.getElementById('posTax'); if (t) t.value = window.globalStoreSettings?.tax || 0;
  const p = document.getElementById('posPaid'); if (p) p.value = 0;
  const n = document.getElementById('posNotes'); if (n) n.value = '';
  const cust = document.getElementById('posCustomer'); if (cust) cust.value = '';
  const custName = document.getElementById('posCustomerName'); if (custName) custName.value = '';
  const pay = document.getElementById('posPayment'); if (pay) pay.value = 'Cash';
  const ledger = document.getElementById('posSaveToLedger'); if (ledger) ledger.checked = false;
  renderCart();
  renderProdGrid();
}

function renderCart() {
  const tbody = document.getElementById('cartTbody');
  const badge = document.getElementById('cartBadge');
  const mobileBadge = document.getElementById('mobileCartBadge');

  let sumSub = 0, totalItems = 0;
  cart.forEach(item => {
    sumSub += item.price * item.qty;
    totalItems += item.qty;
  });
  
  totalItems = parseFloat(Number(totalItems).toFixed(3));

  if (badge) badge.textContent = totalItems + ' item' + (totalItems !== 1 ? 's' : '');
  if (mobileBadge) mobileBadge.textContent = totalItems;
  if (document.getElementById('sumItems')) document.getElementById('sumItems').textContent = totalItems;
  if (document.getElementById('sumSubtotal')) document.getElementById('sumSubtotal').textContent = fmtCur(sumSub);

  if (!cart.length) {
    if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="empty-cart-state">
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
          <div style="display:flex; align-items:center; gap:10px;">
            ${(posFilter.showImage && item.image) ? `<img src="/storage/${item.image}" style="width:32px; height:32px; object-fit:cover; border-radius:4px; flex-shrink:0;">` : `<div style="width:32px; height:32px; background:var(--border-light); border-radius:4px; flex-shrink:0; display:flex; align-items:center; justify-content:center;"><svg width="16" height="16" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg></div>`}
            <div>
              ${item.brand ? `<div style="font-size:9px; text-transform:uppercase; color:var(--text-muted); font-weight:700; letter-spacing:0.5px; margin-bottom:1px; line-height:1;">${item.brand}</div>` : ''}
              <div class="cart-item-name" style="margin:0; line-height:1.2; font-weight:600;">${item.name}</div>
              ${item.ff_var_name ? `<div style="font-size:11px; color:var(--primary); font-weight:600; margin-top:2px;">Size: ${item.ff_var_name}</div>` : ''}
              ${item.ff_addons && item.ff_addons.length > 0 ? `<div style="font-size:10px; color:var(--text-muted); margin-top:2px;">+ ${item.ff_addons.map(a => a.name).join(', ')}</div>` : ''}
            </div>
          </div>
        </td>
        <td style="text-align:center">
          <div class="qty-control" style="display:flex; align-items:center;">
            <button class="qty-btn" onclick="changeQty(${item.cartItemId ? `'${item.cartItemId}'` : item.prodId}, -1, ${!!item.cartItemId})">−</button>
            <input type="number" class="input input-sm qty-display" style="width: 60px; text-align: center; padding: 2px; margin: 0 4px;" step="any" value="${parseFloat(Number(item.qty).toFixed(3))}" onchange="setQty(${item.prodId}, this.value)">
            <button class="qty-btn" onclick="changeQty(${item.cartItemId ? `'${item.cartItemId}'` : item.prodId}, 1, ${!!item.cartItemId})">+</button>
          </div>
        </td>
          <td style="text-align:right;">
             <input type="number" class="input input-sm" style="width: 75px; text-align: right; padding: 2px 4px; height: 26px; font-size: 13px; display: inline-block; font-weight: 600; color: var(--primary);" value="${item.price}" onchange="changePrice(${item.prodId}, this.value)" title="Edit Price">
          </td>
          <td style="text-align:right;">
             <input type="number" class="input input-sm" style="width: 75px; text-align: right; padding: 2px 4px; height: 26px; font-size: 13px; display: inline-block; font-weight: 600; color: var(--success);" value="${item.sub.toFixed(2)}" onchange="setTotal(${item.prodId}, this.value)" title="Enter Total (Auto-calculates Qty)">
          </td>
          <td style="text-align:right">
            ${['kg', 'liters', 'g'].includes(item.unit) ? `<button class="action-btn" style="width:24px;height:24px;padding:4px;color:var(--primary);margin-right:2px;" onclick="openQuickQtyModal(${item.prodId})" title="Quick Qty"><svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><line x1="8" y1="6" x2="16" y2="6"></line><line x1="16" y1="14" x2="16.01" y2="14"></line><line x1="12" y1="14" x2="12.01" y2="14"></line><line x1="8" y1="14" x2="8.01" y2="14"></line><line x1="16" y1="18" x2="16.01" y2="18"></line><line x1="12" y1="18" x2="12.01" y2="18"></line><line x1="8" y1="18" x2="8.01" y2="18"></line></svg></button>` : ''}
            <button class="action-btn del" style="width:24px;height:24px;padding:4px;" onclick="removeFromCart(${item.cartItemId ? `'${item.cartItemId}'` : item.prodId})">${DEL_SVG}</button>
          </td>
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
  const due = grand - paid;
  const absDue = Math.abs(due);
  const elRet = document.getElementById('sumReturn');

  const formattedTotalItems = parseFloat(Number(cart.reduce((s, c) => s + c.qty, 0)).toFixed(3));
  const elItems = document.getElementById('sumItems'); if (elItems) elItems.textContent = formattedTotalItems;
  const elSub = document.getElementById('sumSubtotal'); if (elSub) elSub.textContent = fmtCur(subtotal);
  const elGrand = document.getElementById('sumGrandTotal'); if (elGrand) elGrand.textContent = fmtCur(grand);

  const elRetLabel = document.querySelector('.change-row .sum-label');
  const changeRow = document.querySelector('.change-row');

  const ledgerRow = document.getElementById('posLedgerRow');
  const ledgerText = document.getElementById('posLedgerText');
  const custId = document.getElementById('posCustomer')?.value;
  let prevBal = 0;
  if (custId) {
    const custObj = store.get('customers').find(c => c.id == custId);
    if (custObj) prevBal = parseFloat(custObj.balance) || 0;
  }

  if (elRet && elRetLabel && changeRow) {
    const ledgerCheck = document.getElementById('posSaveToLedger');
    const isChecked = ledgerCheck ? ledgerCheck.checked : false;
    
    let displayDue = isChecked ? 0 : due;
    let displayAbsDue = Math.abs(displayDue);

    if (displayDue > 0) {
      elRetLabel.textContent = "Amount Due";
      elRet.textContent = fmtCur(displayDue);
      changeRow.style.backgroundColor = "var(--danger-light, #ffebee)";
    } else if (displayDue < 0) {
      elRetLabel.textContent = "Change Due";
      elRet.textContent = fmtCur(displayAbsDue);
      changeRow.style.backgroundColor = "var(--success-light, #e8f5e9)";
    } else {
      elRetLabel.textContent = "Change Due";
      elRet.textContent = fmtCur(0);
      changeRow.style.backgroundColor = "var(--success-light, #e8f5e9)";
    }
    let finalDue = isChecked ? (prevBal + due) : due;
    let finalAbs = Math.abs(finalDue);

    if (finalDue > 0) {
      if (ledgerText) {
        ledgerText.textContent = (isChecked ? "Net Customer will pay: " : "Customer will pay: ") + fmtCur(finalAbs);
        ledgerText.style.color = "var(--danger)";
      }
    } else if (finalDue < 0) {
      if (ledgerText) {
        ledgerText.textContent = (isChecked ? "Net Shop will pay: " : "Shop will pay: ") + fmtCur(finalAbs);
        ledgerText.style.color = "var(--success)";
      }
    } else {
      if (ledgerText) {
        ledgerText.textContent = isChecked ? "Ledger will be Settled (0.00)" : "Settled (No Balance)";
        ledgerText.style.color = "var(--text-muted)";
      }
    }

    // Show ledger row only if customer selected (not walk-in)
    if (ledgerRow) {
      if (custId) {
        ledgerRow.style.display = "flex";
      } else {
        ledgerRow.style.display = "none";
        if (ledgerCheck) ledgerCheck.checked = false;
      }
    }
  }

  // Fallback for billing blade which might have sumDue
  const elDue = document.getElementById('sumDue'); if (elDue) elDue.textContent = fmtCur(due > 0 ? due : 0);
}

async function checkout() {
  if (!cart.length) { toast('Cart is empty!', 'warning'); return; }

  const discount = parseFloat(document.getElementById('posDiscount')?.value || 0);
  const tax = parseFloat(document.getElementById('posTax')?.value || 0);
  const paid = parseFloat(document.getElementById('posPaid')?.value || 0);
  const custId = document.getElementById('posCustomer')?.value || null;
  const custName = document.getElementById('posCustomerName')?.value || null;
  const payment = document.getElementById('posPayment')?.value || 'Cash';
  const notes = document.getElementById('posNotes')?.value || '';

  let subtotal = 0;
  cart.forEach(c => { subtotal += c.qty * c.price; });
  const taxAmt = subtotal * (tax / 100);
  const discAmt = subtotal * (discount / 100);
  const total = subtotal + taxAmt - discAmt;

  const due = Math.abs(total - paid);
  const isLedgerSaved = document.getElementById('posSaveToLedger')?.checked;
  const paymentStatus = (isLedgerSaved || paid >= total) ? 'paid' : (paid > 0 ? 'partial' : 'unpaid');

  if (payment === 'installment' && !window.installmentData) {
    if (!custId) {
      toast('Customer must be selected for installment payments!', 'warning');
      return;
    }
    const instModal = document.getElementById('installmentModal');
    if (instModal) {
      instModal.classList.remove('hidden');
      window.baseInstallmentTotal = total;
      document.getElementById('instPercentage').value = 0;
      document.getElementById('instTotal').value = total.toFixed(2);
      document.getElementById('instAdvance').value = paid.toFixed(2);
      calcInstallment();
    }
    return; // Pause checkout process
  }

  const payload = {
    buyer_id: custId,
    customer_name: custName,
    subtotal: subtotal,
    tax: taxAmt,
    discount: discAmt,
    total: total,
    paid_amount: (payment === 'installment' && window.installmentData) ? window.installmentData.advance : paid,
    due_amount: (payment === 'installment' && window.installmentData) ? (total - window.installmentData.advance) : due,
    payment_status: paymentStatus,
    payment_method: payment,
    save_to_ledger: (document.getElementById('posSaveToLedger') && document.getElementById('posSaveToLedger').checked) ? 1 : 0,
    is_installment: (payment === 'installment' ? 1 : 0),
    installment_down_payment: (window.installmentData ? window.installmentData.advance : 0),
    installment_months: (window.installmentData ? window.installmentData.months : 0),
    installment_monthly_amount: (window.installmentData ? window.installmentData.monthly : 0),
    installment_payment_day: (window.installmentData ? window.installmentData.payment_day : 10),
    installment_interest_percentage: (window.installmentData ? window.installmentData.interest_percentage : 0),
    installment_actual_price: (window.installmentData ? window.installmentData.actual_price : 0),
    items: cart.map(c => ({
      product_id: c.prodId,
      qty: c.qty,
      price: c.price,
      stock_units: c.stock_units || [],
      ff_var_name: c.ff_var_name || null,
      ff_var_price: c.ff_var_price || 0,
      ff_addons: c.ff_addons || []
    }))
  };

  try {
    const btn = document.getElementById('checkoutBtn');
    const origText = btn.innerHTML;
    btn.innerHTML = 'Processing...';
    btn.disabled = true;

    const isEditing = window.editingOrderId;
    const url = isEditing ? `/shop/api/orders/${window.editingOrderId}` : '/shop/api/orders';
    const method = isEditing ? 'PUT' : 'POST';

    const response = await api(url, method, payload);

    clearCart();

    if (isEditing) {
      window.editingOrderId = null;
      btn.innerHTML = '<span>Pay Now</span> <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
      toast(`Order updated successfully!`, 'success');
    } else {
      toast(`Order generated successfully!`, 'success');
    }

    await syncData(); // Refresh product grid

    // Open Invoice in new tab or popup
    if (response.order_id) {
      window.open(`/shop/orders/${response.order_id}/invoice`, 'InvoicePopup', 'width=400,height=600');
    }

    if (!isEditing) btn.innerHTML = origText;
    btn.disabled = false;
  } catch (e) {
    toast(e.message || 'Error occurred', 'danger');
    const btn = document.getElementById('checkoutBtn');
    if (btn) {
      btn.innerHTML = '<span>Pay Now</span> <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>';
      btn.disabled = false;
    }
  }
}

// ============================================================
// QUICK QUANTITY CALCULATOR
// ============================================================
window.quickQtyCurrentProdId = null;
window.quickQtyCurrentValue = 0;

function openQuickQtyModal(prodId) {
    window.quickQtyCurrentProdId = prodId;
    window.quickQtyCurrentValue = 0;
    
    const item = cart.find(c => c.prodId == prodId);
    if (item) {
        window.quickQtyCurrentValue = parseFloat(Number(item.qty).toFixed(3)) || 0;
    }
    
    updateQuickQtyDisplay();
    document.getElementById('quickQtyModal').classList.remove('hidden');
}

function addQuickQty(amount) {
    window.quickQtyCurrentValue += amount;
    updateQuickQtyDisplay();
}

function clearQuickQty() {
    window.quickQtyCurrentValue = 0;
    updateQuickQtyDisplay();
}

function updateQuickQtyDisplay() {
    const displayEl = document.getElementById('quickQtyDisplay');
    if (displayEl) {
        displayEl.textContent = parseFloat(Number(window.quickQtyCurrentValue).toFixed(3));
    }
}

function confirmQuickQty() {
    if (window.quickQtyCurrentProdId) {
        setQty(window.quickQtyCurrentProdId, window.quickQtyCurrentValue);
    }
    document.getElementById('quickQtyModal').classList.add('hidden');
}

function closeQuickQtyModal() {
    document.getElementById('quickQtyModal').classList.add('hidden');
}// 