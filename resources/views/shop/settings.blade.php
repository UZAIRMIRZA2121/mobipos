@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page active" id="page-settings">
        <div class="card" style="max-width: 600px; margin: 0 auto;">
            <div class="card-header">
                <h3>Store Settings</h3>
            </div>
            
            <div class="card-body" style="padding: 24px;">
                <div style="display: flex; gap: 20px; margin-bottom: 24px;">
                    <div class="form-group" style="flex: 1;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Global Discount (%)</label>
                        <input type="number" id="settingDiscount" class="input" min="0" max="100" step="0.01" placeholder="e.g. 5" value="0">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">This discount percentage will be automatically applied to new sales.</small>
                    </div>
                    
                    <div class="form-group" style="flex: 1;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Global Tax (%)</label>
                        <input type="number" id="settingTax" class="input" min="0" max="100" step="0.01" placeholder="e.g. 10" value="0">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">This tax percentage will be automatically applied to new sales.</small>
                    </div>
                </div>

                <div style="text-align: right;">
                    <button class="btn btn-primary" onclick="saveStoreSettings()" style="padding: 10px 24px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: text-bottom;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Update Settings
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await fetch('/shop/api/settings');
        if (res.ok) {
            const data = await res.json();
            document.getElementById('settingDiscount').value = data.discount || 0;
            document.getElementById('settingTax').value = data.tax || 0;
        }
    } catch(err) {
        console.error('Failed to load settings', err);
    }
});

async function saveStoreSettings() {
    const discount = document.getElementById('settingDiscount').value || 0;
    const tax = document.getElementById('settingTax').value || 0;
    
    try {
        const res = await fetch('/shop/api/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ discount, tax })
        });
        
        const data = await res.json();
        if (res.ok) {
            if (typeof toast !== 'undefined') {
                toast(data.message, 'success');
            } else {
                alert(data.message);
            }
        } else {
            if (typeof toast !== 'undefined') {
                toast(data.message || 'Error updating settings', 'danger');
            } else {
                alert(data.message || 'Error updating settings');
            }
        }
    } catch(err) {
        console.error(err);
        if (typeof toast !== 'undefined') toast('Error connecting to server', 'danger');
    }
}
</script>
@endsection
