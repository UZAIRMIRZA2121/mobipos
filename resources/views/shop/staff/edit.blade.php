@extends('layouts.app')

@section('content')
<div class="content-header">
  <div class="header-left">
    <h2 class="page-title">Edit Staff</h2>
    <p class="page-subtitle">Update staff member details and privileges</p>
  </div>
  <div class="header-right">
      <a href="{{ route('shop.staff.index') }}" class="btn btn-outline">Back to List</a>
  </div>
</div>

<div class="content-body" style="display: flex; justify-content: center; padding-top: 20px;">
    <div class="card" style="width: 100%; max-width: 800px;">
        <div class="card-header">
            <h3>Staff Details</h3>
        </div>
        
        <form action="{{ route('shop.staff.update', $staff->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body" style="padding: 24px;">
                @if ($errors->any())
                    <div style="padding:12px; margin-bottom:20px; border-radius:8px; background:var(--danger-light); color:var(--danger); font-size:14px;">
                        <ul style="margin:0; padding-left:20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label class="form-label" for="name">Name</label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $staff->name) }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label class="form-label" for="staff_type">Staff Type / Role</label>
                        <input type="text" id="staff_type" name="staff_type" class="form-control" value="{{ old('staff_type', $staff->staff_type) }}" required>
                    </div>
                    <div>
                        <label class="form-label" for="status">Account Status</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="active" {{ old('status', $staff->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $staff->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label class="form-label">Current OTP</label>
                    <input type="text" class="form-control" value="{{ $staff->otp }}" disabled style="background: var(--bg-color); cursor: not-allowed;">
                    <small style="color: var(--text-muted); margin-top: 6px; display: block;">This OTP is used by the staff to login.</small>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 30px 0;">

                <h4 style="margin-bottom: 16px; font-size: 16px; font-weight: 600;">Assign Privileges</h4>
                <p style="font-size: 14px; color: var(--text-muted); margin-bottom: 20px;">Select the sections this staff member is allowed to access.</p>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    @foreach($privileges as $privilege)
                    <label style="display: flex; align-items: center; gap: 10px; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer; transition: all 0.2s ease; @if(in_array($privilege->id, old('privileges', $staffPrivileges))) background-color: var(--primary-light); border-color: var(--primary); @endif">
                        <input type="checkbox" name="privileges[]" value="{{ $privilege->id }}" style="width: 18px; height: 18px;" 
                            {{ in_array($privilege->id, old('privileges', $staffPrivileges)) ? 'checked' : '' }}>
                        <div style="display: flex; flex-direction: column;">
                            <span style="font-weight: 500; font-size: 14px;">{{ $privilege->label }}</span>
                            <span style="font-size: 12px; color: var(--text-muted);">Route: {{ $privilege->route_name }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="card-footer" style="padding: 20px 24px; text-align: right; background: var(--bg-color); border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                <button type="button" class="btn btn-outline" style="margin-right: 12px;" onclick="window.location.href='{{ route('shop.staff.index') }}'">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection
