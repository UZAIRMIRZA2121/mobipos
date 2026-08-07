@extends('layouts.app')

@section('content')
<div class="content-body" style="display: flex; flex-direction: column; align-items: center; padding-top: 40px;">
    
    <div style="width: 100%; max-width: 600px; margin-bottom: 24px;">
        <h2 class="page-title" style="margin-bottom: 4px;">Profile Settings</h2>
        <p class="page-subtitle" style="color: var(--text-muted);">Update your account details and password</p>
    </div>

    <div class="card" style="width: 100%; max-width: 600px;">
        <div class="card-header">
            <h3>Account Information</h3>
        </div>
        <div class="card-body" style="padding: 24px;">
            
            @if(session('success'))
            <div style="padding:12px; margin-bottom:20px; border-radius:8px; background:var(--success-light); color:var(--success); font-weight:500; font-size:14px; display:flex; align-items:center; gap:8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                {{ session('success') }}
            </div>
            @endif

            @if ($errors->any())
            <div style="padding:12px; margin-bottom:20px; border-radius:8px; background:var(--danger-light); color:var(--danger); font-size:14px;">
                <ul style="margin:0; padding-left:20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('shop.profile.update') }}" method="POST">
                @csrf
                
                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Email Address (Read-Only)</label>
                    <input type="email" class="input" value="{{ $user->email }}" readonly style="background-color: var(--surface-hover); color: var(--text-muted); cursor: not-allowed; width: 100%;">
                </div>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Username <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="input" value="{{ old('name', $user->name) }}" required style="width: 100%;">
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 30px 0;">
                <h4 style="font-size: 15px; font-weight: 600; margin-bottom: 15px; color: var(--text-dark);">Change Password <span style="font-size: 12px; font-weight: normal; color: var(--text-muted);">(Leave blank if you don't want to change it)</span></h4>

                <div class="form-group" style="margin-bottom: 20px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">New Password</label>
                    <input type="password" name="password" class="input" placeholder="Enter new password" style="width: 100%;">
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="input" placeholder="Confirm new password" style="width: 100%;">
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px; vertical-align: text-bottom;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
