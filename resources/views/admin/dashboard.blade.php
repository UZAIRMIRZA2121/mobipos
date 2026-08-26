@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page-header" style="margin-bottom: 20px;">
        <h1 class="page-title">Admin Dashboard</h1>
        <p class="page-subtitle">Overview of sales, commissions, and users.</p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <!-- Total Sales Widget -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">PKR {{ number_format($totalSales, 2) }}</div>
                <div style="color: #64748b; font-size: 0.9rem;">Total Subscription Revenue</div>
            </div>
        </div>

        <!-- Total Commission Paid -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">PKR {{ number_format($totalCommissionPaid, 2) }}</div>
                <div style="color: #64748b; font-size: 0.9rem;">Total Commission Paid</div>
            </div>
        </div>

        <!-- Total Commission Pending -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">PKR {{ number_format($totalCommissionPending, 2) }}</div>
                <div style="color: #64748b; font-size: 0.9rem;">Total Commission Pending</div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <!-- Total Users Widget -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">{{ $totalUsers }}</div>
                <div style="color: #64748b; font-size: 0.9rem;">Total Registered Users</div>
            </div>
        </div>

        <!-- Total Stores -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(236, 72, 153, 0.1); color: #ec4899; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">{{ $totalStores }}</div>
                <div style="color: #64748b; font-size: 0.9rem;">Store Accounts</div>
            </div>
        </div>

        <!-- Total Sellers -->
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; display: flex; align-items: center; gap: 15px;">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center;">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <div>
                <div style="font-size: 1.5rem; font-weight: 700; color: #1e293b;">{{ $totalSellers }}</div>
                <div style="color: #64748b; font-size: 0.9rem;">Seller (Affiliate) Accounts</div>
            </div>
        </div>
    </div>

    <!-- Stores Table -->
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header">
            <h3 class="card-title" style="margin: 0; font-size: 1.1rem; font-weight: 600;">Registered Stores</h3>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stores as $store)
                            <tr>
                                <td>#{{ $store->id }}</td>
                                <td>{{ $store->name }}</td>
                                <td>{{ $store->email }}</td>
                                <td>{{ $store->created_at ? $store->created_at->format('M d, Y') : 'N/A' }}</td>
                                <td>
                                    <form action="{{ route('admin.users.toggle-status', $store->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" style="background: none; border: none; padding: 0; cursor: pointer;" title="Click to toggle status">
                                            @if($store->status == 1)
                                                <span style="background: #10b981; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; display: inline-block;">Approved</span>
                                            @else
                                                <span style="background: #f59e0b; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; display: inline-block;">Trial</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <form action="{{ route('admin.users.impersonate', $store->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline" style="padding: 6px 10px; display: inline-flex; align-items: center; justify-content: center;" title="Login as Shop">
                                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                            </button>
                                        </form>
                                        <button type="button" class="btn btn-outline" style="padding: 6px 10px; display: inline-flex; align-items: center; justify-content: center; color: #25D366; border-color: #25D366;" title="Manage WhatsApp Settings" onclick="openAdminWhatsappModal({{ $store->id }})">
                                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 20px; color: #64748b;">No stores found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Admin WhatsApp Settings Modal -->
