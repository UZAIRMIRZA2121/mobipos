// ============================================================
// CUSTOMERS
// ============================================================
function renderCustomers() {
  const q = (document.getElementById('custSearch')?.value || '').toLowerCase();
  let list = store.get('customers');
  if (q) list = list.filter(c => c.name.toLowerCase().includes(q) || (c.phone || '').includes(q));

  document.getElementById('custTbody').innerHTML = list.length ?
    list.map(c => {
      let bal = parseFloat(c.balance || 0);
      let balHtml = '';
      if (bal > 0) balHtml = `<div style="color:var(--danger);font-weight:600;line-height:1.2;font-size:0.85rem">Customer will pay<br><span style="font-size:1rem">${fmtCur(bal)}</span></div>`;
      else if (bal < 0) balHtml = `<div style="color:var(--success);font-weight:600;line-height:1.2;font-size:0.85rem">Shop will pay<br><span style="font-size:1rem">${fmtCur(Math.abs(bal))}</span></div>`;
      else balHtml = `<span style="color:var(--text-muted);font-weight:600">0.00</span>`;

      return `<tr>
      <td style="font-weight:500">${c.name}</td>
      <td>${c.phone || '-'}</td>
      <td>${c.cnic_number || '-'}</td>
      <td>${c.address || '-'}</td>
      <td>
        ${c.cnic_front ? `<a href="/storage/${c.cnic_front}" target="_blank" class="badge badge-success">Front</a>` : ''}
        ${c.cnic_back ? `<a href="/storage/${c.cnic_back}" target="_blank" class="badge badge-success">Back</a>` : ''}
      </td>
      <td>${balHtml}</td>
      <td>
        <button class="action-btn" title="Ledger" onclick="openLedgerModal(${c.id})"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg></button>
        <button class="action-btn edit" onclick="openCustModal(${c.id})">${EDIT_SVG}</button>
        <button class="action-btn del" onclick="deleteCustomer(${c.id})">${DEL_SVG}</button>
      </td>
    </tr>`;
    }).join('') :
    '<tr><td colspan="7" class="empty-cell">No customers found</td></tr>';
}

let currentLedgerCustomerId = null;

async function openLedgerModal(id) {
  currentLedgerCustomerId = id;
  const c = store.get('customers').find(x => x.id == id);
  if (!c) return;

  document.getElementById('ledgerModalTitle').textContent = `Ledger: ${c.name}`;
  document.getElementById('ledgerModal').classList.remove('hidden');

  // Set default date and time
  const now = new Date();
  now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
  document.getElementById('ledgerDate').value = now.toISOString().slice(0, 16);
  document.getElementById('ledgerType').value = 'Payment';
  document.getElementById('ledgerDebit').value = '';
  document.getElementById('ledgerCredit').value = '';
  document.getElementById('ledgerNote').value = '';

  await loadCustomerLedger(id);
}

