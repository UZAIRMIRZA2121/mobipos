<!-- Purchase Order Modal -->
<div class="modal-overlay hidden" id="poModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="poModalTitle">Add to Purchase</h3>
      <button class="modal-close" onclick="closePOModal()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="poId"/>
      <div class="form-grid">
        <div class="form-group" style="grid-column: span 2;">
          <label>Supplier Name</label>
          <input type="text" id="poSupplier" class="input" placeholder="e.g. Ali Traders"/>
        </div>
        <div class="form-group" style="grid-column: span 2;">
          <label>Select Product to Add</label>
          <div style="display:flex; gap:10px;">
            <select id="poProductSelect" class="input" style="flex:1;">
              <option value="">Select a product...</option>
            </select>
            <button class="btn btn-secondary" onclick="addProdToPO()">Add</button>
          </div>
        </div>
      </div>
      
      <div class="table-wrap" style="margin-top:20px;">
        <table class="table">
          <thead>
            <tr>
              <th>Product</th>
              <th width="100">Qty</th>
              <th width="150">Price/Unit</th>
              <th width="150">Amount</th>
              <th width="60" class="text-right">✕</th>
            </tr>
          </thead>
          <tbody id="poItemsTbody">
            <!-- Items added here -->
          </tbody>
        </table>
      </div>

      <div class="form-grid" style="margin-top:20px;">
        <div class="form-group">
          <label>Total Amount (PKR)</label>
          <input type="number" id="poTotalAmount" class="input" readonly value="0"/>
        </div>
        <div class="form-group">
          <label>Paid Amount (PKR) *</label>
          <input type="number" id="poPaidAmount" class="input" min="0" value="0" oninput="calcPOTotal()"/>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn" onclick="closePOModal()">Cancel</button>
      <button class="btn btn-primary" onclick="savePO()">Save Purchase Order</button>
    </div>
  </div>
</div>

<!-- View Purchase Order Modal -->
<div class="modal-overlay hidden" id="viewPoModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>Purchase Order Details</h3>
      <button class="modal-close" onclick="closeViewPOModal()">✕</button>
    </div>
    <div class="modal-body">
      <div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div>
          <p class="text-sm text-gray">Supplier Name</p>
          <p id="viewPoSupplier" style="font-weight: 500; font-size: 16px;"></p>
        </div>
        <div>
          <p class="text-sm text-gray">Date</p>
          <p id="viewPoDate" style="font-weight: 500; font-size: 16px;"></p>
        </div>
      </div>
      
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Product</th>
              <th width="100">Qty</th>
              <th width="150">Price/Unit</th>
              <th width="150">Amount</th>
            </tr>
          </thead>
          <tbody id="viewPoItemsTbody">
            <!-- Items added here -->
          </tbody>
        </table>
      </div>

      <div class="grid" style="grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border-color);">
        <div>
          <p class="text-sm text-gray">Total Amount</p>
          <p id="viewPoTotalAmount" style="font-weight: 600; font-size: 18px;"></p>
        </div>
        <div>
          <p class="text-sm text-gray">Paid Amount</p>
          <p id="viewPoPaidAmount" style="font-weight: 600; font-size: 18px; color: var(--success);"></p>
        </div>
        <div>
          <p class="text-sm text-gray">Remaining Amount</p>
          <p id="viewPoRemainingAmount" style="font-weight: 600; font-size: 18px; color: var(--danger);"></p>
        </div>
      </div>
    </div>
    <div class="modal-footer" style="justify-content: space-between;">
      <button class="btn btn-secondary" onclick="printPO()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:8px; vertical-align:middle;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Print
      </button>
      <button class="btn" onclick="closeViewPOModal()">Close</button>
    </div>
  </div>
</div>

<!-- Expense Modal -->
<div class="modal-overlay hidden" id="expenseModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="expenseModalTitle">Add Expense</h3>
      <button class="modal-close" onclick="closeExpenseModal()">✕</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="expenseId"/>
      <div class="form-group"><label>Title *</label><input type="text" id="expenseTitle" class="input" placeholder="e.g. Electricity Bill" required/></div>
      <div class="form-group"><label>Amount (PKR) *</label><input type="number" id="expenseAmount" class="input" min="0" step="0.01" required/></div>
      <div class="form-group"><label>Date *</label><input type="date" id="expenseDate" class="input" required/></div>
      <div class="form-group"><label>Description</label><textarea id="expenseDescription" class="input" rows="3" placeholder="Optional details..."></textarea></div>
    </div>
    <div class="modal-footer">
      <button class="btn" onclick="closeExpenseModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveExpense()">Save Expense</button>
    </div>
  </div>
