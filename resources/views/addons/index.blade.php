@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page" id="page-addons">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Add-ons Management</h3>
                <div class="header-actions" style="display: flex; gap: 10px;">
                    <input type="text" class="input input-sm" id="addonSearch" placeholder="Search add-ons..." oninput="filterAddons(this.value)"/>
                    <button class="btn btn-primary btn-sm" onclick="openAddonModal()">+ Add Add-on</button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success" style="margin: 12px 16px; padding: 10px 14px; background: #d1fae5; color: #065f46; border-radius: 6px; border-left: 4px solid #10b981;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-wrap">
                <table class="table" id="addonsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Add-on Name</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($addons as $i => $addon)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $addon->name }}</td>
                            <td>
                                @if($addon->category)
                                    <span style="display:inline-block; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; background:var(--primary-light, #ede9fe); color:var(--primary, #7c3aed);">
                                        {{ $addon->category->name }}
                                    </span>
                                @else
                                    <span style="color:var(--text-muted); font-size:12px;">All Categories</span>
                                @endif
                            </td>
                            <td style="display:flex; gap:6px; align-items:center;">
                                {{-- Edit Button --}}
                                <button class="action-btn edit"
                                    title="Edit"
                                    onclick="openEditAddonModal({{ $addon->id }}, '{{ addslashes($addon->name) }}', '{{ $addon->cat_id ?? '' }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>

                                {{-- Delete Button --}}
                                <form action="{{ route('shop.addons.destroy', $addon->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn del" title="Delete" onclick="return confirm('Delete this add-on?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                            <path d="M10 11v6M14 11v6"/>
                                            <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr id="emptyRow">
                            <td colspan="4" class="text-center empty-cell">No add-ons found. Click "+ Add Add-on" to create one.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Addon Modal -->
<div class="modal-overlay hidden" id="addonModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h4 class="modal-title">Add New Add-on</h4>
            <button class="modal-close" onclick="closeAddonModal()">&#10005;</button>
        </div>
        <form action="{{ route('shop.addons.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label class="label">Add-on Name <span style="color:red">*</span></label>
                    <input type="text" name="name" id="addonNameInput" class="input" placeholder="e.g. Extra Cheese, Extra Chicken" required autocomplete="off"/>
                </div>
                <div class="form-group">
                    <label class="label">Category <span style="font-weight:400; color:var(--text-muted);">(optional)</span></label>
                    <select name="cat_id" id="addonCatSelect" class="input">
                        <option value="">— All Categories —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">
                        Leave as "All Categories" to show this add-on for every product type.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddonModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Add-on</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Addon Modal -->
<div class="modal-overlay hidden" id="editAddonModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h4 class="modal-title">Edit Add-on</h4>
            <button class="modal-close" onclick="closeEditAddonModal()">&#10005;</button>
        </div>
        <form id="editAddonForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label class="label">Add-on Name <span style="color:red">*</span></label>
                    <input type="text" name="name" id="editAddonNameInput" class="input" placeholder="e.g. Extra Cheese, Extra Chicken" required autocomplete="off"/>
                </div>
                <div class="form-group">
                    <label class="label">Category <span style="font-weight:400; color:var(--text-muted);">(optional)</span></label>
                    <select name="cat_id" id="editAddonCatSelect" class="input">
                        <option value="">— All Categories —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditAddonModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Add-on</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // --- Add Modal ---
    function openAddonModal() {
        document.getElementById('addonModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('addonNameInput').focus(), 100);
    }
    function closeAddonModal() {
        document.getElementById('addonModal').classList.add('hidden');
        document.getElementById('addonNameInput').value = '';
        document.getElementById('addonCatSelect').value = '';
    }

    // --- Edit Modal ---
    function openEditAddonModal(id, name, catId) {
        const baseUrl = '{{ rtrim(route("shop.addons.update", "__ID__"), "") }}'.replace('__ID__', id);
        document.getElementById('editAddonForm').action = baseUrl;
        document.getElementById('editAddonNameInput').value = name;
        document.getElementById('editAddonCatSelect').value = catId || '';
        document.getElementById('editAddonModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('editAddonNameInput').focus(), 100);
    }
    function closeEditAddonModal() {
        document.getElementById('editAddonModal').classList.add('hidden');
    }

    // --- Search ---
    function filterAddons(query) {
        const rows = document.querySelectorAll('#addonsTable tbody tr');
        const q = query.toLowerCase();
        rows.forEach(row => {
            if (row.id === 'emptyRow') return;
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    // Close modals on overlay click
    document.getElementById('addonModal').addEventListener('click', function(e) { if (e.target === this) closeAddonModal(); });
    document.getElementById('editAddonModal').addEventListener('click', function(e) { if (e.target === this) closeEditAddonModal(); });
</script>
@endsection