async function loadCustomerLedger(id) {
  document.getElementById('ledgerTbody').innerHTML = '<tr><td colspan="6">Loading...</td></tr>';
  const statusEl = document.getElementById('ledgerModalStatus');
  if (statusEl) {
    statusEl.textContent = '';
    statusEl.className = '';
    statusEl.style.color = '';
    statusEl.style.backgroundColor = '';
  }

  try {
    const res = await api(`/shop/api/customers/${id}/ledger`);
    if (res.length === 0) {
      document.getElementById('ledgerTbody').innerHTML = '<tr><td colspan="8" class="empty-cell">No ledger entries found.</td></tr>';
      if (statusEl) {
        statusEl.textContent = 'Settled (No Balance)';
        statusEl.style.color = 'var(--text-muted)';
      }
      return;
    }

    document.getElementById('ledgerTbody').innerHTML = res.map(l => `
            <tr>
                <td>${fmtDateTime(l.date)}</td>
                <td>${l.payment_proof ? `<a href="/storage/${l.payment_proof}" target="_blank"><img src="/storage/${l.payment_proof}" style="width:40px; height:40px; object-fit:cover; border-radius:4px; border:1px solid #e5e7eb;" title="View Proof"></a>` : '-'}</td>
                <td>${l.type}</td>
                <td style="color:red">${l.debit > 0 ? parseFloat(l.debit).toFixed(2) : '-'}</td>
                <td style="color:green">${l.credit > 0 ? parseFloat(l.credit).toFixed(2) : '-'}</td>
                <td style="font-weight:bold">${parseFloat(l.balance).toFixed(2)}</td>
                <td>${l.note || '-'}</td>
                <td><button class="action-btn del" onclick="deleteLedgerEntry(${l.id})" title="Delete Entry"><svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button></td>
            </tr>
        `).join('');

    const finalBalance = parseFloat(res[res.length - 1].balance);
    if (statusEl) {
      if (finalBalance > 0) {
        statusEl.textContent = 'Customer will pay: ' + fmtCur(finalBalance);
        statusEl.style.color = 'var(--danger)';
        statusEl.style.backgroundColor = 'var(--danger-light, #ffebee)';
      } else if (finalBalance < 0) {
        statusEl.textContent = 'Shop will pay: ' + fmtCur(Math.abs(finalBalance));
        statusEl.style.color = 'var(--success)';
        statusEl.style.backgroundColor = 'var(--success-light, #e8f5e9)';
      } else {
        statusEl.textContent = 'Settled (0.00)';
        statusEl.style.color = 'var(--text-muted)';
        statusEl.style.backgroundColor = '#f3f4f6';
      }
    }
  } catch (e) {
    document.getElementById('ledgerTbody').innerHTML = '<tr><td colspan="6" style="color:red">Error loading ledger.</td></tr>';
    console.error(e);
  }
}

async function addLedgerEntry(e) {
  e.preventDefault();
  if (!currentLedgerCustomerId) return;

  const formData = new FormData();
  formData.append('date', document.getElementById('ledgerDate').value);
  formData.append('type', document.getElementById('ledgerType').value);
  formData.append('debit', document.getElementById('ledgerDebit').value || 0);
  formData.append('credit', document.getElementById('ledgerCredit').value || 0);
  formData.append('note', document.getElementById('ledgerNote').value);
  
  const proofFile = document.getElementById('ledgerProof').files[0];
  if (proofFile) {
    formData.append('payment_proof', proofFile);
  }

  try {
    const btn = document.getElementById('ledgerSubmitBtn');
    const oldText = btn.textContent;
    btn.textContent = 'Saving...';
    btn.disabled = true;

    const res = await api(`/shop/api/customers/${currentLedgerCustomerId}/ledger`, 'POST', formData);

    btn.textContent = oldText;
    btn.disabled = false;

    if (res.ledger) {
      toast('Entry added successfully');
      document.getElementById('ledgerDebit').value = '';
      document.getElementById('ledgerCredit').value = '';
      document.getElementById('ledgerNote').value = '';
      document.getElementById('ledgerProof').value = '';
      
      let customersList = store.get('customers');
      const cust = customersList.find(c => c.id == currentLedgerCustomerId);
      if (cust && res.ledger) {
          cust.balance = res.ledger.balance;
          store.set('customers', customersList);
          renderCustomers();
      }
      
      await loadCustomerLedger(currentLedgerCustomerId);
    } else if (res.message) {
      toast(res.message, 'danger');
    }
  } catch (err) {
    toast('Error saving entry', 'danger');
    document.getElementById('ledgerSubmitBtn').textContent = 'Add Entry';
    document.getElementById('ledgerSubmitBtn').disabled = false;
  }
}

async function deleteLedgerEntry(ledgerId) {
    confirmDelete('Are you sure you want to delete this ledger entry? This will update the customer balance automatically.', async () => {
        try {
            const url = `/shop/api/customers/${currentLedgerCustomerId}/ledger/${ledgerId}`;
            const res = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
            const data = await res.json();
            if (res.ok && data.message && data.message.includes('successfully')) {
                toast('Entry deleted successfully');
                let customersList = store.get('customers');
                const cust = customersList.find(c => c.id == currentLedgerCustomerId);
                if (cust) {
                    cust.balance = data.new_customer_balance;
                    store.set('customers', customersList);
                    renderCustomers();
                }
                await loadCustomerLedger(currentLedgerCustomerId);
            } else {
                toast(data.message || 'Error deleting entry', 'danger');
            }
        } catch (err) {
            toast('Error deleting entry', 'danger');
            console.error(err);
        }
    });
}

