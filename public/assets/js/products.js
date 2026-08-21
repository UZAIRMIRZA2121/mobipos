// ============================================================
// MEDICINES
// ============================================================
function renderProducts() {
  const q = (document.getElementById('prodSearch')?.value || '').toLowerCase();
  const condFilter = document.getElementById('prodConditionFilter')?.value || '';
  const typeFilter = document.getElementById('prodTypeFilter')?.value || '';
  const catFilter = document.getElementById('prodCategoryFilter')?.value || '';

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

  let prods = store.get('products');
  if (q) prods = prods.filter(p =>
    (p.code || '').toLowerCase().includes(q)
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

  document.getElementById('prodTbody').innerHTML = prods.length ?
    prods.map(p => {
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
          <button class="action-btn" style="color:var(--primary)" onclick="openProdSalesModal(${p.id})" title="View Sales">${VIEW_SVG}</button>
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
    document.getElementById('prodCode').value = p.code || '';
    document.getElementById('prodBarcode').value = p.barcode || '';

    document.getElementById('prodColor').value = p.color || '';
    document.getElementById('prodStorage').value = p.storage || '';
    document.getElementById('prodPurchase').value = p.purchase;
    document.getElementById('prodSale').value = p.sale;
    document.getElementById('prodStatus').value = p.status;
    document.getElementById('prodStock').value = p.stock !== undefined ? p.stock : 1;
    // Dynamic IMEI Rendering
    if (typeof renderImeiFields === 'function') {
      renderImeiFields(p.stock_units || []);
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
    ['prodName', 'prodCode', 'prodBarcode', 'prodColor', 'prodStorage', 'prodPurchase', 'prodSale', 'prodImage', 'prodBuyer'].forEach(id => document.getElementById(id).value = '');
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
    
    if (typeof renderImeiFields === 'function') renderImeiFields();
  }
  document.getElementById('prodModal').classList.remove('hidden');
  toggleProductFields();
}

function renderImeiFields(stockUnits = []) {
   const stock = parseInt(document.getElementById('prodStock').value) || 1;
   const container = document.getElementById('groupImeiInner');
   const soldContainer = document.getElementById('groupImeiSold');
   const soldInner = document.getElementById('groupImeiSoldInner');
   
   if (!container) return;
   
   // Separate units
   const availableUnits = stockUnits.filter(u => u.status === 'available');
   const soldUnits = stockUnits.filter(u => u.status === 'sold' || u.status === 'returned');
   
   // Keep existing input values to not wipe user typing if they just change quantity
   const existingInputs = Array.from(document.querySelectorAll('.unit-imei-input')).map(el => el.value);
   
   let html = '';
   for(let i=0; i<stock; i++) {
      let val = '';
      if (existingInputs.length > 0) {
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
  const groupImei = document.getElementById('groupImei');
  const groupStorage = document.getElementById('groupStorage');
  if (groupImei) groupImei.style.display = (type === 'accessory') ? 'none' : 'block';
  if (groupStorage) groupStorage.style.display = (type === 'accessory') ? 'none' : 'block';
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

  formData.append('color', document.getElementById('prodColor').value.trim());
  formData.append('storage', document.getElementById('prodStorage').value.trim());
  formData.append('purchase_price', document.getElementById('prodPurchase').value || 0);
  formData.append('sale_price', sale);
  formData.append('status', document.getElementById('prodStatus').value);
  formData.append('stock', document.getElementById('prodStock').value || 1);
  formData.append('delete_image', document.getElementById('prodImageDeleted').value);

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
  } catch (e) { toast(e.message || 'Error saving product', 'danger'); }
}

function deleteProduct(id) {
  confirmDelete('Delete this product?', async () => {
    try {
      await api('/shop/api/products/' + id, 'DELETE');
      toast('Product deleted', 'danger');
      await syncData();
    } catch (e) { toast('Error deleting', 'danger'); }
  });
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


