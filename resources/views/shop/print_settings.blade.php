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
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Store Logo</label>
                            <input type="file" id="settingLogo" class="input" accept="image/*" onchange="previewLogo(event)">
                            <small style="color: #6b7280; font-size: 12px; margin-top: 4px; display: block;">Upload a logo image (PNG/JPG). It will be printed at the top of the invoice.</small>
                            <label style="display: block; font-weight: 500; margin-top: 15px; margin-bottom: 8px; color: var(--text-dark);">Logo Size: <span id="logoSizeDisplay">120</span>px</label>
                            <input type="range" id="settingLogoSize" min="40" max="250" value="120" style="width: 100%; cursor: pointer;">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Store Name</label>
                            <textarea id="settingStoreName" class="input" rows="2" placeholder="e.g. My Amazing Store"></textarea>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Header Text</label>
                            <textarea id="settingHeaderText" class="input" rows="2" placeholder="e.g. Welcome to our store!"></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Address</label>
                            <textarea id="settingAddress" class="input" rows="2" placeholder="e.g. 123 Main Street, City"></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Phone Number</label>
                            <input type="text" id="settingPhone" class="input" placeholder="e.g. +92 300 1234567">
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Footer Text</label>
                            <textarea id="settingFooterText" class="input" rows="2" placeholder="e.g. Thank you for your purchase!"></textarea>
                        </div>

                    </div>

                    <div style="text-align: right; margin-top: 24px;">
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
                    <style>
                      @import url('https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=DM+Sans:wght@400;500;700&display=swap');
                      .receipt-preview {
                        width: 76mm;
                        font-family: 'DM Mono', 'Courier New', monospace;
                        font-size: 11px;
                        color: #000;
                        background: #fff;
                        padding: 10px;
                        line-height: 1.5;
                        margin: 0 auto;
                        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                      }
                      .receipt-preview * { box-sizing: border-box; }
                      .receipt-preview .r-center { text-align: center; }
                      .receipt-preview .r-store-name {
                        font-family: 'DM Sans', sans-serif;
                        font-size: 15px;
                        font-weight: 700;
                        letter-spacing: 0.5px;
                        margin-bottom: 2px;
                        text-transform: uppercase;
                      }
                      .receipt-preview .r-store-sub { font-size: 9.5px; color: #444; line-height: 1.6; }
                      .receipt-preview .r-divider { border: none; border-top: 1px dashed #999; margin: 6px 0; }
                      .receipt-preview .r-divider-solid { border: none; border-top: 1px solid #000; margin: 5px 0; }
                      .receipt-preview .r-divider-double { border: none; border-top: 2px solid #000; margin: 5px 0; }
                      .receipt-preview .r-row { display: flex; justify-content: space-between; font-size: 10.5px; padding: 1px 0; }
                      .receipt-preview .r-row .label { color: #555; }
                      .receipt-preview .r-row .val { font-weight: 500; }
                      .receipt-preview .r-inv-num { font-size: 12px; font-weight: 700; letter-spacing: 1px; }
                      .receipt-preview .r-items { width: 100%; border-collapse: collapse; margin: 4px 0; }
                      .receipt-preview .r-items thead tr { border-bottom: 1px solid #000; border-top: 1px solid #000; }
                      .receipt-preview .r-items th {
                        font-size: 9.5px;
                        font-weight: 700;
                        padding: 3px 2px;
                        text-transform: uppercase;
                        letter-spacing: 0.05em;
                        font-family: 'DM Sans', sans-serif;
                      }
                      .receipt-preview .r-items th:last-child, .receipt-preview .r-items td:last-child { text-align: right; }
                      .receipt-preview .r-items th:nth-child(2), .receipt-preview .r-items td:nth-child(2) { text-align: center; }
                      .receipt-preview .r-items th:nth-child(3), .receipt-preview .r-items td:nth-child(3) { text-align: right; }
                      .receipt-preview .r-items td {
                        font-size: 10px;
                        padding: 3px 2px;
                        vertical-align: top;
                        border-bottom: 1px dashed #ddd;
                      }
                      .receipt-preview .r-items tbody tr:last-child td { border-bottom: none; }
                      .receipt-preview .r-item-name { font-size: 10.5px; font-weight: 500; line-height: 1.3; }
                      .receipt-preview .r-totals { width: 100%; font-size: 10.5px; margin-top: 2px; border-collapse: collapse; }
                      .receipt-preview .r-totals td { padding: 2px 0; }
                      .receipt-preview .r-totals td:last-child { text-align: right; font-weight: 500; }
                      .receipt-preview .r-grand { font-size: 13px; font-weight: 700; font-family: 'DM Sans', sans-serif; }
                      .receipt-preview .r-grand td:last-child { font-size: 13px; font-weight: 700; }
                      .receipt-preview .r-paid { font-size: 11px; }
                      .receipt-preview .r-footer { text-align: center; font-size: 10.5px; color: #000; font-weight: 700; margin-top: 4px; line-height: 1.7; font-family: 'DM Sans', sans-serif; text-transform: uppercase; }
                    </style>

                    <div class="receipt-preview" id="invoicePrintArea">
                        <div class="r-center">
                            <div id="prevLogoContainer" style="position: relative; display: inline-block; margin: 0 auto 10px auto; display: none;">
                                <img id="prevLogo" src="" alt="Logo" style="object-fit: contain; display: block;">
                                <button onclick="removeLogo()" type="button" title="Remove Logo" style="position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; border: none; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; line-height: 1; padding: 0;">&times;</button>
                            </div>
                            
                            <div class="r-store-name" id="prevStoreName">STORE NAME</div>
                            
                            <div class="r-store-sub">
                                <span id="prevHeaderText">Header text</span><br>
                                <span id="prevAddress">Address Line</span><br>
                                <span id="prevPhone">Phone</span>
                            </div>
                            
                            <div style="font-weight:bold; font-size:14px; margin-top:5px;">INVOICE</div>
                        </div>

                        <hr class="r-divider-solid"/>

                        <div class="r-row"><span class="label">Receipt #:</span><span class="val r-inv-num">000057</span></div>
                        <div class="r-row"><span class="label">Date:</span><span class="val">07/08/2026 12:48 PM</span></div>
                        <div class="r-row"><span class="label">Customer:</span><span class="val">Walk-in</span></div>
                        <div class="r-row"><span class="label">Payment:</span><span class="val">Cash</span></div>

                        <hr class="r-divider-solid"/>

                        <table class="r-items">
                            <thead>
                                <tr>
                                    <th style="width:42%">Item</th>
                                    <th style="width:10%;text-align:center">Qty</th>
                                    <th style="width:48%;text-align:right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><div class="r-item-name">Type-C to Lightning Cable 1m</div><div style="font-size:9px; color:#555; margin-top:2px;">@ PKR 100</div></td>
                                    <td style="text-align:center">1</td>
                                    <td style="text-align:right">PKR 100</td>
                                </tr>
                            </tbody>
                        </table>

                        <hr class="r-divider-solid"/>

                        <table class="r-totals">
                            <tr><td>Subtotal:</td><td>PKR 100</td></tr>
                        </table>

                        <hr class="r-divider-double"/>

                        <table class="r-totals">
                            <tr class="r-grand"><td>TOTAL:</td><td>PKR 100</td></tr>
                        </table>

                        <hr class="r-divider-solid"/>

                        <table class="r-totals">
                            <tr class="r-paid"><td>Paid:</td><td>PKR 500</td></tr>
                            <tr class="r-paid"><td>Change:</td><td>PKR 400</td></tr>
                            <tr class="r-paid"><td style="font-weight:bold">Status:</td><td style="font-weight:bold; font-size: 12px;">PAID</td></tr>
                        </table>

                        <hr class="r-divider-solid"/>

                        <div class="r-footer" id="prevFooterText">Thank you for your shopping!</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
let removeLogoFlag = false;

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
            
            if (data.logo) {
                const prevLogo = document.getElementById('prevLogo');
                const container = document.getElementById('prevLogoContainer');
                prevLogo.src = data.logo;
                container.style.display = 'inline-block';
            }
            if (data.logo_size) {
                document.getElementById('settingLogoSize').value = data.logo_size;
                document.getElementById('logoSizeDisplay').textContent = data.logo_size;
                document.getElementById('prevLogo').style.maxWidth = data.logo_size + 'px';
            }
            
            // Setup logo size slider listener
            document.getElementById('settingLogoSize').addEventListener('input', function(e) {
                document.getElementById('logoSizeDisplay').textContent = e.target.value;
                document.getElementById('prevLogo').style.maxWidth = e.target.value + 'px';
            });
            
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

function previewLogo(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const prevLogo = document.getElementById('prevLogo');
            const container = document.getElementById('prevLogoContainer');
            prevLogo.src = e.target.result;
            container.style.display = 'inline-block';
            removeLogoFlag = false;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeLogo() {
    const prevLogo = document.getElementById('prevLogo');
    const container = document.getElementById('prevLogoContainer');
    const input = document.getElementById('settingLogo');
    
    prevLogo.src = '';
    container.style.display = 'none';
    input.value = '';
    removeLogoFlag = true;
}

async function savePrintSettings() {
    const formData = new FormData();
    formData.append('store_name', document.getElementById('settingStoreName').value);
    formData.append('header_text', document.getElementById('settingHeaderText').value);
    formData.append('address', document.getElementById('settingAddress').value);
    formData.append('phone', document.getElementById('settingPhone').value);
    formData.append('footer_text', document.getElementById('settingFooterText').value);
    
    if (removeLogoFlag) {
        formData.append('remove_logo', '1');
    }
    formData.append('logo_size', document.getElementById('settingLogoSize').value);

    const logoFile = document.getElementById('settingLogo').files[0];
    if (logoFile) {
        formData.append('logo', logoFile);
    }
    
    try {
        const res = await fetch('/shop/api/settings/print', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: formData
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