</div>

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
        <div class="form-group"><label>Code</label><input type="text" id="prodCode" class="input" placeholder="Product Code (optional)"/></div>
        <div class="form-group"><label>Barcode</label><input type="text" id="prodBarcode" class="input" placeholder="Barcode (optional)"/></div>
        <div class="form-group"><label>Stock Quantity</label><input type="number" id="prodStock" class="input" min="0" value="1" oninput="if(typeof renderImeiFields === 'function') renderImeiFields()"/></div>
        <div class="form-group form-full" id="groupImei">
          <label>IMEI / Serial Numbers (Available)</label>
          <div id="groupImeiInner" style="max-height: 250px; overflow-y: auto; padding: 12px; border: 1px solid var(--border); border-radius: 6px; background: #fafafa; display: flex; flex-direction: column; gap: 10px;">
             <!-- populated dynamically by JS -->
          </div>
          <small style="color:var(--text-muted); font-size:11px; margin-top: 4px; display: block;">* For Dual-SIM devices, enter both IMEIs separated by a comma (e.g. 3589..., 3589...). Each box represents 1 physical Stock Unit.</small>
          
          <!-- Sold IMEIs Container -->
          <div id="groupImeiSold" style="display: none; margin-top: 15px;">
            <label style="color: var(--danger); font-size: 12px; margin-bottom: 5px; display: block;">Already Sold IMEIs</label>
            <div id="groupImeiSoldInner" style="max-height: 150px; overflow-y: auto; padding: 10px; border: 1px dashed var(--danger); border-radius: 6px; background: #fff5f5; display: flex; flex-direction: column; gap: 6px;">
               <!-- populated dynamically by JS -->
            </div>
          </div>
        </div>
        <div class="form-group"><label>Color</label><input type="text" id="prodColor" class="input" placeholder="e.g. Space Black"/></div>
        <div class="form-group" id="groupStorage"><label>Storage</label><input type="text" id="prodStorage" class="input" placeholder="e.g. 256GB"/></div>
        <div class="form-group"><label>Purchase Price (PKR)</label><input type="number" id="prodPurchase" class="input" min="0" step="0.01"/></div>
        <div class="form-group"><label>Sale Price (PKR) *</label><input type="number" id="prodSale" class="input" min="0" step="0.01"/></div>
        <div class="form-group"><label>Status *</label><select id="prodStatus" class="input"><option value="in_stock">In Stock</option><option value="sold">Sold</option><option value="in_repair">In Repair</option></select></div>
        <div class="form-group">
          <label>Category</label>
          <div style="display: flex; gap: 8px;">
            <select id="prodCategory" class="input" style="flex: 1;"><option value="">Select category</option></select>
            <button type="button" class="btn btn-outline" style="padding: 0 12px; font-size: 18px;" onclick="openCatModal()" title="Add Category">+</button>
          </div>
        </div>
        <div class="form-group">
          <label>Sourced From (Buyer)</label>
          <div style="display: flex; gap: 8px;">
            <select id="prodBuyer" class="input" style="flex: 1;"><option value="">Select Customer (Optional)</option></select>
            <button type="button" class="btn btn-outline" style="padding: 0 12px; font-size: 18px;" onclick="openCustModal()" title="Add Customer">+</button>
          </div>
        </div>
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
        <div class="form-group"><label>CNIC Number</label><input type="text" id="custCnicNumber" class="input" placeholder="e.g. 12345-1234567-1"/></div>
        <div class="form-group">
        </div>
        <div class="form-group">
          <label>CNIC Front</label>
          <input type="file" id="custCnicFront" class="input" accept="image/*"/>
          <div id="custCnicFrontList" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;"></div>
        </div>
        <div class="form-group">
          <label>CNIC Back</label>
          <input type="file" id="custCnicBack" class="input" accept="image/*"/>
          <div id="custCnicBackList" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;"></div>
        </div>
        <div class="form-group form-full">
          <label>Agreements Images</label>
          <input type="file" id="custAgreementsImages" class="input" accept="image/*" multiple/>
          <div id="custAgreementsList" style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;"></div>
        </div>
        <div class="form-group form-full"><label>Address</label><textarea id="custAddress" class="input" rows="2"></textarea></div>
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
<div class="modal-overlay hidden" id="confirmModal" style="z-index: 99999;">
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

