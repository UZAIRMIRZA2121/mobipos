<!-- Product Modal -->
<div class="modal-overlay hidden" id="prodModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="prodModalTitle">Add Product</h3>
      <button class="modal-close" onclick="closeProductModal()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="prodId"/>
      <div class="form-grid">
        <div class="form-group"><label>Product Name *</label><input type="text" id="prodName" class="input" placeholder="e.g. iPhone 14 Pro Max"/></div>
        <div class="form-group"><label>Type *</label><select id="prodType" class="input"><option value="mobile">Mobile</option><option value="tablet">Tablet</option><option value="laptop">Laptop</option><option value="accessory">Accessory</option></select></div>
        <div class="form-group"><label>Condition *</label><select id="prodCondition" class="input"><option value="new">New</option><option value="used">Used</option><option value="refurbished">Refurbished</option></select></div>
        <div class="form-group"><label>IMEI / Serial Number</label><input type="text" id="prodImei" class="input" placeholder="Unique identifier"/></div>
        <div class="form-group"><label>Color</label><input type="text" id="prodColor" class="input" placeholder="e.g. Space Black"/></div>
        <div class="form-group"><label>Storage</label><input type="text" id="prodStorage" class="input" placeholder="e.g. 256GB"/></div>
        <div class="form-group"><label>Purchase Price (PKR)</label><input type="number" id="prodPurchase" class="input" min="0" step="0.01"/></div>
        <div class="form-group"><label>Sale Price (PKR) *</label><input type="number" id="prodSale" class="input" min="0" step="0.01"/></div>
        <div class="form-group"><label>Status *</label><select id="prodStatus" class="input"><option value="in_stock">In Stock</option><option value="sold">Sold</option><option value="in_repair">In Repair</option></select></div>
        <div class="form-group"><label>Category</label><select id="prodCategory" class="input"><option value="">Select category</option></select></div>
        <div class="form-group"><label>Sourced From (Buyer)</label><select id="prodBuyer" class="input"><option value="">Select Customer (Optional)</option></select></div>
        <div class="form-group"><label>Stock Quantity</label><input type="number" id="prodStock" class="input" min="0" value="1"/></div>
        <div class="form-group"><label>Product Image</label><input type="file" id="prodImage" class="input" accept="image/*"/></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeProductModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveProduct()">Save Product</button>
    </div>
  </div>
</div>

<!-- Customer Modal -->
<div class="modal-overlay hidden" id="custModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="custModalTitle">Add Customer</h3>
      <button class="modal-close" onclick="closeCustModal()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="custId"/>
      <div class="form-grid">
        <div class="form-group"><label>Customer Name *</label><input type="text" id="custName" class="input"/></div>
        <div class="form-group"><label>Phone *</label><input type="text" id="custPhone" class="input"/></div>
        <div class="form-group"><label>CNIC Number</label><input type="text" id="custCnicNumber" class="input" placeholder="e.g. 12345-1234567-1"/></div><div class="form-group"><label>CNIC Front</label><input type="file" id="custCnicFront" class="input" accept="image/*"/></div><div class="form-group"><label>CNIC Back</label><input type="file" id="custCnicBack" class="input" accept="image/*"/></div><div class="form-group form-full"><label>Address</label><textarea id="custAddress" class="input" rows="2"></textarea></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeCustModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveCustomer()">Save Customer</button>
    </div>
  </div>
</div>

<!-- Invoice Preview Modal -->
<div class="modal-overlay hidden" id="invoiceModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>Invoice Preview</h3>
      <button class="modal-close" onclick="closeInvoiceModal()">✕</button>
    </div>
    <div class="modal-body" id="invoicePreview" style="background:#f0f0f0;padding:24px"></div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeInvoiceModal()">Close</button>
      <button class="btn btn-primary" onclick="printInvoice()">Print Invoice</button>
    </div>
  </div>
</div>

<!-- Confirm Modal -->
<div class="modal-overlay hidden" id="confirmModal">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3>Confirm Delete</h3>
      <button class="modal-close" onclick="closeConfirmModal()">✕</button>
    </div>
    <div class="modal-body">
      <p id="confirmMsg">Are you sure you want to delete this item? This action cannot be undone.</p>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeConfirmModal()">Cancel</button>
      <button class="btn btn-danger" id="confirmOkBtn">Delete</button>
    </div>
  </div>
</div>
<!-- Category Modal -->
<div class="modal-overlay hidden" id="catModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="catModalTitle">Add Category</h3>
      <button class="modal-close" onclick="closeCatModal()">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="catId"/>
      <div class="form-grid">
        <div class="form-group"><label>Category Name *</label><input type="text" id="catName" class="input"/></div>
        <div class="form-group"><label>Color Code</label><input type="color" id="catColor" class="input" style="height: 40px; padding: 2px;" value="#3b82f6"/></div>
        <div class="form-group form-full"><label>Description</label><textarea id="catDesc" class="input" rows="2"></textarea></div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeCatModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveCategory()">Save Category</button>
    </div>
  </div>
</div>




