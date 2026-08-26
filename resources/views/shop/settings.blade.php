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
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Active Business Type</label>
                        <select id="settingBusinessType" class="input">
                            <option value="mobile">Mobile & Electronics</option>
                            <option value="cosmetics">Cosmetics</option>
                            <option value="garments">Garments</option>
                            <option value="shoes">Shoes</option>
                            <option value="food">Food & Grocery</option>
                        </select>
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Select your business type to customize features.</small>
                    </div>

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
        
        <!-- WhatsApp Settings -->
        <div class="card" style="max-width: 600px; margin: 24px auto;">
            <div class="card-header">
                <h3>WhatsApp Notifications</h3>
            </div>
            
            <div class="card-body" style="padding: 24px;">
                <div class="settings-grid">
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; cursor: pointer; user-select: none;">
                            <input type="checkbox" id="whatsapp_config" style="margin-right: 10px; width: 20px; height: 20px;">
                            <span style="font-weight: 500; font-size: 16px;">Enable WhatsApp Notifications</span>
                        </label>
                        <p class="form-hint" style="margin-top: 8px; color: var(--text-muted); font-size: 13px; margin-left: 30px;">Turn on to automatically send WhatsApp reminders and notifications to your customers.</p>
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

        <div class="card" style="max-width: 600px; margin: 24px auto;">
            <div class="card-header">
                <h3>Backup & Restore</h3>
            </div>
            
            <div class="card-body" style="padding: 24px;">
                <div style="margin-bottom: 24px;">
                    <h4 style="margin-bottom: 8px;">Export Store Data</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 12px;">Download a complete backup of your store's data including products, sales, customers, and settings.</p>
                    <a href="{{ route('shop.api.settings.backup.export') }}" class="btn btn-outline" target="_blank" style="display: inline-flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        Take Backup
                    </a>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border); margin: 24px 0;">

                <div>
                    <h4 style="margin-bottom: 8px;">Restore Store Data</h4>
                    <p style="color: var(--danger); font-size: 13px; margin-bottom: 12px;"><strong>Warning:</strong> Restoring a backup will erase all your current store data and replace it with the data from the backup file.</p>
                    
                    <form id="backupImportForm" onsubmit="importBackup(event)" style="display: flex; gap: 12px; align-items: flex-end;">
                        <div class="form-group" style="flex: 1; margin: 0;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Upload Backup File (.json)</label>
                            <input type="file" id="backupFile" class="input" accept=".json" required>
                        </div>
                        <button type="submit" class="btn btn-primary" id="btnImportBackup">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: text-bottom;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            Restore Backup
                        </button>
                    </form>
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
            if (data.business_type) document.getElementById('settingBusinessType').value = data.business_type;
            if (data.whatsapp_config !== undefined) document.getElementById('whatsapp_config').checked = data.whatsapp_config == 1;
        }
    } catch(err) {
        console.error('Failed to load settings', err);
    }
});

async function saveStoreSettings() {
    const discount = document.getElementById('settingDiscount').value || 0;
    const tax = document.getElementById('settingTax').value || 0;
    const business_type = document.getElementById('settingBusinessType').value;
    const whatsapp_config = document.getElementById('whatsapp_config').checked ? 1 : 0;
    
    try {
        const res = await fetch('/shop/api/settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ discount, tax, business_type, whatsapp_config })
        });
        
        const data = await res.json();
        if (res.ok) {
            if (typeof toast !== 'undefined') {
                toast(data.message, 'success');
            } else {
                alert(data.message);
            }
            setTimeout(() => window.location.reload(), 1500); // Reload to apply module changes globally
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

function importBackup(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('backupFile');
    const file = fileInput.files[0];
    if (!file) return;

    confirmDelete('Are you sure you want to restore this backup? This will erase all current data and cannot be undone!', async () => {
        const btn = document.getElementById('btnImportBackup');
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Restoring...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('backup_file', file);

        try {
            const res = await fetch('/shop/api/settings/backup/import', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            });
            
            const data = await res.json();
            if (res.ok) {
                if (typeof toast !== 'undefined') toast(data.message, 'success');
                else alert(data.message);
                fileInput.value = '';
                
                setTimeout(() => window.location.reload(), 1500);
            } else {
                if (typeof toast !== 'undefined') toast(data.message || 'Error restoring backup', 'danger');
                else alert(data.message || 'Error restoring backup');
            }
        } catch(err) {
            console.error(err);
            if (typeof toast !== 'undefined') toast('Error connecting to server', 'danger');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}
</script>
@endsection