<!-- Ledger Modal -->
<div class="modal-overlay hidden" id="ledgerModal">
  <div class="modal modal-lg">
    <div class="modal-header" style="display: flex; justify-content: space-between; align-items: center;">
      <div style="display: flex; align-items: center; gap: 15px;">
          <h3 id="ledgerModalTitle" style="margin: 0;">Customer Ledger</h3>
          <span id="ledgerModalStatus" style="font-size: 14px; font-weight: 600; padding: 4px 10px; border-radius: 4px;"></span>
      </div>
      <div style="display: flex; align-items: center; gap: 10px;">
        <button class="btn btn-secondary btn-sm" onclick="printCustomerLedger()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right:5px; vertical-align:middle;"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
          Print Ledger
        </button>
        <button class="modal-close" onclick="closeLedgerModal()" style="position: static;">×</button>
      </div>
    </div>
    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
      
      <!-- Add Entry Form -->
      <style>
        .ledger-form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; align-items: end; }
        @media (max-width: 768px) { .ledger-form-grid { grid-template-columns: 1fr; } }
      </style>
      <div class="card" style="margin-bottom: 20px; padding: 15px; background: #f9fafb; border: 1px solid #e5e7eb;">
        <h4 style="margin-bottom: 10px; font-size: 14px; font-weight: 600;">Add New Entry</h4>
        <form onsubmit="addLedgerEntry(event)" class="ledger-form-grid">
            <div class="form-group mb-0">
                <label>Date & Time</label>
                <input type="datetime-local" id="ledgerDate" class="input input-sm" required>
            </div>
            <div class="form-group mb-0">
                <label>Type</label>
                <select id="ledgerType" class="input input-sm" required>
                    <option value="Payment">Payment</option>
                    <option value="Sale">Sale</option>
                    <option value="Refund">Refund</option>
                    <option value="Opening Balance">Opening Balance</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group mb-0">
                <label>Debit (They Owe)</label>
                <input type="number" id="ledgerDebit" class="input input-sm" step="0.01" min="0">
            </div>
            <div class="form-group mb-0">
                <label>Credit (They Paid)</label>
                <input type="number" id="ledgerCredit" class="input input-sm" step="0.01" min="0">
            </div>
            <div class="form-group mb-0">
                <label>Note</label>
                <input type="text" id="ledgerNote" class="input input-sm" placeholder="Optional notes">
            </div>
            <div class="form-group mb-0">
                <label>Payment Proof</label>
                <input type="file" id="ledgerProof" class="input input-sm" accept="image/*">
            </div>
            <div class="form-group mb-0" style="grid-column: 1 / -1; display: flex; justify-content: flex-end;">
                <button type="submit" id="ledgerSubmitBtn" class="btn btn-primary btn-sm" style="width: 200px;">Add Entry</button>
            </div>
        </form>
      </div>

      <!-- Ledger Table -->
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Proof</th>
              <th>Type</th>
              <th>Debit</th>
              <th>Credit</th>
              <th>Balance</th>
              <th>Note</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody id="ledgerTbody">
            <!-- Entries loaded via JS -->
          </tbody>
        </table>
      </div>

    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeLedgerModal()">Close</button>
    </div>
  </div>
</div>

<!-- Product Sales Modal -->
<div class="modal-overlay hidden" id="prodSalesModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3 id="prodSalesModalTitle">Product Sales History</h3>
      <button class="modal-close" onclick="closeProdSalesModal()">×</button>
    </div>
    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Order #</th>
              <th>Customer</th>
              <th>Qty</th>
              <th class="text-right">Price</th>
              <th class="text-right">Total</th>
            </tr>
          </thead>
          <tbody id="prodSalesTbody">
            <!-- Entries loaded via JS -->
          </tbody>
          <tfoot id="prodSalesTfoot" style="font-weight: bold; background: #f9fafb;">
            <!-- Totals loaded via JS -->
          </tfoot>
        </table>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeProdSalesModal()">Close</button>
    </div>
  </div>
</div>

<!-- Installment Setup Modal -->
<div class="modal-overlay hidden" id="installmentModal">
  <div class="modal modal-md">
    <div class="modal-header">
      <h3>Installment Setup</h3>
      <button class="modal-close" onclick="closeInstallmentModal()">×</button>
    </div>
    <div class="modal-body" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Total Amount</label>
        <input type="number" id="instTotal" class="input" readonly>
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Advance Payment (Down Payment)</label>
        <input type="number" id="instAdvance" class="input" oninput="calcInstallment()">
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Remaining Amount</label>
        <input type="number" id="instRemaining" class="input" readonly>
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Number of Months</label>
        <input type="number" id="instMonths" class="input" value="1" min="1" oninput="calcInstallment()">
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Monthly Installment</label>
        <input type="number" id="instMonthlyAmount" class="input" readonly>
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Payment Day (1-30)</label>
        <input type="number" id="instPaymentDay" class="input" value="10" min="1" max="30" required>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeInstallmentModal()">Cancel</button>
      <button class="btn btn-primary" onclick="confirmInstallment()">Confirm & Checkout</button>
    </div>
  </div>
</div>
