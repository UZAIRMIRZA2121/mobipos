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
      const res = await api('/shop/api/categories', 'POST', data);
      toast('Category added!', 'success');
      if (res && res.id && !document.getElementById('prodModal').classList.contains('hidden')) {
        const select = document.getElementById('prodCategory');
        if (select) {
          select.appendChild(new Option(data.name, res.id));
          select.value = res.id;
        }
      }
    }
    closeCatModal();
    await syncData();
  } catch (e) { toast('Error saving category', 'danger'); }
}

function deleteCategory(id) {
  confirmDelete('Delete this category?', async () => {
    try {
      await api('/shop/api/categories/' + id, 'DELETE');
      toast('Category deleted', 'danger');
      await syncData();
    } catch (e) { toast('Error deleting', 'danger'); }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  const cs = document.getElementById('catSearch');
  if (cs) cs.addEventListener('input', renderCategories);
});

