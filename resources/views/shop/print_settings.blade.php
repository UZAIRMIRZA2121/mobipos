@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page active" id="page-print-settings">
        
        <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
            
            <!-- Left Column: Form -->
            <div class="card" style="flex: 1; min-width: 300px; margin: 0;">
                <div class="card-header">
                    <h3>Invoice Print Settings</h3>
                </div>
                
                <div class="card-body" style="padding: 24px;">
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Store Name</label>
                        <input type="text" id="settingStoreName" class="input" placeholder="e.g. My Amazing Store">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Header Text</label>
                        <textarea id="settingHeaderText" class="input" rows="2" placeholder="e.g. Welcome to our store!"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Address</label>
                        <textarea id="settingAddress" class="input" rows="2" placeholder="e.g. 123 Main Street, City"></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Phone Number</label>
                        <input type="text" id="settingPhone" class="input" placeholder="e.g. +92 300 1234567">
                    </div>

                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Footer Text</label>
                        <textarea id="settingFooterText" class="input" rows="2" placeholder="e.g. Thank you for your purchase!"></textarea>
                    </div>

                    <div style="text-align: right;">
                        <button class="btn btn-primary" onclick="savePrintSettings()" style="padding: 10px 24px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: text-bottom;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                            Update Settings
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Live Preview -->
            <div class="card" style="width: 340px; flex-shrink: 0; margin: 0; position: sticky; top: 20px;">
                <div class="card-header" style="background: #f9fafb; border-bottom: 1px solid var(--border);">
                    <h3 style="font-size: 14px; text-align: center; width: 100%; margin: 0;">Live Preview</h3>
                </div>
                <div class="card-body" style="padding: 24px; background: #f3f4f6; display: flex; justify-content: center; min-height: 400px; align-items: flex-start;">
                    
                    <!-- Thermal Receipt Paper Mockup -->
                    <div style="background: white; width: 100%; max-width: 280px; padding: 20px 15px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; text-align: center;">
                        
                        <h2 id="prevStoreName" style="font-size: 16px; margin: 0 0 5px 0; font-weight: bold; text-transform: uppercase;">STORE NAME</h2>
                        
                        <div id="prevAddress" style="margin-bottom: 2px;">Address Line</div>
                        <div id="prevPhone" style="margin-bottom: 10px;">Phone</div>
                        
                        <div id="prevHeaderText" style="margin-bottom: 15px; font-style: italic;">Header text</div>
                        
                        <div style="border-top: 1px dashed #ccc; border-bottom: 1px dashed #ccc; padding: 10px 0; margin-bottom: 15px; text-align: left;">
                            <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 5px;">
                                <span>Item</span>
                                <span>Total</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 2px;">
                                <span>Sample Item 1 x2</span>
                                <span>$20.00</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Sample Item 2 x1</span>
                                <span>$15.00</span>
                            </div>
                        </div>

                        <div style="text-align: right; font-weight: bold; margin-bottom: 15px;">
                            Total: $35.00
                        </div>

                        <div id="prevFooterText" style="margin-top: 15px;">Thank you for shopping!</div>
                        
                        <div style="margin-top: 10px; font-size: 10px; color: #666;">
                            Date: 2026-08-07 12:00 PM
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', async () => {
    // Setup Live Preview Sync
    const fields = ['StoreName', 'HeaderText', 'Address', 'Phone', 'FooterText'];
    
    fields.forEach(f => {
        const input = document.getElementById('setting' + f);
        const prev = document.getElementById('prev' + f);
        if(input && prev) {
            input.addEventListener('input', (e) => {
                let defaultText = f === 'StoreName' ? 'STORE NAME' : 
                                  (f === 'HeaderText' ? 'Header text' : 
                                  (f === 'FooterText' ? 'Thank you for shopping!' : f));
                                  
                if (e.target.tagName === 'TEXTAREA') {
                    prev.innerHTML = (e.target.value || '').replace(/\n/g, '<br>') || defaultText;
                } else {
                    prev.textContent = e.target.value || defaultText;
                }
            });
        }
    });

    try {
        const res = await fetch('/shop/api/settings/print');
        if (res.ok) {
            const data = await res.json();
            document.getElementById('settingStoreName').value = data.store_name || '';
            document.getElementById('settingHeaderText').value = data.header_text || '';
            document.getElementById('settingAddress').value = data.address || '';
            document.getElementById('settingPhone').value = data.phone || '';
            document.getElementById('settingFooterText').value = data.footer_text || '';
            
            // Trigger input events to update preview
            fields.forEach(f => {
                const el = document.getElementById('setting' + f);
                if(el) el.dispatchEvent(new Event('input'));
            });
        }
    } catch(err) {
        console.error('Failed to load settings', err);
    }
});

async function savePrintSettings() {
    const payload = {
        store_name: document.getElementById('settingStoreName').value,
        header_text: document.getElementById('settingHeaderText').value,
        address: document.getElementById('settingAddress').value,
        phone: document.getElementById('settingPhone').value,
        footer_text: document.getElementById('settingFooterText').value
    };
    
    try {
        const res = await fetch('/shop/api/settings/print', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify(payload)
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
