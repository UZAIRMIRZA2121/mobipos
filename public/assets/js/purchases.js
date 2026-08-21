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
    ['suppName', 'suppCompany', 'suppPhone', 'suppEmail', 'suppAddress', 'suppNotes'].forEach(id => document.getElementById(id).value = '');
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
  } catch (e) { toast('Error saving supplier', 'danger'); }
}

function deleteSupplier(id) {
  confirmDelete('Delete this supplier?', async () => {
    try {
      await api('/suppliers/' + id, 'DELETE');
      toast('Supplier deleted', 'danger');
      await syncData();
    } catch (e) { toast('Error deleting', 'danger'); }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const ss = document.getElementById('suppSearch');
  if (ss) ss.addEventListener('input', renderSuppliers);
});

// ============================================================
// PURCHASE ORDERS
// ============================================================
let currentPOItems = [];
window.poPaymentStatus = '';

function setPOStatusFilter(status, btn) {
  if (window.poPaymentStatus === status) {
    window.poPaymentStatus = '';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-outline');
  } else {
    window.poPaymentStatus = status;
    document.querySelectorAll('.po-status-filter').forEach(b => {
      b.classList.remove('btn-primary');
      b.classList.add('btn-outline');
    });
    btn.classList.remove('btn-outline');
    btn.classList.add('btn-primary');
  }
  renderPurchaseOrders();
}

async function renderPurchaseOrders() {
  try {
    const sDate = document.getElementById('poStartDate')?.value || '';
    const eDate = document.getElementById('poEndDate')?.value || '';
    const status = window.poPaymentStatus || '';
    const res = await api(`/shop/api/purchase-orders?start_date=${sDate}&end_date=${eDate}&payment_status=${status}`);
    // Fallback to res if it's an array (old API) or res.data if it's the new format
    const pos = Array.isArray(res) ? res : res.data;

    if (res.totals) {
      const elGrandTotal = document.getElementById('poGrandTotal');
      const elTotalPaid = document.getElementById('poTotalPaid');
      const elTotalDue = document.getElementById('poTotalDue');
      if (elGrandTotal) elGrandTotal.textContent = fmtCur(res.totals.grand_total);
      if (elTotalPaid) elTotalPaid.textContent = fmtCur(res.totals.total_paid);
      if (elTotalDue) elTotalDue.textContent = fmtCur(res.totals.total_due);
    }

    const tbody = document.getElementById('poTbody');
    if (!tbody) return;

    if (!pos || !pos.length) {
      tbody.innerHTML = '<tr><td colspan="9" class="empty-cell">No purchase orders found</td></tr>';
      return;
    }

    tbody.innerHTML = pos.map(po => `
          <tr>
              <td>#PO-${po.id}</td>
              <td>${fmtDateTime(po.created_at)}</td>
              <td style="font-weight: 500">${po.supplier_name || '-'}</td>
              <td>${po.items ? po.items.length : 0} items</td>
              <td style="font-weight: 600">${fmtCur(po.amount)}</td>
              <td style="color:var(--success)">${fmtCur(po.paid_amount)}</td>
              <td style="color:var(--danger)">${fmtCur(po.remaining_amount)}</td>
              <td>
                <span class="badge ${po.payment_status === 'paid' ? 'badge-success' : (po.payment_status === 'partial' ? 'badge-warning' : 'badge-danger')}">
                  ${po.payment_status.toUpperCase()}
                </span>
              </td>
              <td class="text-right">
                  <button class="action-btn" style="color:var(--primary)" onclick='viewPO(${JSON.stringify(po).replace(/'/g, "&apos;")})' title="View">
                      ${VIEW_SVG}
                  </button>
                  <button class="action-btn edit" onclick='editPO(${JSON.stringify(po).replace(/'/g, "&apos;")})' title="Edit">
                      ${EDIT_SVG}
                  </button>
                  <button class="action-btn del" onclick="deletePO(${po.id})" title="Delete">
                      ${DEL_SVG}
                  </button>
              </td>
          </tr>
      `).join('');
  } catch (err) {
    console.error(err);
    toast('Failed to load purchase orders', 'danger');
  }
}

let currentViewedPO = null;

function viewPO(po) {
  currentViewedPO = po;
  document.getElementById('viewPoSupplier').textContent = po.supplier_name || '-';
  document.getElementById('viewPoDate').textContent = fmtDateTime(po.created_at);

  const tbody = document.getElementById('viewPoItemsTbody');
  if (po.items && po.items.length > 0) {
    tbody.innerHTML = po.items.map(item => `
          <tr>
              <td>
                 <div style="font-weight:500">${item.product ? item.product.name + (item.product.condition || item.product.color ? ` (${[item.product.condition, item.product.color].filter(Boolean).join(' - ')})` : '') : 'Unknown Product'}</div>
                 ${item.product && (item.product.code || item.product.barcode) ? `<div style="font-size:11px; color:var(--text-muted); font-family:monospace;">Code: ${item.product.code || item.product.barcode}</div>` : ''}
              </td>
              <td>${item.qty}</td>
              <td>${fmtCur(item.price)}</td>
              <td>${fmtCur(item.amount)}</td>
          </tr>
      `).join('');
  } else {
    tbody.innerHTML = '<tr><td colspan="4" class="empty-cell">No items</td></tr>';
  }

  document.getElementById('viewPoTotalAmount').textContent = fmtCur(po.amount);
  document.getElementById('viewPoPaidAmount').textContent = fmtCur(po.paid_amount);
  document.getElementById('viewPoRemainingAmount').textContent = fmtCur(po.remaining_amount);

  document.getElementById('viewPoModal').classList.remove('hidden');
}

function closeViewPOModal() {
  document.getElementById('viewPoModal').classList.add('hidden');
  currentViewedPO = null;
}

function printPO() {
  if (!currentViewedPO) return;
  const po = currentViewedPO;

  let itemsHtml = '';
  if (po.items && po.items.length > 0) {
    itemsHtml = po.items.map(item => `
            <tr>
                <td>
                   <div style="font-weight:500">${item.product ? item.product.name + (item.product.condition || item.product.color ? ` (${[item.product.condition, item.product.color].filter(Boolean).join(' - ')})` : '') : 'Unknown Product'}</div>
                   ${item.product && (item.product.code || item.product.barcode) ? `<div style="font-size:12px; color:#666; font-family:monospace;">Code: ${item.product.code || item.product.barcode}</div>` : ''}
                </td>
                <td>${item.qty}</td>
                <td>${fmtCur(item.price)}</td>
                <td style="text-align: right;">${fmtCur(item.amount)}</td>
            </tr>
        `).join('');
  } else {
    itemsHtml = '<tr><td colspan="4" style="text-align: center;">No items</td></tr>';
  }

  const printHtml = `
    <html>
    <head>
        <title>Purchase Order #${po.id}</title>
        <style>
            body { font-family: 'Segoe UI', Arial, sans-serif; padding: 20px; color: #333; }
            .header { text-align: center; margin-bottom: 40px; }
            .header h1 { margin: 0; font-size: 24px; }
            .info-table { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
            .info-table td { padding: 8px 0; border-bottom: 1px solid #eee; }
            .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
            .items-table th { padding: 8px 0; border-bottom: 2px solid #333; text-align: left; }
            .items-table td { padding: 8px 0; border-bottom: 1px solid #eee; }
            .totals-section { width: 100%; margin-top: 15px; }
            .totals-section table { width: 100%; border-collapse: collapse; }
            .totals-section td { padding: 8px 0; border-bottom: 1px solid #eee; }
            .totals-section .total-row { font-weight: bold; font-size: 1.1em; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>PURCHASE ORDER</h1>
            <p>Order #${po.id} | Date: ${fmtDateTime(po.created_at)}</p>
        </div>
        
        <table class="info-table">
            <tr>
                <td style="font-weight: bold;">Supplier:</td>
                <td style="text-align: right;">${po.supplier_name || '-'}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Status:</td>
                <td style="text-align: right;">${po.payment_status.toUpperCase()}</td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price/Unit</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHtml}
            </tbody>
        </table>

        <div class="totals-section">
            <table>
                <tr>
                    <td>Subtotal:</td>
                    <td style="text-align: right;">${fmtCur(po.amount)}</td>
                </tr>
                <tr>
                    <td>Paid Amount:</td>
                    <td style="text-align: right; color: green;">${fmtCur(po.paid_amount)}</td>
                </tr>
                <tr class="total-row">
                    <td>Balance Due:</td>
                    <td style="text-align: right; color: red;">${fmtCur(po.remaining_amount)}</td>
                </tr>
            </table>
        </div>
    </body>
    </html>
    `;

  let printFrame = document.getElementById('poPrintFrame');
  if (!printFrame) {
    printFrame = document.createElement('iframe');
    printFrame.id = 'poPrintFrame';
    printFrame.style.position = 'absolute';
    printFrame.style.width = '100vw';
    printFrame.style.height = '100vh';
    printFrame.style.left = '-10000px';
    printFrame.style.top = '-10000px';
    printFrame.style.border = 'none';
    document.body.appendChild(printFrame);
  }

  const doc = printFrame.contentWindow.document;
  doc.open();
  // Remove the script that tries to call window.close() since we're in an iframe now
  doc.write(printHtml.replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, ''));
  doc.close();

  setTimeout(() => {
    printFrame.contentWindow.focus();
    printFrame.contentWindow.print();
  }, 250);
}

