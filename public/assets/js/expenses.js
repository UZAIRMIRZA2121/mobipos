// ============================================================
// EXPENSES
// ============================================================
async function renderExpenses() {
  try {
    const sDate = document.getElementById('expStartDate')?.value || '';
    const eDate = document.getElementById('expEndDate')?.value || '';
    const expenses = await api(`/shop/api/expenses?start_date=${sDate}&end_date=${eDate}`);
    const tbody = document.getElementById('expensesTbody');
    if (!tbody) return;

    if (!expenses || !expenses.length) {
      tbody.innerHTML = '<tr><td colspan="6" class="empty-cell">No expenses found</td></tr>';
      return;
    }

    tbody.innerHTML = expenses.map(e => `
          <tr>
              <td>${fmtDateTime(e.expense_date)}</td>
              <td style="font-weight: 500">${e.title}</td>
              <td style="color: var(--text-muted); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${e.description || ''}">${e.description || '-'}</td>
              <td style="font-weight: 600">${fmtCur(e.amount)}</td>
              <td>${e.user ? e.user.name : '-'}</td>
              <td class="text-right">
                  <button class="btn btn-ghost" onclick='editExpense(${JSON.stringify(e).replace(/'/g, "&apos;")})'>
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                  </button>
                  <button class="btn btn-ghost" style="color:var(--danger)" onclick="deleteExpense(${e.id})">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                  </button>
              </td>
          </tr>
      `).join('');
  } catch (err) {
    console.error(err);
    toast('Failed to load expenses', 'danger');
  }
}

function openExpenseModal() {
  document.getElementById('expenseId').value = '';
  document.getElementById('expenseTitle').value = '';
  document.getElementById('expenseAmount').value = '';
  document.getElementById('expenseDate').value = new Date().toISOString().split('T')[0];
  document.getElementById('expenseDescription').value = '';

  document.getElementById('expenseModalTitle').textContent = 'Add Expense';
  document.getElementById('expenseModal').classList.remove('hidden');
}

function editExpense(exp) {
  document.getElementById('expenseId').value = exp.id;
  document.getElementById('expenseTitle').value = exp.title;
  document.getElementById('expenseAmount').value = exp.amount;
  document.getElementById('expenseDate').value = exp.expense_date;
  document.getElementById('expenseDescription').value = exp.description || '';

  document.getElementById('expenseModalTitle').textContent = 'Edit Expense';
  document.getElementById('expenseModal').classList.remove('hidden');
}

function closeExpenseModal() {
  document.getElementById('expenseModal').classList.add('hidden');
}

async function saveExpense() {
  const id = document.getElementById('expenseId').value;
  const data = {
    title: document.getElementById('expenseTitle').value,
    amount: parseFloat(document.getElementById('expenseAmount').value) || 0,
    expense_date: document.getElementById('expenseDate').value,
    description: document.getElementById('expenseDescription').value
  };

  if (!data.title || !data.amount || !data.expense_date) return toast('Please fill required fields', 'warning');

  try {
    const url = id ? `/shop/api/expenses/${id}` : '/shop/api/expenses';
    const method = id ? 'PUT' : 'POST';

    const res = await api(url, method, data);

    toast(res.message);
    closeExpenseModal();
    renderExpenses();
  } catch (err) {
    toast(err.message, 'danger');
  }
}

async function deleteExpense(id) {
  confirmDelete('Delete this expense?', async () => {
    try {
      const res = await api(`/shop/api/expenses/${id}`, 'DELETE');
      toast('Expense deleted');
      renderExpenses();
    } catch (err) {
      toast(err.message, 'danger');
    }
  });
}