function closeLedgerModal() {
  document.getElementById('ledgerModal').classList.add('hidden');
  currentLedgerCustomerId = null;
}

function printCustomerLedger() {
    if (!currentLedgerCustomerId) return;
    const printUrl = '/shop/customers/' + currentLedgerCustomerId + '/print-ledger';
    
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
    
    // In some browsers, print dialog for iframe works better when we don't rely on onload,
    // but our views have window.print() on load anyway. So just loading the src is enough.
    printFrame.src = printUrl;
}

function createImagePreview(imgPath, onDelete) {
  const div = document.createElement('div');
  div.style.position = 'relative';
  div.style.display = 'inline-block';
  
  const imgEl = document.createElement('img');
  imgEl.src = '/storage/' + imgPath;
  imgEl.style.width = '80px';
  imgEl.style.height = '80px';
  imgEl.style.objectFit = 'cover';
  imgEl.style.borderRadius = '4px';
  imgEl.style.border = '1px solid #ccc';
  
  const btn = document.createElement('button');
  btn.innerHTML = '✕';
  btn.style.position = 'absolute';
  btn.style.top = '-5px';
  btn.style.right = '-5px';
  btn.style.background = 'red';
  btn.style.color = 'white';
  btn.style.border = 'none';
  btn.style.borderRadius = '50%';
  btn.style.width = '20px';
  btn.style.height = '20px';
  btn.style.cursor = 'pointer';
  btn.style.fontSize = '12px';
  btn.style.lineHeight = '20px';
  btn.style.padding = '0';
  btn.onclick = (e) => {
    e.preventDefault();
    onDelete();
  };
  
  div.appendChild(imgEl);
  div.appendChild(btn);
  return div;
}