function populatePoProductDropdown(q = '') {
  const listEl = document.getElementById('poProductDropdownList');
  if (!listEl) return;

  let prods = store.get('products') || [];
  
  if (q) {
    q = q.toLowerCase();
    prods = prods.filter(p => 
      (p.code || '').toLowerCase().includes(q) || 
      (p.barcode || '').toLowerCase().includes(q)
    );
  }

  if (prods.length === 0) {
    listEl.innerHTML = '<div style="padding:10px; color:var(--text-muted); text-align:center;">No products found</div>';
    return;
  }

  listEl.innerHTML = prods.map(p => {
      let codeStr = p.code ? ` (Code: ${p.code})` : (p.barcode ? ` (Barcode: ${p.barcode})` : '');
      let attrStr = (p.condition || p.color) ? ` (${[p.condition, p.color].filter(Boolean).join(' - ')})` : '';
      let displayStr = `${p.name}${attrStr}${codeStr}`;
      let pFullName = p.name + attrStr;
      let safeName = (pFullName || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
      let safeCode = (p.code || p.barcode || '').replace(/'/g, "\\'").replace(/"/g, "&quot;");
      let safeDisplayStr = displayStr.replace(/'/g, "\\'").replace(/"/g, "&quot;");
      
      return `<div class="po-dropdown-item" style="padding:8px 12px; border-bottom:1px solid var(--border-light); cursor:pointer;" 
                   onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'"
                   onclick="selectPoProduct(${p.id}, '${safeName}', ${p.purchase_price || 0}, '${safeCode}', '${safeDisplayStr}')">
                   <div style="font-weight:500; font-size:14px; margin-bottom: 2px;">${displayStr}</div>
                   <div style="font-size:12px; color:var(--text-muted);">${fmtCur(p.purchase_price)}</div>
              </div>`;
  }).join('');
}

function selectPoProduct(id, name, price, code, displayStr) {
  const hiddenInput = document.getElementById('poProductSelect');
  if(hiddenInput) {
      hiddenInput.value = id;
      hiddenInput.dataset.name = name;
      hiddenInput.dataset.price = price;
      hiddenInput.dataset.code = code;
  }
  
  const searchInput = document.getElementById('poProductSearch');
  if(searchInput) searchInput.value = displayStr;
  
  togglePoDropdown(false);
}

function filterPoDropdown(q) {
  togglePoDropdown(true);
  populatePoProductDropdown(q);
}

function togglePoDropdown(show) {
  const listEl = document.getElementById('poProductDropdownList');
  if (listEl) {
    listEl.style.display = show ? 'block' : 'none';
  }
}

function openPOModal() {
  currentPOItems = [];
  document.getElementById('poId').value = '';
  document.getElementById('poSupplier').value = '';
  document.getElementById('poTotalAmount').value = '0';
  document.getElementById('poPaidAmount').value = '0';

  const searchInput = document.getElementById('poProductSearch');
  if (searchInput) searchInput.value = '';

  // Populate products dropdown
  populatePoProductDropdown();

  document.getElementById('poModalTitle').textContent = 'Add to Purchase';
  renderPOItemsTable();
  document.getElementById('poModal').classList.remove('hidden');
}

function editPO(po) {
  document.getElementById('poId').value = po.id;
  document.getElementById('poSupplier').value = po.supplier_name || '';
  document.getElementById('poPaidAmount').value = po.paid_amount || '0';

  currentPOItems = po.items ? po.items.map(item => {
    const p = (store.get('products') || []).find(x => x.id === item.product_id);
    return {
      product_id: item.product_id,
      name: item.product ? item.product.name : 'Unknown Product',
      code: item.product ? (item.product.code || item.product.barcode || '') : '',
      qty: item.qty,
      price: item.price,
      sale_price: p ? parseFloat(p.sale_price) || 0 : 0,
      type: p ? p.type : '',
      imeis: []
    };
  }) : [];

  const searchInput = document.getElementById('poProductSearch');
  if (searchInput) searchInput.value = '';

  // Populate products dropdown
  populatePoProductDropdown();

  document.getElementById('poModalTitle').textContent = 'Edit Purchase Order';
  renderPOItemsTable();
  document.getElementById('poModal').classList.remove('hidden');
}

function closePOModal() {
  document.getElementById('poModal').classList.add('hidden');
}

function addProdToPO() {
  const hiddenInput = document.getElementById('poProductSelect');
  if (!hiddenInput || !hiddenInput.value) return toast('Please select a product', 'warning');

  const prodId = parseInt(hiddenInput.value);
  const name = hiddenInput.dataset.name;
  const code = hiddenInput.dataset.code || '';
  const price = parseFloat(hiddenInput.dataset.price) || 0;
  
  const p = (store.get('products') || []).find(x => x.id === prodId);
  const salePrice = p ? parseFloat(p.sale_price) || 0 : 0;
  const type = p ? p.type : '';

  // Check if already added
  const existing = currentPOItems.find(i => i.product_id === prodId);
  if (existing) {
    existing.qty += 1;
  } else {
    currentPOItems.push({ 
       product_id: prodId, 
       name: name, 
       code: code, 
       qty: 1, 
       price: price,
       sale_price: salePrice,
       type: type,
       imeis: [] 
    });
  }

  // Clear selection
  hiddenInput.value = '';
  const searchInput = document.getElementById('poProductSearch');
  if(searchInput) searchInput.value = '';

  renderPOItemsTable();
}

function removeProdFromPO(index) {
  currentPOItems.splice(index, 1);
  renderPOItemsTable();
}

function updatePOItemQty(index, qty) {
  currentPOItems[index].qty = parseInt(qty) || 1;
  renderPOItemsTable();
}

function updatePOItemPrice(index, price) {
  currentPOItems[index].price = parseFloat(price) || 0;
  renderPOItemsTable();
}

function updatePOItemSalePrice(index, price) {
  currentPOItems[index].sale_price = parseFloat(price) || 0;
}

function openPoImeiSetup(index) {
  const row = document.getElementById(`po-imei-row-${index}`);
  const container = document.getElementById(`po-imei-container-${index}`);
  if (!row || !container) return;
  
  const item = currentPOItems[index];
  item.imeis = item.imeis || [];
  
  let html = '';
  for(let i=0; i<item.qty; i++) {
     let val = item.imeis[i] || '';
     html += `<input type="text" class="input" style="padding:4px; font-size:12px;" placeholder="IMEI ${i+1}" value="${val}" oninput="updatePoImei(${index}, ${i}, this.value)" />`;
  }
  container.innerHTML = html;
  row.style.display = 'table-row';
}

function closePoImeiSetup(index) {
  const row = document.getElementById(`po-imei-row-${index}`);
  if (row) row.style.display = 'none';
  renderPOItemsTable();
}

function updatePoImei(itemIndex, imeiIndex, val) {
  currentPOItems[itemIndex].imeis[imeiIndex] = val;
}

function renderPOItemsTable() {
  const tbody = document.getElementById('poItemsTbody');
  if (!currentPOItems.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="empty-cell">No products added yet</td></tr>';
    document.getElementById('poTotalAmount').value = '0';
    return;
  }

  let total = 0;
  tbody.innerHTML = currentPOItems.map((item, index) => {
    const amount = item.qty * item.price;
    total += amount;
    
    const needsImei = ['mobile', 'tablet', 'laptop'].includes(item.type);
    let imeisFilled = (item.imeis || []).filter(x => x && x.trim() !== '').length;

    return `
      <tr>
        <td>${item.name} ${item.code ? `<div style="font-size:11px; color:var(--text-muted); font-family:monospace;">Code: ${item.code}</div>` : ''}</td>
        <td><input type="number" class="input" style="padding:4px; height:auto" min="1" value="${item.qty}" onchange="updatePOItemQty(${index}, this.value)"></td>
        <td><input type="number" class="input" style="padding:4px; height:auto" min="0" value="${item.price}" onchange="updatePOItemPrice(${index}, this.value)"></td>
        <td><input type="number" class="input" style="padding:4px; height:auto" min="0" value="${item.sale_price || 0}" onchange="updatePOItemSalePrice(${index}, this.value)"></td>
        <td>
           ${needsImei ? `<button class="btn btn-sm btn-outline" onclick="openPoImeiSetup(${index})">IMEIs (${imeisFilled}/${item.qty})</button>` : `<span class="text-muted" style="font-size:12px;">N/A</span>`}
        </td>
        <td>${fmtCur(amount)}</td>
        <td class="text-right">
          <button class="btn btn-ghost" style="color:var(--danger); padding:4px;" onclick="removeProdFromPO(${index})">✕</button>
        </td>
      </tr>
      <tr id="po-imei-row-${index}" style="display:none; background:#fafafa;">
         <td colspan="7" style="padding: 10px; border-bottom: 2px solid var(--border);">
             <div style="font-size:12px; font-weight:600; margin-bottom:8px;">Setup IMEIs for ${item.name}</div>
             <div id="po-imei-container-${index}" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(150px, 1fr)); gap:10px;">
                 <!-- IMEIs injected here -->
             </div>
             <button class="btn btn-sm btn-secondary" style="margin-top:10px;" onclick="closePoImeiSetup(${index})">Done</button>
         </td>
      </tr>
      `;
  }).join('');

  document.getElementById('poTotalAmount').value = total;
}

function calcPOTotal() {
  // Can be used if additional logic is needed on paid amount change, e.g. validating it doesn't exceed total
}

async function savePO() {
  if (!currentPOItems.length) return toast('Please add at least one product', 'warning');

  const id = document.getElementById('poId').value;
  const data = {
    supplier_name: document.getElementById('poSupplier').value,
    paid_amount: parseFloat(document.getElementById('poPaidAmount').value) || 0,
    items: currentPOItems.map(i => ({
      product_id: i.product_id,
      qty: i.qty,
      price: i.price,
      sale_price: i.sale_price,
      imeis: i.imeis || []
    }))
  };

  try {
    const url = id ? `/shop/api/purchase-orders/${id}` : '/shop/api/purchase-orders';
    const method = id ? 'PUT' : 'POST';

    const res = await api(url, method, data);
    toast(res.message);
    closePOModal();

    // Since stock changed, re-sync data
    await syncData();
    if (document.getElementById('page-purchase-orders')) renderPurchaseOrders();
  } catch (err) {
    toast(err.message, 'danger');
  }
}

async function deletePO(id) {
  if (!confirm('Delete this purchase order? This will revert the stock levels.')) return;
  try {
    const res = await api(`/shop/api/purchase-orders/${id}`, 'DELETE');
    toast(res.message);

    // Since stock reverted, re-sync data
    await syncData();
    if (document.getElementById('page-purchase-orders')) renderPurchaseOrders();
  } catch (err) {
    toast(err.message, 'danger');
  }
}