<div class="modal-overlay hidden" id="adminWhatsappModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3>WhatsApp Integration (Ultramsg)</h3>
            <button class="modal-close" onclick="closeAdminWhatsappModal()">✕</button>
        </div>
        
        <div class="modal-body" style="padding: 24px;">
            <input type="hidden" id="adminWhatsappUserId">
            
            <div style="margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h4 style="margin: 0; font-size: 14px; color: var(--text-dark);">API Credentials</h4>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; font-weight: 500; color: var(--text-dark);">
                        <input type="checkbox" id="adminSettingWhatsappConfig" style="width: 16px; height: 16px;">
                        Enable WhatsApp Integration
                    </label>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">API URL</label>
                    <input type="text" id="adminSettingUltramsgApiUrl" class="input" placeholder="e.g. https://api.ultramsg.com/instance12345">
                    <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Your Ultramsg API URL.</small>
                </div>
                <div style="display: flex; gap: 20px;">
                    <div class="form-group" style="flex: 1;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Instance ID</label>
                        <input type="text" id="adminSettingUltramsgInstance" class="input" placeholder="e.g. instance12345">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Your Ultramsg Instance ID.</small>
                    </div>

                    <div class="form-group" style="flex: 1;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Token</label>
                        <input type="text" id="adminSettingUltramsgToken" class="input" placeholder="e.g. 1a2b3c4d5e6f">
                        <small style="color: var(--text-muted); font-size: 12px; margin-top: 4px; display: block;">Your Ultramsg API Token.</small>
                    </div>
                </div>
            </div>

            <div>
                <h4 style="margin-bottom: 16px; font-size: 14px; color: var(--text-dark);">Billing Information</h4>
                <div style="display: flex; gap: 20px; align-items: flex-start;">
                    <div class="form-group" style="flex: 1;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Total Msg Sent</label>
                        <input type="text" id="adminSettingUltramsgTotalSent" class="input" readonly style="background: #f8fafc; cursor: not-allowed; font-weight: bold;">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Per Msg Cost</label>
                        <input type="number" id="adminSettingUltramsgMsgCost" class="input" step="0.01" min="0" oninput="calculateTotalAmount()">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label style="display: block; font-weight: 500; margin-bottom: 8px; color: var(--text-dark);">Total Amount</label>
                        <input type="text" id="adminSettingUltramsgTotalAmount" class="input" readonly style="background: #f8fafc; cursor: not-allowed; font-weight: bold; color: #10b981;">
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer" style="display: flex; justify-content: space-between; align-items: center;">
            <button class="btn btn-outline" style="color: #ef4444; border-color: #ef4444;" onclick="resetAdminWhatsappCounter()">Reset Counter</button>
            <div>
                <button class="btn btn-ghost" onclick="closeAdminWhatsappModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveAdminWhatsappSettings()" style="padding: 10px 24px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px; vertical-align: text-bottom;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Save Settings
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function calculateTotalAmount() {
        const sent = parseInt(document.getElementById('adminSettingUltramsgTotalSent').value) || 0;
        const cost = parseFloat(document.getElementById('adminSettingUltramsgMsgCost').value) || 0;
        document.getElementById('adminSettingUltramsgTotalAmount').value = (sent * cost).toFixed(2);
    }

    async function openAdminWhatsappModal(userId) {
        document.getElementById('adminWhatsappUserId').value = userId;
        document.getElementById('adminSettingWhatsappConfig').checked = false;
        document.getElementById('adminSettingUltramsgApiUrl').value = '';
        document.getElementById('adminSettingUltramsgInstance').value = '';
        document.getElementById('adminSettingUltramsgToken').value = '';
        document.getElementById('adminSettingUltramsgTotalSent').value = '0';
        document.getElementById('adminSettingUltramsgMsgCost').value = '0.00';
        document.getElementById('adminSettingUltramsgTotalAmount').value = '0.00';
        
        // Show modal immediately with empty fields
        document.getElementById('adminWhatsappModal').classList.remove('hidden');

        try {
            const response = await fetch(`/admin/users/${userId}/whatsapp-settings`);
            if (response.ok) {
                const data = await response.json();
                document.getElementById('adminSettingWhatsappConfig').checked = !!data.whatsapp_config;
                document.getElementById('adminSettingUltramsgApiUrl').value = data.ultramsg_api_url || '';
                document.getElementById('adminSettingUltramsgInstance').value = data.ultramsg_instance_id || '';
                document.getElementById('adminSettingUltramsgToken').value = data.ultramsg_token || '';
                document.getElementById('adminSettingUltramsgTotalSent').value = data.ultramsg_total_sent || 0;
                document.getElementById('adminSettingUltramsgMsgCost').value = data.ultramsg_msg_cost || 0.00;
                calculateTotalAmount();
            }
        } catch (error) {
            console.error('Failed to fetch whatsapp settings:', error);
        }
    }

    function closeAdminWhatsappModal() {
        document.getElementById('adminWhatsappModal').classList.add('hidden');
    }

    async function saveAdminWhatsappSettings() {
        const userId = document.getElementById('adminWhatsappUserId').value;
        const isConfigured = document.getElementById('adminSettingWhatsappConfig').checked;
        const apiUrl = document.getElementById('adminSettingUltramsgApiUrl').value;
        const instanceId = document.getElementById('adminSettingUltramsgInstance').value;
        const token = document.getElementById('adminSettingUltramsgToken').value;
        const msgCost = document.getElementById('adminSettingUltramsgMsgCost').value;

        try {
            const response = await fetch(`/admin/users/${userId}/whatsapp-settings`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    whatsapp_config: isConfigured,
                    ultramsg_api_url: apiUrl,
                    ultramsg_instance_id: instanceId,
                    ultramsg_token: token,
                    ultramsg_msg_cost: msgCost
                })
            });

            if (response.ok) {
                if (typeof toast !== 'undefined') toast('Settings updated successfully', 'success');
                else alert('Settings updated successfully');
                closeAdminWhatsappModal();
            } else {
                if (typeof toast !== 'undefined') toast('Failed to update settings', 'danger');
                else alert('Failed to update settings');
            }
        } catch (error) {
            console.error('Error saving whatsapp settings:', error);
            if (typeof toast !== 'undefined') toast('Server error', 'danger');
            else alert('Server error');
        }
    }

    async function resetAdminWhatsappCounter() {
        const userId = document.getElementById('adminWhatsappUserId').value;
        if (!confirm('Are you sure you want to reset the message counter for this shop?')) return;

        try {
            const response = await fetch(`/admin/users/${userId}/whatsapp-settings/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (response.ok) {
                document.getElementById('adminSettingUltramsgTotalSent').value = '0';
                calculateTotalAmount();
                if (typeof toast !== 'undefined') toast('Counter reset successfully', 'success');
                else alert('Counter reset successfully');
            } else {
                if (typeof toast !== 'undefined') toast('Failed to reset counter', 'danger');
                else alert('Failed to reset counter');
            }
        } catch (error) {
            console.error('Error resetting counter:', error);
            if (typeof toast !== 'undefined') toast('Server error', 'danger');
            else alert('Server error');
        }
    }
</script>

@endsection