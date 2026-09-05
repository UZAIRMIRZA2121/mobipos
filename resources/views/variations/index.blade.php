@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page" id="page-variations">
        <div class="card">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Variation Management</h3>
                <div class="header-actions" style="display: flex; gap: 10px;">
                    <input type="text" class="input input-sm" id="varSearch" placeholder="Search variations..." oninput="filterVariations(this.value)"/>
                    <button class="btn btn-primary btn-sm" onclick="openVarModal()">+ Add Variation</button>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success" style="margin: 12px 16px; padding: 10px 14px; background: #d1fae5; color: #065f46; border-radius: 6px; border-left: 4px solid #10b981;">
                    {{ session('success') }}
                </div>
            @endif

            <div class="table-wrap">
                <table class="table" id="variationsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Variation Name</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($variations as $i => $variation)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $variation->name }}</td>
                            <td>
                                @if($variation->category)
                                    <span style="display:inline-block; padding:2px 10px; border-radius:20px; font-size:12px; font-weight:600; background:var(--primary-light, #ede9fe); color:var(--primary, #7c3aed);">
                                        {{ $variation->category->name }}
                                    </span>
                                @else
                                    <span style="color:var(--text-muted); font-size:12px;">All Categories</span>
                                @endif
                            </td>
                            <td style="display:flex; gap:6px; align-items:center;">
                                {{-- Edit Button --}}
                                <button class="action-btn edit"
                                    title="Edit"
                                    onclick="openEditVarModal({{ $variation->id }}, '{{ addslashes($variation->name) }}', '{{ $variation->cat_id ?? '' }}')">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                </button>

                                {{-- Delete Button --}}
                                <form action="{{ route('shop.variations.destroy', $variation->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn del" title="Delete" onclick="return confirm('Delete this variation?')">
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
                            <td colspan="4" class="text-center empty-cell">No variations found. Click "+ Add Variation" to create one.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add Variation Modal -->
<div class="modal-overlay hidden" id="varModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h4 class="modal-title">Add New Variation</h4>
            <button class="modal-close" onclick="closeVarModal()">&#10005;</button>
        </div>
        <form action="{{ route('shop.variations.store') }}" method="POST">
            @csrf
            <div class="modal-body" style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label class="label">Variation Name <span style="color:red">*</span></label>
                    <input type="text" name="name" id="varNameInput" class="input" placeholder="e.g. Small, Medium, Large" required autocomplete="off"/>
                </div>
                <div class="form-group">
                    <label class="label">Category <span style="font-weight:400; color:var(--text-muted);">(optional)</span></label>
                    <select name="cat_id" id="varCatSelect" class="input">
                        <option value="">— All Categories —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">
                        Assign a category so this size only shows for relevant products.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeVarModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Variation</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Variation Modal -->
<div class="modal-overlay hidden" id="editVarModal">
    <div class="modal" style="max-width:420px;">
        <div class="modal-header">
            <h4 class="modal-title">Edit Variation</h4>
            <button class="modal-close" onclick="closeEditVarModal()">&#10005;</button>
        </div>
        <form id="editVarForm" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body" style="display:flex; flex-direction:column; gap:16px;">
                <div class="form-group">
                    <label class="label">Variation Name <span style="color:red">*</span></label>
                    <input type="text" name="name" id="editVarNameInput" class="input" placeholder="e.g. Small, Medium, Large" required autocomplete="off"/>
                </div>
                <div class="form-group">
                    <label class="label">Category <span style="font-weight:400; color:var(--text-muted);">(optional)</span></label>
                    <select name="cat_id" id="editVarCatSelect" class="input">
                        <option value="">— All Categories —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditVarModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Variation</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // --- Add Modal ---
    function openVarModal() {
        document.getElementById('varModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('varNameInput').focus(), 100);
    }
    function closeVarModal() {
        document.getElementById('varModal').classList.add('hidden');
        document.getElementById('varNameInput').value = '';
        document.getElementById('varCatSelect').value = '';
    }

    // --- Edit Modal ---
    function openEditVarModal(id, name, catId) {
        const baseUrl = '{{ rtrim(route("shop.variations.update", "__ID__"), "") }}'.replace('__ID__', id);
        document.getElementById('editVarForm').action = baseUrl;
        document.getElementById('editVarNameInput').value = name;
        document.getElementById('editVarCatSelect').value = catId || '';
        document.getElementById('editVarModal').classList.remove('hidden');
        setTimeout(() => document.getElementById('editVarNameInput').focus(), 100);
    }
    function closeEditVarModal() {
        document.getElementById('editVarModal').classList.add('hidden');
    }

    // --- Search ---
    function filterVariations(query) {
        const rows = document.querySelectorAll('#variationsTable tbody tr');
        const q = query.toLowerCase();
        rows.forEach(row => {
            if (row.id === 'emptyRow') return;
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    }

    // Close modals on overlay click
    document.getElementById('varModal').addEventListener('click', function(e) { if (e.target === this) closeVarModal(); });
    document.getElementById('editVarModal').addEventListener('click', function(e) { if (e.target === this) closeEditVarModal(); });
</script>
@endsection
