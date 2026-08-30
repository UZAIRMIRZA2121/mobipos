// ============================================================
// MEDICINES (PRODUCTS)
// ============================================================
const PRINT_SVG = `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>`;
const LOSS_SVG = `<svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>`;
window.prodCurrentPage = 1;
window.prodPerPage = 10;
let lastProdFilters = '';

function renderProducts() {
  const q = (document.getElementById('prodSearch')?.value || '').toLowerCase();
  const condFilter = document.getElementById('prodConditionFilter')?.value || '';
  const typeFilter = document.getElementById('prodTypeFilter')?.value || '';
  const catFilter = document.getElementById('prodCategoryFilter')?.value || '';

  const currentFilters = `${q}|${condFilter}|${typeFilter}|${catFilter}`;
  if (lastProdFilters !== currentFilters) {
      window.prodCurrentPage = 1;
      lastProdFilters = currentFilters;
  }

  const catSelect = document.getElementById('prodCategoryFilter');
  if (catSelect && catSelect.options.length <= 1) {
    const cats = store.get('categories') || [];
    cats.forEach(c => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.name;
      catSelect.appendChild(opt);
    });
  }

  let prods = store.get('products') || [];
  if (q) prods = prods.filter(p =>
    (p.code || '').toLowerCase().includes(q) || (p.name || '').toLowerCase().includes(q) || (p.imei || '').toLowerCase().includes(q) || (p.barcode || '').toLowerCase().includes(q)
  );

  if (condFilter) {
    prods = prods.filter(p => (p.condition || '').toLowerCase() === condFilter.toLowerCase());
  }

  if (typeFilter) {
    prods = prods.filter(p => p.type === typeFilter);
  }

  if (catFilter) {
    prods = prods.filter(p => p.category_id == catFilter);
  }

  const totalItems = prods.length;
  const startIndex = (window.prodCurrentPage - 1) * window.prodPerPage;
  const paginatedProds = prods.slice(startIndex, startIndex + window.prodPerPage);

  const tbody = document.getElementById('prodTbody');
  if (!tbody) return;

  tbody.innerHTML = paginatedProds.length ?
    paginatedProds.map(p => {
      return `<tr>
        <td>
          ${p.image ? `<img src="/storage/${p.image}" alt="Product" style="width:40px; height:40px; border-radius:4px; object-fit:cover;">` : '<div style="width:40px; height:40px; background:#f3f4f6; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#9ca3af; font-size:10px;">No Img</div>'}
        </td>
        <td>
          ${p.code ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono);margin-bottom:2px;">Code: ${p.code}</div>` : ''}
          <div style="font-weight:500">${p.name} ${p.condition || p.color ? `<span style="font-size:12px; font-weight:normal; color:var(--text-muted);">(${[p.condition, p.color].filter(Boolean).join(' - ')})</span>` : ''}</div>
          ${p.imei ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono)">IMEI/SN: ${p.imei}</div>` : ''}
          ${p.barcode ? `<div style="font-size:11px;color:var(--text-muted);font-family:var(--mono)">Barcode: ${p.barcode}</div>` : ''}
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
          ${p.barcode ? `<button class="action-btn" style="color:var(--primary)" onclick="openPrintBarcodeModal('${p.barcode}')" title="Print Barcode">${PRINT_SVG}</button>` : ''}
          <button class="action-btn" style="color:var(--primary)" onclick="openProdSalesModal(${p.id})" title="View Sales">${VIEW_SVG}</button>
          <button class="action-btn edit" onclick="openProductModal(${p.id})" title="Edit">${EDIT_SVG}</button>
          <button class="action-btn del" onclick="deleteProduct(${p.id})" title="Delete">${DEL_SVG}</button>
          <button class="action-btn" style="color:var(--danger)" onclick="openLossModal(${p.id})" title="Report Loss">${LOSS_SVG}</button>
        </td>
      </tr>`;
    }).join('') :
    '<tr><td colspan="9" class="empty-cell">No products found</td></tr>';
    
  renderProdPagination(totalItems);
}

function renderProdPagination(totalItems) {
    const container = document.getElementById('prodPagination');
    if (!container) return;
    
    if (totalItems <= window.prodPerPage) {
        container.innerHTML = '';
        return;
    }
    
    const totalPages = Math.ceil(totalItems / window.prodPerPage);
    let html = '';
    
    html += `<button class="btn btn-outline btn-sm" ${window.prodCurrentPage === 1 ? 'disabled' : ''} onclick="window.prodCurrentPage--; renderProducts()">Prev</button>`;
    
    // Simple pagination logic, show up to 5 pages
    let startPage = Math.max(1, window.prodCurrentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        html += `<button class="btn btn-sm ${window.prodCurrentPage === i ? 'btn-primary' : 'btn-outline'}" onclick="window.prodCurrentPage = ${i}; renderProducts()">${i}</button>`;
    }
    
    html += `<button class="btn btn-outline btn-sm" ${window.prodCurrentPage === totalPages ? 'disabled' : ''} onclick="window.prodCurrentPage++; renderProducts()">Next</button>`;
    
    html += `<span style="font-size: 13px; color: var(--text-muted); margin-left: 10px;">Total: ${totalItems} items</span>`;
    
    container.innerHTML = html;
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
    document.getElementById('prodCode').value = p.code || '';
    document.getElementById('prodBarcode').value = p.barcode || '';

    document.getElementById('prodColor').value = p.color || '';
    document.getElementById('prodStorage').value = p.storage || '';
    
    // Set Metadata
    if (p.meta_data) {
       const meta = typeof p.meta_data === 'string' ? JSON.parse(p.meta_data) : p.meta_data;
       const prodBrand = document.getElementById('prodBrand');
       if(prodBrand) prodBrand.value = meta.brand || '';
       const prodSize = document.getElementById('prodSize');
       if(prodSize) prodSize.value = meta.size || '';
       const prodWeight = document.getElementById('prodWeight');
       if(prodWeight) prodWeight.value = meta.weight || '';
       const prodExpiry = document.getElementById('prodExpiry');
       if(prodExpiry) prodExpiry.value = meta.expiry_date || '';
    }

    document.getElementById('prodPurchase').value = p.purchase;
    document.getElementById('prodSale').value = p.sale;
    document.getElementById('prodDiscount').value = p.discount || '';
    document.getElementById('prodStatus').value = p.status;
    document.getElementById('prodStock').value = p.stock !== undefined ? p.stock : 1;
    // Dynamic IMEI Rendering
    if (typeof renderImeiFields === 'function') {
      renderImeiFields(true);
    }

    document.getElementById('prodCategory').value = p.category_id || '';
    document.getElementById('prodBuyer').value = p.buyer_id || '';
    
    if (p.image) {
      document.getElementById('existingImagePreview').src = '/storage/' + p.image;
      document.getElementById('existingImageContainer').style.display = 'block';
    } else {
      document.getElementById('existingImageContainer').style.display = 'none';
    }
    document.getElementById('prodImageDeleted').value = '0';
  } else {
    document.getElementById('prodModalTitle').textContent = 'Add Product';
    document.getElementById('prodId').value = '';
    ['prodName', 'prodCode', 'prodBarcode', 'prodColor', 'prodStorage', 'prodBrand', 'prodSize', 'prodWeight', 'prodExpiry', 'prodPurchase', 'prodSale', 'prodDiscount', 'prodImage', 'prodBuyer'].forEach(id => {
      const el = document.getElementById(id);
      if(el) el.value = '';
    });
    if (typeof clearPhoto === 'function') clearPhoto();
    document.getElementById('existingImageContainer').style.display = 'none';
    document.getElementById('prodImageDeleted').value = '0';
    document.getElementById('prodType').value = 'mobile';
    document.getElementById('prodCondition').value = 'new';
    document.getElementById('prodStatus').value = 'in_stock';
    document.getElementById('prodStock').value = '1';
    document.getElementById('prodCategory').value = '';
    
    if (document.getElementById('groupImeiSold')) {
       document.getElementById('groupImeiSold').style.display = 'none';
       document.getElementById('groupImeiSoldInner').innerHTML = '';
    }
    
    if (typeof renderImeiFields === 'function') renderImeiFields(true);
  }
  document.getElementById('prodModal').classList.remove('hidden');
  toggleProductFields();
}

function renderImeiFields(forceRefresh = false) {
   const stock = parseInt(document.getElementById('prodStock').value) || 1;
   const container = document.getElementById('groupImeiInner');
   const soldContainer = document.getElementById('groupImeiSold');
   const soldInner = document.getElementById('groupImeiSoldInner');
   
   if (!container) return;
   
   const prodId = document.getElementById('prodId').value;
   let stockUnits = [];
   if (prodId) {
       const p = store.get('products').find(x => x.id == prodId);
       if (p && p.stock_units) {
           stockUnits = p.stock_units;
       }
   }
   
   // Separate units
   const availableUnits = stockUnits.filter(u => u.status === 'available');
   const soldUnits = stockUnits.filter(u => u.status !== 'available');
   
   // Keep existing input values to not wipe user typing if they just change quantity
   let existingInputs = [];
   if (!forceRefresh) {
       existingInputs = Array.from(document.querySelectorAll('.unit-imei-input')).map(el => el.value);
   }
   
   let html = '';
   for(let i=0; i<stock; i++) {
      let val = '';
      if (!forceRefresh && existingInputs.length > i) {
         val = existingInputs[i] || '';
      } else if (availableUnits[i]) {
         val = availableUnits[i].imeis || '';
      }
      
      html += `<div style="margin-bottom: 8px;">
         <label style="font-size:11px; color: var(--text-muted);">Stock Unit ${i+1}</label>
         <input type="text" class="input unit-imei-input" placeholder="e.g. 3589... , 3589..." value="${val}"/>
      </div>`;
   }
   container.innerHTML = html;
   
   // Handle sold units display
   if (soldUnits.length > 0 && soldContainer && soldInner) {
      soldContainer.style.display = 'block';
      let soldHtml = '';
      soldUnits.forEach((u, i) => {
         soldHtml += `<div style="font-family: var(--mono); font-size: 11px; padding: 4px 0; border-bottom: 1px solid #ffcccc; display: flex; justify-content: space-between;">
            <span>${u.imeis || 'Unknown IMEI'}</span>
            <span style="font-weight: bold; font-size: 10px; color: #cc0000; text-transform: uppercase;">${u.status}</span>
         </div>`;
      });
      soldInner.innerHTML = soldHtml;
   } else if (soldContainer) {
      soldContainer.style.display = 'none';
      soldInner.innerHTML = '';
   }
}

function toggleProductFields() {
  const type = document.getElementById('prodType').value;
  const activeModule = window.ACTIVE_MODULE || 'mobile';

  // Hide all module specific fields
  document.querySelectorAll('.module-field').forEach(el => el.style.display = 'none');
  
  // Show fields for active module
  document.querySelectorAll('.module-' + activeModule).forEach(el => el.style.display = 'block');
  
  // Additional type checks for mobile module
  if (activeModule === 'mobile' && type === 'accessory') {
      const groupImei = document.getElementById('groupImei');
      const groupStorage = document.getElementById('groupStorage');
      if (groupImei) groupImei.style.display = 'none';
      if (groupStorage) groupStorage.style.display = 'none';
  }
}

function generateRandomBarcode() {
    const barcodeInput = document.getElementById('prodBarcode');
    if (barcodeInput) {
        let barcode = '';
        for (let i = 0; i < 12; i++) {
            barcode += Math.floor(Math.random() * 10);
        }
        barcodeInput.value = barcode;
    }
}

function openProductModal(id) {
  editProduct(id);
}
function closeProductModal() {
  document.getElementById('prodModal').classList.add('hidden');
  if (typeof clearPhoto === 'function') clearPhoto();
}

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
  formData.append('code', document.getElementById('prodCode').value.trim());
  formData.append('barcode', document.getElementById('prodBarcode').value.trim());

  const unitImeis = Array.from(document.querySelectorAll('.unit-imei-input')).map(el => el.value.trim());
  unitImeis.forEach(u => formData.append('units_imeis[]', u));

  formData.append('color', document.getElementById('prodColor') ? document.getElementById('prodColor').value.trim() : '');
  formData.append('storage', document.getElementById('prodStorage') ? document.getElementById('prodStorage').value.trim() : '');
  formData.append('purchase_price', document.getElementById('prodPurchase').value || 0);
  formData.append('sale_price', sale);
  if(document.getElementById('prodDiscount').value) formData.append('discount', document.getElementById('prodDiscount').value);
  formData.append('status', document.getElementById('prodStatus').value);
  formData.append('stock', document.getElementById('prodStock').value || 1);
  formData.append('delete_image', document.getElementById('prodImageDeleted').value);

  const meta_data = {
    brand: document.getElementById('prodBrand') ? document.getElementById('prodBrand').value.trim() : '',
    size: document.getElementById('prodSize') ? document.getElementById('prodSize').value.trim() : '',
    weight: document.getElementById('prodWeight') ? document.getElementById('prodWeight').value.trim() : '',
    expiry_date: document.getElementById('prodExpiry') ? document.getElementById('prodExpiry').value.trim() : '',
  };
  formData.append('meta_data', JSON.stringify(meta_data));

  const catId = parseInt(document.getElementById('prodCategory').value);
  if (catId) formData.append('category_id', catId);

  const buyerId = parseInt(document.getElementById('prodBuyer').value);
  if (buyerId) formData.append('buyer_id', buyerId);

  const imgInput = document.getElementById('prodImage');
  if (imgInput.files.length > 0) {
    formData.append('image', imgInput.files[0]);
  }

  try {
    let res;
    if (editId) {
      formData.append('_method', 'PUT');
      res = await api('/shop/api/products/' + editId, 'POST', formData);
      toast('Product updated!', 'success');
    } else {
      res = await api('/shop/api/products', 'POST', formData);
      toast('Product added!', 'success');
    }
    closeProductModal();
    await syncData();
    document.dispatchEvent(new CustomEvent('productSaved', { detail: res }));
  } catch (e) { toast(e.message || 'Error saving product', 'danger'); }
}

function deleteProduct(id) {
  if(!confirm('Are you sure you want to delete this product?')) return;
  api(`/shop/api/products/${id}`, 'DELETE').then(() => {
    toast('Product deleted', 'success');
    syncData();
  }).catch(e => {
    toast(e.message || 'Error deleting product', 'danger');
  });
}

function openPrintBarcodeModal(barcode) {
  document.getElementById('printBarcodeValue').value = barcode;
  document.getElementById('printBarcodeText').textContent = barcode;
  document.getElementById('printBarcodeCopies').value = 1;
  document.getElementById('printBarcodeModal').classList.remove('hidden');
  
  const generate = () => {
      if (typeof JsBarcode === 'function') {
          JsBarcode("#printBarcodePreview", barcode, {
              format: "CODE128",
              width: 2,
              height: 60,
              displayValue: false
          });
      }
  };
  
  if (typeof JsBarcode === 'function') generate();
  else setTimeout(generate, 500);
}

function closePrintBarcodeModal() {
  document.getElementById('printBarcodeModal').classList.add('hidden');
}

function confirmPrintBarcode() {
  const barcode = document.getElementById('printBarcodeValue').value;
  const copies = parseInt(document.getElementById('printBarcodeCopies').value) || 1;
  
  const printWindow = window.open('', '_blank');
  
  let html = `
    <html>
    <head>
      <title>Print Barcode</title>
      <style>
        body { font-family: monospace; text-align: center; margin: 0; padding: 10px; }
        .barcode-container { display: inline-block; text-align: center; margin: 10px; padding: 10px; border: 1px dashed #ccc; page-break-inside: avoid; }
        svg { max-width: 100%; height: auto; }
      </style>
      <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
    </head>
    <body>
  `;
  
  for (let i = 0; i < copies; i++) {
      html += `
        <div class="barcode-container">
          <svg class="barcode-svg" data-value="${barcode}"></svg>
          <div style="font-size: 14px; font-weight: bold; letter-spacing: 2px;">${barcode}</div>
        </div>
      `;
  }
  
  html += `
      <script>
        window.onload = function() {
            const svgs = document.querySelectorAll('.barcode-svg');
            svgs.forEach(svg => {
                const val = svg.getAttribute('data-value');
                JsBarcode(svg, val, {
                    format: "CODE128",
                    width: 2,
                    height: 50,
                    displayValue: false,
                    margin: 0
                });
            });
            setTimeout(() => {
                window.print();
            }, 500);
        };
      <\/script>
    </body>
    </html>
  `;
  
  printWindow.document.open();
  printWindow.document.write(html);
  printWindow.document.close();
  
  closePrintBarcodeModal();
}

async function openProdSalesModal(id) {
  const p = store.get('products').find(x => x.id == id);
  if (!p) return;
  
  document.getElementById('prodSalesModalTitle').textContent = `Sales History: ${p.name}`;
  document.getElementById('prodSalesTbody').innerHTML = '<tr><td colspan="6" class="text-center" style="padding: 20px;">Loading...</td></tr>';
  document.getElementById('prodSalesModal').classList.remove('hidden');
  document.getElementById('prodSalesTfoot').innerHTML = '';

  try {
    const sales = await api('/shop/api/products/' + id + '/sales');
    if (sales.length === 0) {
      document.getElementById('prodSalesTbody').innerHTML = '<tr><td colspan="6" class="empty-cell">No sales found for this product.</td></tr>';
      return;
    }

    let totalQty = 0;
    let totalRevenue = 0;

    document.getElementById('prodSalesTbody').innerHTML = sales.map(s => {
      totalQty += Number(s.qty);
      totalRevenue += Number(s.total);
      return `
      <tr>
        <td>${fmtDateTime(s.date)}</td>
        <td><a href="/shop/orders/${s.order_id}/invoice" target="_blank" style="color:var(--primary);text-decoration:underline;">#${s.order_id}</a></td>
        <td>${s.customer}</td>
        <td>${s.qty}</td>
        <td class="text-right">${fmtCur(s.price)}</td>
        <td class="text-right" style="font-weight:bold">${fmtCur(s.total)}</td>
      </tr>
      `;
    }).join('');

    document.getElementById('prodSalesTfoot').innerHTML = `
      <tr>
        <td colspan="3" class="text-right"><strong>Total:</strong></td>
        <td><strong>${totalQty}</strong></td>
        <td></td>
        <td class="text-right" style="color: var(--primary);"><strong>${fmtCur(totalRevenue)}</strong></td>
      </tr>
    `;
  } catch (err) {
    document.getElementById('prodSalesTbody').innerHTML = '<tr><td colspan="6" style="color:red" class="text-center">Error loading sales data</td></tr>';
  }
}

function closeProdSalesModal() {
  document.getElementById('prodSalesModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', () => {
  const ps = document.getElementById('prodSearch');
  if (ps) ps.addEventListener('input', renderProducts);

  const pt = document.getElementById('prodType');
  if (pt) pt.addEventListener('change', toggleProductFields);
});

// ============================================================
// CAMERA FUNCTIONALITY
// ============================================================
let cameraStream = null;

function openCamera() {
  const modal = document.getElementById('cameraModal');
  const video = document.getElementById('cameraVideo');
  const previewContainer = document.getElementById('photoPreviewContainer');
  
  if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
    navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" } })
      .then(function(stream) {
        cameraStream = stream;
        video.srcObject = stream;
        modal.classList.remove('hidden');
        previewContainer.style.display = 'none';
      })
      .catch(function(err) {
        console.error("Camera error:", err);
        alert("Could not access the camera. Please ensure you have granted permissions.");
      });
  } else {
    alert("Camera API is not supported in your browser.");
  }
}

function closeCamera() {
  if (cameraStream) {
    cameraStream.getTracks().forEach(track => track.stop());
    cameraStream = null;
  }
  document.getElementById('cameraModal').classList.add('hidden');
}

function takePhoto() {
  const video = document.getElementById('cameraVideo');
  const canvas = document.getElementById('cameraCanvas');
  const preview = document.getElementById('photoPreview');
  const previewContainer = document.getElementById('photoPreviewContainer');
  const fileInput = document.getElementById('prodImage');
  
  if (!cameraStream) return;

  canvas.width = video.videoWidth;
  canvas.height = video.videoHeight;
  
  const context = canvas.getContext('2d');
  context.drawImage(video, 0, 0, canvas.width, canvas.height);
  
  canvas.toBlob(function(blob) {
    const file = new File([blob], "camera_capture_" + Date.now() + ".jpg", { type: "image/jpeg" });
    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    fileInput.files = dataTransfer.files;
    
    preview.src = URL.createObjectURL(blob);
    previewContainer.style.display = 'block';
    
    closeCamera();
  }, 'image/jpeg', 0.9);
}

function clearPhoto() {
  document.getElementById('photoPreviewContainer').style.display = 'none';
  document.getElementById('photoPreview').src = '';
  document.getElementById('prodImage').value = '';
}

function markImageForDeletion() {
  document.getElementById('existingImageContainer').style.display = 'none';
  document.getElementById('prodImageDeleted').value = '1';
}

function handleImageSelect(input) {
  const preview = document.getElementById('photoPreview');
  const previewContainer = document.getElementById('photoPreviewContainer');
  
  if (input.files && input.files[0]) {
    preview.src = URL.createObjectURL(input.files[0]);
    previewContainer.style.display = 'block';
    closeCamera();
  } else {
    clearPhoto();
  }
}

async function fetchBarcodeData() {
  const barcode = document.getElementById('prodBarcode').value.trim();
  if (!barcode) {
    alert('Please enter or scan a barcode first.');
    return;
  }

  // Show a toast or loading state
  showToast('Fetching product info...', 'info');

  try {
    const res = await fetch(`/shop/api/barcode-lookup/${barcode}`);
    if (res.ok) {
      const data = await res.json();
      if (data.found) {
        if (data.name) document.getElementById('prodName').value = data.name;
        if (data.brand) document.getElementById('prodBrand').value = data.brand;
        if (data.weight) document.getElementById('prodWeight').value = data.weight;
        showToast('Product data fetched successfully!', 'success');
      } else {
        showToast('Product not found in global databases.', 'error');
      }
    } else {
      showToast('Error looking up barcode.', 'error');
    }
  } catch (error) {
    console.error(error);
    showToast('Network error looking up barcode.', 'error');
  }
}

// ============================================================
// LOSS REPORTING
// ============================================================
function openLossModal(id) {
  const p = store.get('products').find(x => x.id == id);
  if (!p) return;
  document.getElementById('lossProdId').value = p.id;
  document.getElementById('lossPurchasePrice').value = p.purchase || 0;
  document.getElementById('lossQty').value = 1;
  document.getElementById('lossQty').max = p.stock || 0;
  calcLoss();
  document.getElementById('lossModal').classList.remove('hidden');
}

function closeLossModal() {
  document.getElementById('lossModal').classList.add('hidden');
}

function calcLoss() {
  const qty = parseFloat(document.getElementById('lossQty').value) || 0;
  const price = parseFloat(document.getElementById('lossPurchasePrice').value) || 0;
  document.getElementById('lossTotalAmount').value = (qty * price).toFixed(2);
}

async function submitLoss() {
  const id = document.getElementById('lossProdId').value;
  const qty = document.getElementById('lossQty').value;
  
  if (!id || !qty || qty <= 0) {
      toast('Please enter a valid quantity.', 'warning');
      return;
  }
  
  const p = store.get('products').find(x => x.id == id);
  if (p && qty > p.stock) {
      toast(`Maximum available stock is ${p.stock}`, 'warning');
      return;
  }

  const btn = event.target;
  const originalText = btn.innerHTML;
  btn.innerHTML = 'Processing...';
  btn.disabled = true;

  try {
      const formData = new FormData();
      formData.append('qty', qty);
      await api(`/shop/api/products/${id}/loss`, 'POST', formData);
      toast('Loss reported successfully and expense added.', 'success');
      closeLossModal();
      syncData(); // Refresh inventory data
  } catch (e) {
      toast(e.message || 'Error reporting loss', 'danger');
  } finally {
      btn.innerHTML = originalText;
      btn.disabled = false;
  }
}
