// ============================================================
// INVOICE MODAL
// ============================================================
async function showInvoiceModal(invoice) {
  let ledgerHtml = '';
  if (invoice.custId) {
    try {
      const res = await api(`/shop/api/customers/${invoice.custId}/ledger`);
      if (res && res.length > 0) {
        const last3 = res.slice(-3);
        ledgerHtml = `
              <hr class="r-divider"/>
              <div class="r-center" style="font-weight:bold; margin-bottom: 5px;">Recent Ledger</div>
              <table class="r-items" style="font-size: 10px;">
                  <thead>
                      <tr>
                          <th style="width:40%">Date</th>
                          <th style="width:30%;text-align:right">Debit</th>
                          <th style="width:30%;text-align:right">Credit</th>
                      </tr>
                  </thead>
                  <tbody>
                      ${last3.map(l => `<tr>
                          <td>${l.date}</td>
                          <td style="text-align:right">${l.debit > 0 ? parseFloat(l.debit).toFixed(2) : '-'}</td>
                          <td style="text-align:right">${l.credit > 0 ? parseFloat(l.credit).toFixed(2) : '-'}</td>
                      </tr>`).join('')}
                  </tbody>
              </table>
              <div style="font-weight:bold; font-size:10px; margin-top:3px;">
                  Current Balance: ${parseFloat(res[res.length - 1].balance).toFixed(2)}
              </div>
              `;
      }
    } catch (e) { console.error(e); }
  }

  document.getElementById('invoicePreview').innerHTML = buildInvoiceHTML(invoice, ledgerHtml);
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

function buildInvoiceHTML(inv, ledgerHtml = '') {
  // Generate barcode image data URL if JsBarcode is available and setting is true
  let barcodeImg = '';
  if (window.printSettings?.barcode_print !== false) {
    try {
      if (typeof JsBarcode !== 'undefined') {
        const canvas = document.createElement('canvas');
        JsBarcode(canvas, inv.id, {
          format: "CODE128",
          displayValue: true,
          fontSize: 16,
          height: 40,
          margin: 0
        });
        barcodeImg = canvas.toDataURL("image/png");
      }
    } catch (e) {
      console.error("Barcode generation failed", e);
    }
  }

  // Build item rows
  const itemRows = inv.items.map(it => `
    <tr>
      <td>
        <div class="r-item-name">${it.product ? it.product.name : (it.name || 'Unknown')}</div>
        ${it.imeis ? `<div style="font-size: 8px; color: #555; margin-top: 2px;">IMEI/SN: ${it.imeis}</div>` : ''}
      </td>
      <td style="text-align:center">${it.qty}</td>
      <td style="text-align:right">${parseFloat(it.price).toFixed(0)}</td>
      <td style="text-align:right">${parseFloat(it.sub || (it.qty * it.price)).toFixed(0)}</td>
    </tr>`).join('');

  const custPhone = inv.custId ? (getCustomer(inv.custId).phone || '') : '';

  const sName = (window.printSettings?.name || 'MobiPos').replace(/\n/g, '<br>');
  const sDesc = (window.printSettings?.desc || '').replace(/\n/g, '<br>');
  const sAddress = (window.printSettings?.address || '').replace(/\n/g, '<br>');
  const sHeading = (window.printSettings?.heading || 'INVOICE').replace(/\n/g, '<br>');
  const sFooter = (window.printSettings?.footer || '*** Thank You! ***').replace(/\n/g, '<br>');
  const sLogoSize = window.printSettings?.logoSize || 120;
  const sLogo = window.printSettings?.logo ? `<img src="${window.printSettings.logo}" style="max-width: ${sLogoSize}px; max-height: 200px; object-fit: contain; margin-bottom: 5px;">` : `<div class="r-logo">${sName.charAt(0)}</div>`;


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
      ${inv.payment_status ? `<tr><td style="font-weight:bold">Status:</td><td style="font-weight:bold">${inv.payment_status.toUpperCase()}</td></tr>` : ''}
    </table>

    ${ledgerHtml}

    ${inv.notes ? `<hr class="r-divider"/><div class="r-notes"><strong>Note:</strong> ${inv.notes}</div>` : ''}

    <hr class="r-divider"/>

    <!-- Footer -->
    <div class="r-footer">
      ${sFooter}<br>
      <br>
      ${barcodeImg ? `<img src="${barcodeImg}" style="max-width: 100%; height: 40px; margin-top: 5px;">` : (window.printSettings?.barcode_print !== false ? `<span class="r-barcode">${inv.id}</span>` : '')}
    </div>

    <div style="height:12px"></div>
  </div>`;
}

function printInvoice() {
  const content = document.getElementById('invoicePrintArea').outerHTML;
  const win = window.open('', '_blank', 'width=340,height=700');
  win.document.write(`<!DOCTYPE html><html><head><base href="${window.location.origin}"><title>Receipt â€” ${document.getElementById('invoicePrintArea')?.querySelector?.('.r-inv-num')?.textContent || 'Invoice'}</title><style>${THERMAL_CSS}</style></head><body>${content}</body></html>`);
  win.document.close();
  setTimeout(() => win.print(), 800);
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
window.salesPaymentStatus = '';

function setSalesStatusFilter(status, btn) {
  if (window.salesPaymentStatus === status) {
    window.salesPaymentStatus = '';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-outline');
  } else {
    window.salesPaymentStatus = status;
    document.querySelectorAll('.status-filter').forEach(b => {
      b.classList.remove('btn-primary');
      b.classList.add('btn-outline');
    });
    btn.classList.remove('btn-outline');
    btn.classList.add('btn-primary');
  }
  renderSales();
}

async function renderSales(page = 1) {
  const q = (document.getElementById('salesSearch')?.value || '').toLowerCase();
  const sDate = document.getElementById('salesStartDate')?.value || '';
  const eDate = document.getElementById('salesEndDate')?.value || '';
  const status = window.salesPaymentStatus || '';

  try {
    const tbody = document.getElementById('salesTbody');
    if (!tbody) return;
    tbody.innerHTML = '<tr><td colspan="11" class="text-center" style="padding:20px;">Loading sales...</td></tr>';

    const res = await api(`/shop/api/orders?page=${page}&search=${q}&start_date=${sDate}&end_date=${eDate}&payment_status=${status}`);
    const list = res.data;

    if (res.totals) {
      const elGrandTotal = document.getElementById('salesGrandTotal');
      const elTotalPaid = document.getElementById('salesTotalPaid');
      const elTotalDue = document.getElementById('salesTotalDue');
      if (elGrandTotal) elGrandTotal.textContent = fmtCur(res.totals.grand_total);
      if (elTotalPaid) elTotalPaid.textContent = fmtCur(res.totals.total_paid);
      if (elTotalDue) elTotalDue.textContent = fmtCur(res.totals.total_due);
    }

    window.currentSalesList = list;

    tbody.innerHTML = list.length ?
      list.map(i => `<tr>
          <td><span class="badge badge-primary" style="font-family:var(--mono)">${i.id}</span></td>
          <td>${i.buyer ? i.buyer.name : 'Walk-in'}</td>
          <td>${i.items.reduce((s, x) => s + parseInt(x.qty), 0)}</td>
          <td style="white-space:nowrap">${fmtCur(i.subtotal)}</td>
          <td style="white-space:nowrap">${fmtCur(i.discount)}</td>
          <td style="white-space:nowrap">${fmtCur(i.tax)}</td>
          <td style="white-space:nowrap;font-weight:700;color:var(--primary)">${fmtCur(i.total)}</td>
          <td style="white-space:nowrap">${fmtCur(Math.min(parseFloat(i.paid_amount) || 0, parseFloat(i.total) || 0))}</td>
          <td>
            <span class="badge ${i.payment_status === 'paid' ? 'badge-success' : (i.payment_status === 'refunded' ? 'badge-gray' : (i.payment_status === 'partial' ? 'badge-warning' : 'badge-danger'))}" style="text-transform:capitalize">${i.payment_status}</span>
          </td>
          <td><span class="badge badge-gray" style="text-transform:capitalize">${i.payment_method}</span></td>
          <td>${fmtDateTime(i.created_at)}</td>
          <td style="display:flex; gap:5px;">
            <button class="action-btn view" onclick="window.open('/shop/orders/${i.id}/invoice', 'InvoicePopup', 'width=400,height=600')" title="View Invoice">${VIEW_SVG}</button>
            <button class="action-btn edit" onclick="editOrderInPos(${i.id})" title="Edit Order">${EDIT_SVG}</button>
            <button class="action-btn view" onclick="refundOrder(${i.id})" title="Refund" style="color:var(--warning)">${REFUND_SVG}</button>
            <button class="action-btn delete" onclick="deleteOrder(${i.id})" title="Delete">${DEL_SVG}</button>
          </td>
        </tr>`).join('') :
      '<tr><td colspan="12" class="empty-cell">No sales found</td></tr>';

    renderSalesPagination(res);
  } catch (e) {
    console.error(e);
    document.getElementById('salesTbody').innerHTML = '<tr><td colspan="11" class="empty-cell text-danger">Failed to load sales</td></tr>';
  }
}

function renderSalesPagination(res) {
  let paginationHtml = '<div class="pagination-wrapper" style="display:flex; justify-content:center; gap:5px; margin-top:15px; padding-bottom:15px;">';

  if (res.prev_page_url) {
    paginationHtml += `<button class="btn btn-sm btn-outline" onclick="renderSales(${res.current_page - 1})">Prev</button>`;
  }

  for (let i = 1; i <= res.last_page; i++) {
    if (i === 1 || i === res.last_page || (i >= res.current_page - 2 && i <= res.current_page + 2)) {
      paginationHtml += `<button class="btn btn-sm ${i === res.current_page ? 'btn-primary' : 'btn-outline'}" onclick="renderSales(${i})">${i}</button>`;
    } else if (i === res.current_page - 3 || i === res.current_page + 3) {
      paginationHtml += `<span style="padding:4px 8px;">...</span>`;
    }
  }

  if (res.next_page_url) {
    paginationHtml += `<button class="btn btn-sm btn-outline" onclick="renderSales(${res.current_page + 1})">Next</button>`;
  }
  paginationHtml += '</div>';

  const wrap = document.querySelector('#page-sales .table-wrap');
  const existing = document.getElementById('salesPagination');
  if (existing) existing.remove();

  if (res.last_page > 1) {
    const pageDiv = document.createElement('div');
    pageDiv.id = 'salesPagination';
    pageDiv.innerHTML = paginationHtml;
    wrap.appendChild(pageDiv);
  }
}

function editOrderInPos(id) {
  localStorage.setItem('mp_edit_order_id', id);
  window.location.href = '/shop/pos';
}

function refundOrder(id) {
  confirmDelete('Are you sure you want to refund this order? This will restore stock.', async () => {
    try {
      await api(`/shop/api/orders/${id}/refund`, 'POST');
      toast('Order refunded successfully', 'success');
      renderSales();
    } catch (e) {
      toast('Error refunding order', 'danger');
      console.error(e);
    }
  });
}

function deleteOrder(id) {
  confirmDelete('Are you sure you want to delete this order? This will restore stock and remove the record permanently.', async () => {
    try {
      await api(`/shop/api/orders/${id}`, 'DELETE');
      toast('Order deleted successfully', 'success');
      renderSales();
    } catch (e) {
      toast('Error deleting order', 'danger');
      console.error(e);
    }
  });
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

