@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1 class="page-title">Packages</h1>
            <p class="page-subtitle">Manage system subscription packages</p>
        </div>
        <button class="btn btn-primary" onclick="openPackageModal()">+ Add Package</button>
    </div>

    <div class="card">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Commission</th>
                        <th>Billing</th>
                        <th>Trial</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($packages as $package)
                    <tr>
                        <td style="font-weight:500;">{{ $package->name }}</td>
                        <td>PKR {{ number_format($package->price, 2) }}</td>
                        <td>PKR {{ number_format($package->commission, 2) }}</td>
                        <td style="text-transform: capitalize;">{{ str_replace('_', ' ', $package->billing_type) }}</td>
                        <td>{{ $package->active_days }} days</td>
                        <td>
                            @if($package->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-danger">Inactive</span>
                            @endif
                        </td>
                        <td>
                            <button class="action-btn edit" onclick="openPackageModal({{ $package->toJson() }})" title="Edit">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this package?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-btn del" title="Delete">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                    @if($packages->isEmpty())
                    <tr>
                        <td colspan="6" class="empty-cell" style="text-align: center; padding: 20px;">No packages found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Add/Edit Package Modal -->
<div class="modal-overlay hidden" id="packageModalOverlay">
  <div class="modal" style="max-width: 600px;">
    <div class="modal-header">
      <h3 id="packageModalTitle">Add Package</h3>
      <button class="modal-close" onclick="closePackageModal()">×</button>
    </div>
    <div class="modal-body">
      <form id="packageForm" method="POST" action="{{ route('admin.packages.store') }}">
        @csrf
        <input type="hidden" name="_method" id="packageMethod" value="POST">
        
        <div class="form-grid">
            <div class="form-group form-full">
                <label>Package Name *</label>
                <input type="text" name="name" id="packageName" class="input" required>
            </div>
            
            <div class="form-group">
                <label>Price *</label>
                <input type="number" step="0.01" name="price" id="packagePrice" class="input" required>
            </div>
            
            <div class="form-group">
                <label>Commission Amount</label>
                <input type="number" step="0.01" name="commission" id="packageCommission" class="input" value="0">
            </div>
            
            <div class="form-group">
                <label>Billing Type *</label>
                <select name="billing_type" id="packageBillingType" class="input" required>
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                    <option value="one_time">One Time</option>
                </select>
            </div>

            <div class="form-group">
                <label>Active Days</label>
                <input type="number" name="active_days" id="packageTrialDays" class="input" value="0" required>
            </div>

            <div class="form-group">
                <label>Status</label>
                <select name="status" id="packageStatus" class="input" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="form-group form-full">
                <label>Short Description</label>
                <input type="text" name="short_description" id="packageShortDesc" class="input">
            </div>

            <div class="form-group form-full">
                <label>Features & Toggles (Check if Yes)</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 5px;">
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: 400;">
                        <input type="hidden" name="is_cloud" value="0">
                        <input type="checkbox" name="is_cloud" id="packageIsCloud" value="1"> Is Cloud
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: 400;">
                        <input type="hidden" name="is_offline" value="0">
                        <input type="checkbox" name="is_offline" id="packageIsOffline" value="1"> Is Offline
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: 400;">
                        <input type="hidden" name="lifetime_license" value="0">
                        <input type="checkbox" name="lifetime_license" id="packageLifetime" value="1"> Lifetime License
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: 400;">
                        <input type="hidden" name="hosting_included" value="0">
                        <input type="checkbox" name="hosting_included" id="packageHosting" value="1"> Hosting Included
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: 400;">
                        <input type="hidden" name="support_included" value="0">
                        <input type="checkbox" name="support_included" id="packageSupport" value="1" checked> Support Included
                    </label>
                    <label style="display: flex; align-items: center; gap: 5px; font-weight: 400;">
                        <input type="hidden" name="free_updates" value="0">
                        <input type="checkbox" name="free_updates" id="packageUpdates" value="1" checked> Free Updates
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label>Sort Order</label>
                <input type="number" name="sort_order" id="packageSortOrder" class="input" value="0" required>
            </div>
        </div>

        <div class="modal-footer" style="margin-top: 20px;">
            <button type="button" class="btn btn-ghost" onclick="closePackageModal()">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Package</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openPackageModal(package = null) {
    const modal = document.getElementById('packageModalOverlay');
    const form = document.getElementById('packageForm');
    const title = document.getElementById('packageModalTitle');
    const methodInput = document.getElementById('packageMethod');

    if (package) {
        title.textContent = 'Edit Package';
        form.action = `/admin/packages/${package.id}`;
        methodInput.value = 'PUT';
        
        document.getElementById('packageName').value = package.name;
        document.getElementById('packagePrice').value = package.price;
        document.getElementById('packageCommission').value = package.commission || 0;
        document.getElementById('packageBillingType').value = package.billing_type;
        document.getElementById('packageTrialDays').value = package.active_days;
        document.getElementById('packageStatus').value = package.status;
        document.getElementById('packageShortDesc').value = package.short_description || '';
        document.getElementById('packageSortOrder').value = package.sort_order;
        
        document.getElementById('packageIsCloud').checked = package.is_cloud;
        document.getElementById('packageIsOffline').checked = package.is_offline;
        document.getElementById('packageLifetime').checked = package.lifetime_license;
        document.getElementById('packageHosting').checked = package.hosting_included;
        document.getElementById('packageSupport').checked = package.support_included;
        document.getElementById('packageUpdates').checked = package.free_updates;
    } else {
        title.textContent = 'Add Package';
        form.action = '{{ route('admin.packages.store') }}';
        methodInput.value = 'POST';
        form.reset();
        
        // Default checks
        document.getElementById('packageSupport').checked = true;
        document.getElementById('packageUpdates').checked = true;
    }
    
    modal.classList.remove('hidden');
}

function closePackageModal() {
    document.getElementById('packageModalOverlay').classList.add('hidden');
}
</script>
@endsection