function openCustModal(id) {
  const agreementsList = document.getElementById('custAgreementsList');
  agreementsList.innerHTML = '';
  const cnicFrontList = document.getElementById('custCnicFrontList');
  cnicFrontList.innerHTML = '';
  const cnicBackList = document.getElementById('custCnicBackList');
  cnicBackList.innerHTML = '';
  
  if (id) {
    const c = store.get('customers').find(x => x.id == id);
    if (!c) return;
    document.getElementById('custModalTitle').textContent = 'Edit Customer';
    document.getElementById('custId').value = c.id;
    document.getElementById('custName').value = c.name;
    document.getElementById('custPhone').value = c.phone || '';
    document.getElementById('custCnicNumber').value = c.cnic_number || '';
    document.getElementById('custAddress').value = c.address || '';
    
    if (c.cnic_front) {
      cnicFrontList.appendChild(createImagePreview(c.cnic_front, () => deleteCnicImage(c.id, 'front')));
    }
    
    if (c.cnic_back) {
      cnicBackList.appendChild(createImagePreview(c.cnic_back, () => deleteCnicImage(c.id, 'back')));
    }
    
    if (c.agreements_images && c.agreements_images.length > 0) {
      c.agreements_images.forEach((img, idx) => {
        agreementsList.appendChild(createImagePreview(img, () => deleteAgreementImage(c.id, idx)));
      });
    }
  } else {
    document.getElementById('custModalTitle').textContent = 'Add Customer';
    document.getElementById('custId').value = '';
    ['custName', 'custPhone', 'custCnicNumber', 'custAddress'].forEach(id => document.getElementById(id).value = '');
    ['custCnicFront', 'custCnicBack', 'custAgreementsImages'].forEach(id => document.getElementById(id).value = '');
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
  
  const agreementFiles = document.getElementById('custAgreementsImages').files;
  for (let i = 0; i < agreementFiles.length; i++) {
    formData.append('agreements_images[]', agreementFiles[i]);
  }

  try {
    if (editId) {
      await api('/shop/api/customers/' + editId, 'POST', formData);
      toast('Customer updated!', 'success');
    } else {
      const res = await api('/shop/api/customers', 'POST', formData);
      if (res && res.id) window.lastCreatedCustomerId = res.id;
      toast('Customer added!', 'success');
      
      if (res && res.id) {
        const optionText = `${name} (${phone || ''})`;
        
        const addAndSelect = (selectId, modalId) => {
          const select = document.getElementById(selectId);
          const modal = modalId ? document.getElementById(modalId) : null;
          if (select) {
            if (modal && modal.classList.contains('hidden')) return;
            if (!Array.from(select.options).some(opt => opt.value == res.id)) {
              select.appendChild(new Option(optionText, res.id));
            }
            select.value = res.id;
          }
        };

        addAndSelect('prodBuyer', 'prodModal');
        addAndSelect('newInstCustomer', 'newInstallmentModal');
        addAndSelect('posCustomer', null);
      }
    }
    closeCustModal();
    await syncData();
  } catch (e) { toast(e.message || 'Error saving customer', 'danger'); }
}

function deleteCustomer(id) {
  confirmDelete('Delete this customer?', async () => {
    try {
      await api('/shop/api/customers/' + id, 'DELETE');
      toast('Customer deleted', 'danger');
      await syncData();
    } catch (e) { toast('Error deleting', 'danger'); }
  });
}

function deleteAgreementImage(custId, idx) {
  confirmDelete('Are you sure you want to delete this image?', async () => {
    try {
      await api(`/shop/api/customers/${custId}/agreements-images/${idx}`, 'DELETE');
      toast('Image deleted', 'success');
      await syncData();
      openCustModal(custId); // refresh modal UI
    } catch(e) {
      toast('Error deleting image', 'danger');
    }
  });
}

function deleteCnicImage(custId, type) {
  confirmDelete('Are you sure you want to delete this CNIC image?', async () => {
    try {
      await api(`/shop/api/customers/${custId}/cnic-image/${type}`, 'DELETE');
      toast('CNIC image deleted', 'success');
      await syncData();
      openCustModal(custId); // refresh modal UI
    } catch(e) {
      toast('Error deleting image', 'danger');
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const cs = document.getElementById('custSearch');
  if (cs) cs.addEventListener('input', renderCustomers);
});

// ============================================================
// INSTALLMENT MODAL
// ============================================================

function closeInstallmentModal() {
  document.getElementById('installmentModal').classList.add('hidden');
  window.installmentData = null;
}

function calcInstallment() {
  const total = parseFloat(document.getElementById('instTotal').value || 0);
  let advance = parseFloat(document.getElementById('instAdvance').value || 0);
  
  if (advance > total) {
    advance = total;
    document.getElementById('instAdvance').value = advance;
  }
  
  const remaining = total - advance;
  document.getElementById('instRemaining').value = remaining.toFixed(2);
  
  let months = parseInt(document.getElementById('instMonths').value || 1);
  if (months < 1) {
    months = 1;
    document.getElementById('instMonths').value = months;
  }
  
  const monthly = (remaining / months).toFixed(2);
  document.getElementById('instMonthlyAmount').value = monthly;
}

function applyInstallmentPercentage() {
  const pct = parseFloat(document.getElementById('instPercentage').value || 0);
  const base = window.baseInstallmentTotal || 0;
  const newTotal = base + (base * (pct / 100));
  document.getElementById('instTotal').value = newTotal.toFixed(2);
  calcInstallment();
}

function confirmInstallment() {
  const total = parseFloat(document.getElementById('instTotal').value || 0);
  const advance = parseFloat(document.getElementById('instAdvance').value || 0);
  const remaining = parseFloat(document.getElementById('instRemaining').value || 0);
  const months = parseInt(document.getElementById('instMonths').value || 1);
  const monthly = parseFloat(document.getElementById('instMonthlyAmount').value || 0);
  const payment_day = parseInt(document.getElementById('instPaymentDay').value || 10);
  const interest_percentage = parseFloat(document.getElementById('instPercentage').value || 0);
  const actual_price = window.baseInstallmentTotal || 0;

  window.installmentData = {
    total, advance, remaining, months, monthly, payment_day, interest_percentage, actual_price
  };
  
  document.getElementById('installmentModal').classList.add('hidden');
  checkout(); // Resume checkout
}

