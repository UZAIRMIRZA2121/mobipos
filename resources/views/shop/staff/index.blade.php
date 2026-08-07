@extends('layouts.app')

@section('content')
<main class="page-content">

    @if ($errors->any())
        <div style="padding:12px; margin: 20px; border-radius:8px; background:var(--danger-light); color:var(--danger); font-size:14px;">
            <ul style="margin:0; padding-left:20px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="page" id="page-staff">
      <div class="card">
        <div class="card-header">
          <h3>Staff Management</h3>
          <div class="header-actions">
            <button class="btn btn-primary btn-sm" onclick="document.getElementById('staffModal').classList.remove('hidden')">+ Add Staff</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role / Type</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($staffs as $staff)
              <tr>
                  <td style="font-weight: 500;">{{ $staff->name }}</td>
                  <td style="color: var(--text-muted);">{{ $staff->email }}</td>
                  <td>
                      <span style="background: var(--primary-light); color: var(--primary); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 500;">
                          {{ $staff->staff_type }}
                      </span>
                  </td>
                  <td>
                      <div style="display:flex; flex-direction:column; gap:4px;">
                          @if($staff->status === 'active')
                              <span class="badge" style="background: var(--success-light); color: var(--success); width:fit-content;">Active</span>
                              @if($staff->is_online)
                                  <span class="badge" style="background: #dcfce7; color: #166534; width:fit-content; display:flex; align-items:center; gap:4px;">
                                      <span style="width:6px; height:6px; border-radius:50%; background:#16a34a; display:inline-block;"></span> Online
                                  </span>
                              @else
                                  <span class="badge" style="background: #f1f5f9; color: #475569; width:fit-content; display:flex; align-items:center; gap:4px;">
                                      <span style="width:6px; height:6px; border-radius:50%; background:#94a3b8; display:inline-block;"></span> Offline
                                  </span>
                              @endif
                          @else
                              <span class="badge" style="background: var(--danger-light); color: var(--danger); width:fit-content;">Inactive</span>
                          @endif
                      </div>
                  </td>
                  <td>
                      <div style="display:flex; gap:8px; align-items: center; flex-wrap:wrap;">
                          @if($staff->otp)
                          <button type="button" class="action-btn" style="background:var(--primary-light); color:var(--primary);" onclick="toast('OTP for {{ $staff->name }} is: {{ $staff->otp }}')" title="Show OTP">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                          </button>
                          @else
                          <form action="{{ route('shop.staff.generate-otp', $staff->id) }}" method="POST" style="margin:0;" title="Generate New OTP">
                              @csrf
                              <button type="submit" class="action-btn" style="background:var(--primary-light); color:var(--primary);">
                                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.92-10.44l5.08-5.08"/></svg>
                              </button>
                          </form>
                          @endif

                          @if($staff->is_online)
                          <form action="{{ route('shop.staff.force-offline', $staff->id) }}" method="POST" style="margin:0;" title="Force Offline" onsubmit="return confirm('Are you sure you want to disconnect this staff member?');">
                              @csrf
                              <button type="submit" class="action-btn" style="background:#fef3c7; color:#d97706;">
                                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M18.36 6.64a9 9 0 1 1-12.73 0"></path><line x1="12" y1="2" x2="12" y2="12"></line></svg>
                              </button>
                          </form>
                          @endif

                          <button type="button" onclick="document.getElementById('editStaffModal_{{ $staff->id }}').classList.remove('hidden')" class="action-btn edit" title="Edit">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                          </button>
                          <form action="{{ route('shop.staff.destroy', $staff->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this staff member?');" style="margin:0;">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="action-btn del" title="Delete">
                                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                              </button>
                          </form>
                      </div>
                  </td>
              </tr>
              @empty
              <tr>
                  <td colspan="5" style="padding: 32px; text-align: center; color: var(--text-muted);">
                      No staff members found. Click "Add Staff" to create one.
                  </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add Staff Modal -->
    <div class="modal-overlay hidden" id="staffModal">
      <form action="{{ route('shop.staff.store') }}" method="POST" class="modal modal-lg">
        @csrf
        <div class="modal-header">
          <h3>Add Staff</h3>
          <button type="button" class="modal-close" onclick="document.getElementById('staffModal').classList.add('hidden')">✕</button>
        </div>
          <div class="modal-body">
            <div class="form-grid">
              <div class="form-group">
                  <label>Name *</label>
                  <input type="text" name="name" class="input" value="{{ old('name') }}" required>
              </div>
              <div class="form-group">
                  <label>Email Address *</label>
                  <input type="email" name="email" class="input" value="{{ old('email') }}" required>
              </div>
              <div class="form-group">
                  <label>Staff Type / Role *</label>
                  <input type="text" name="staff_type" class="input" value="{{ old('staff_type') }}" required placeholder="e.g. Cashier, Manager">
              </div>
              <div class="form-group">
                  <label>Account Status</label>
                  <select name="status" class="input" required>
                      <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                      <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                  </select>
              </div>
            </div>

            <h4 style="margin-top: 24px; margin-bottom: 16px; font-size: 16px; font-weight: 600;">Assign Privileges</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                @foreach($privileges as $privilege)
                <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                    <input type="checkbox" name="privileges[]" value="{{ $privilege->id }}" style="width: 18px; height: 18px;">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: 500; font-size: 14px;">{{ $privilege->label }}</span>
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $privilege->name }}</span>
                    </div>
                </label>
                @endforeach
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('staffModal').classList.add('hidden')">Cancel</button>
            <button type="submit" class="btn btn-primary">Create Staff Member</button>
          </div>
        </form>
    </div>

    @foreach($staffs as $staff)
    @php
        $staffPrivileges = $staff->privileges ? explode(',', $staff->privileges) : [];
    @endphp
    <!-- Edit Staff Modal -->
    <div class="modal-overlay hidden" id="editStaffModal_{{ $staff->id }}">
      <form action="{{ route('shop.staff.update', $staff->id) }}" method="POST" class="modal modal-lg">
        @csrf
        @method('PUT')
        <div class="modal-header">
          <h3>Edit Staff: {{ $staff->name }}</h3>
          <button type="button" class="modal-close" onclick="document.getElementById('editStaffModal_{{ $staff->id }}').classList.add('hidden')">✕</button>
        </div>
        <div class="modal-body">
            <div class="form-grid">
              <div class="form-group">
                  <label>Full Name *</label>
                  <input type="text" name="name" class="input" value="{{ old('name', $staff->name) }}" required>
              </div>
              <div class="form-group">
                  <label>Email Address *</label>
                  <input type="email" name="email" class="input" value="{{ old('email', $staff->email) }}" required>
              </div>
              <div class="form-group">
                  <label>Staff Type / Role *</label>
                  <input type="text" name="staff_type" class="input" value="{{ old('staff_type', $staff->staff_type) }}" required placeholder="e.g. Cashier, Manager">
              </div>
              <div class="form-group">
                  <label>Account Status</label>
                  <select name="status" class="input" required>
                      <option value="active" {{ (old('status', $staff->status) == 'active') ? 'selected' : '' }}>Active</option>
                      <option value="inactive" {{ (old('status', $staff->status) == 'inactive') ? 'selected' : '' }}>Inactive</option>
                  </select>
              </div>
            </div>

            <h4 style="margin-top: 24px; margin-bottom: 16px; font-size: 16px; font-weight: 600;">Assign Privileges</h4>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                @foreach($privileges as $privilege)
                <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                    <input type="checkbox" name="privileges[]" value="{{ $privilege->id }}" style="width: 18px; height: 18px;" {{ in_array($privilege->id, $staffPrivileges) ? 'checked' : '' }}>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-weight: 500; font-size: 14px;">{{ $privilege->label }}</span>
                        <span style="font-size: 12px; color: var(--text-muted);">{{ $privilege->name }}</span>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('editStaffModal_{{ $staff->id }}').classList.add('hidden')">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
    @endforeach

</main>

@if ($errors->any())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('staffModal').classList.remove('hidden');
    });
</script>
@endif

@endsection
