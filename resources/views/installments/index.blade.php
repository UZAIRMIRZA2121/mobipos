@extends('layouts.app')

@section('content')
<div class="pos-container" style="padding: 20px;">

    <!-- Summary Cards -->
    <div style="display:flex; gap:15px; margin-bottom: 20px; flex-wrap: wrap;">
        <!-- Card 1 -->
        <div class="card" style="flex:1; min-width:200px; border-radius: 12px; padding: 20px; display:flex; flex-direction:row; align-items:center; gap:15px; border:none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #dcfce7; color: #166534; display:flex; justify-content:center; align-items:center; font-size: 24px; font-weight:bold;">
                $
            </div>
            <div>
                <h4 style="margin:0; font-size: 18px; font-weight:700; color: #111827;">PKR {{ number_format($sumTotalAmount, 2) }}</h4>
                <p style="margin:0; font-size: 13px; color: #6b7280;">Total Amount</p>
            </div>
        </div>
        
        <!-- Card 2 -->
        <div class="card" style="flex:1; min-width:200px; border-radius: 12px; padding: 20px; display:flex; flex-direction:row; align-items:center; gap:15px; border:none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #ffedd5; color: #c2410c; display:flex; justify-content:center; align-items:center; font-size: 24px; font-weight:bold;">
                $
            </div>
            <div>
                <h4 style="margin:0; font-size: 18px; font-weight:700; color: #111827;">PKR {{ number_format($sumTotalPaid, 2) }}</h4>
                <p style="margin:0; font-size: 13px; color: #6b7280;">Total Paid</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="card" style="flex:1; min-width:200px; border-radius: 12px; padding: 20px; display:flex; flex-direction:row; align-items:center; gap:15px; border:none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #ccfbf1; color: #0f766e; display:flex; justify-content:center; align-items:center; font-size: 24px; font-weight:bold;">
                $
            </div>
            <div>
                <h4 style="margin:0; font-size: 18px; font-weight:700; color: #111827;">PKR {{ number_format($sumUnpaidAmount, 2) }}</h4>
                <p style="margin:0; font-size: 13px; color: #6b7280;">Unpaid Amount</p>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <h3 style="margin:0;">Installment Plans</h3>
            
            <div class="header-actions" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <input type="date" id="instStartDate" class="input input-sm" title="Start Date" onchange="filterInstallments()"/>
                <input type="date" id="instEndDate" class="input input-sm" title="End Date" onchange="filterInstallments()"/>
                
                <input type="text" id="instSearch" class="input input-sm" placeholder="Search Order/Customer..." style="width: 200px;" onkeyup="debounceFilterInstallments()"/>
                
                <button type="button" class="btn btn-sm btn-outline inst-status-filter" data-status="paid" onclick="toggleInstStatusFilter('paid', this)">This Month Paid</button>
                <button type="button" class="btn btn-sm btn-outline inst-status-filter" data-status="unpaid" onclick="toggleInstStatusFilter('unpaid', this)">This Month Unpaid</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="openNewInstallmentModal()">+ New Installment</button>
            </div>
        </div>

        <div class="card-body">
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Down Payment</th>
                            <th>Paid So Far</th>
                            <th>Remaining</th>
                            <th>Monthly Inst.</th>
                            <th>Status</th>
                            <th>Next Payment</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($installments as $installment)
                            @php
                                $totalPaid = $installment->down_payment + $installment->payments->sum('amount');
                                $remaining = $installment->total_amount - $totalPaid;
                                
                                $paymentDay = $installment->payment_day ?? 10;
                                $currentDate = now()->startOfDay();
                                $dueDate = now()->setDay($paymentDay)->startOfDay();
                                
                                // Installments start billing from the month following creation
                                if ($installment->created_at->format('Y-m') === $currentDate->format('Y-m')) {
                                    $dueDate->addMonth();
                                }
                                
                                $dueText = '';
                                $dueColor = '';
                                
                                if ($installment->status === 'Completed' || $remaining <= 0) {
                                    $dueText = 'Fully Paid';
                                    $dueColor = '#10b981';
                                } else {
                                    $paidThisMonth = $installment->payments->filter(function($payment) {
                                        return \Carbon\Carbon::parse($payment->payment_date)->format('Y-m') === now()->format('Y-m');
                                    })->sum('amount');
                                    
                                    if ($paidThisMonth > 0) {
                                        $dueText = 'Paid this month';
                                        $dueColor = '#10b981';
                                    } else {
                                        if ($currentDate->gt($dueDate)) {
                                            $daysLate = $currentDate->diffInDays($dueDate);
                                            $dueText = $daysLate . ' day(s) late';
                                            $dueColor = '#ef4444';
                                        } elseif ($currentDate->lt($dueDate)) {
                                            $daysLeft = $currentDate->diffInDays($dueDate);
                                            $dueText = 'Due in ' . $daysLeft . ' day(s)';
                                            $dueColor = '#3b82f6';
                                        } else {
                                            $dueText = 'Due Today';
                                            $dueColor = '#f59e0b';
                                        }
                                    }
                                }
                            @endphp
                            <tr class="installment-row" 
                                data-search="{{ strtolower($installment->order_id . ' ' . ($installment->customer->name ?? '') . ' ' . ($installment->customer->phone ?? '')) }}"
                                data-date="{{ $installment->created_at->format('Y-m-d') }}"
                                data-month-status="{{ $paidThisMonth > 0 ? 'paid' : 'unpaid' }}">
                                <td>{{ $installment->order_id }}</td>
                                <td>{{ $installment->customer->name ?? 'Unknown' }}</td>
                                <td>
                                    <div>PKR {{ number_format($installment->total_amount, 2) }}</div>
                                    @if($installment->interest_percentage > 0)
                                        <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">Base: PKR {{ number_format($installment->actual_price, 2) }}</div>
                                        <div style="font-size: 11px; color: #6b7280;">Interest: {{ $installment->interest_percentage }}%</div>
                                    @endif
                                </td>
                                <td>PKR {{ number_format($installment->down_payment, 2) }}</td>
                                <td>PKR {{ number_format($totalPaid, 2) }}</td>
                                <td>PKR {{ number_format($remaining, 2) }}</td>
                                <td>PKR {{ number_format($installment->agreed_monthly_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $installment->status === 'Completed' ? 'badge-success' : 'badge-warning' }}">
                                        {{ $installment->status }}
                                    </span>
                                </td>
                                <td>
                                    <span style="color: {{ $dueColor }}; font-weight: 500;">
                                        {{ $dueText }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:5px;">
                                        <a href="{{ route('shop.installments.show', $installment->id) }}" class="action-btn view" title="View / Pay">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        </a>
                                        <a href="{{ route('shop.installments.print', $installment->id) }}" class="action-btn" style="background:#fef3c7; color:#d97706;" target="_blank" title="Print Installment Details">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                                        </a>
                                        <button type="button" class="action-btn delete" style="background:#fee2e2; color:#dc2626; border:none; cursor:pointer;" onclick="deleteInstallment({{ $installment->order_id }})" title="Delete Installment Setup">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">No installment plans found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    let filterTimeout = null;
    let currentInstStatus = null;

    function debounceFilterInstallments() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(filterInstallments, 1500); // 1.5 seconds debounce
    }

    function toggleInstStatusFilter(status, btn) {
        if (currentInstStatus === status) {
            // Uncheck
            currentInstStatus = null;
            btn.classList.remove('active');
        } else {
            // Check
            currentInstStatus = status;
            document.querySelectorAll('.inst-status-filter').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
        }
        filterInstallments();
    }

    function filterInstallments() {
        const search = document.getElementById('instSearch').value.toLowerCase();
        const start = document.getElementById('instStartDate').value;
        const end = document.getElementById('instEndDate').value;
        const rows = document.querySelectorAll('.installment-row');

        rows.forEach(row => {
            const rowSearch = row.getAttribute('data-search');
            const rowDate = row.getAttribute('data-date');
            const rowStatus = row.getAttribute('data-month-status');

            let matchSearch = search === '' || rowSearch.includes(search);
            let matchStatus = currentInstStatus === null || rowStatus === currentInstStatus;
            
            let matchDate = true;
            if (start && rowDate < start) matchDate = false;
            if (end && rowDate > end) matchDate = false;

            if (matchSearch && matchStatus && matchDate) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    function deleteInstallment(orderId) {
        confirmDelete('Are you sure you want to cancel/delete this installment setup? This will also delete the associated order and return the item to stock.', () => {
            api(`/shop/api/orders/${orderId}`, 'DELETE')
                .then(res => {
                    toast('Installment setup deleted successfully', 'success');
                    setTimeout(() => window.location.reload(), 1000);
                })
                .catch(err => toast(err.message || 'Error deleting installment', 'danger'));
        });
    }

    // New Installment Modal Logic
    function openNewInstallmentModal() {
        const customers = store.get('customers', []);
        const products = store.get('products', []);
        
        const custSelect = document.getElementById('newInstCustomer');
        custSelect.innerHTML = '<option value="">Select Customer</option>' + 
            customers.map(c => `<option value="${c.id}">${c.name} (${c.phone || ''})</option>`).join('');
            
        const prodSelect = document.getElementById('newInstProduct');
        prodSelect.innerHTML = '<option value="">Select Product</option>' + 
            products.map(p => `<option value="${p.id}" data-price="${p.sale}" data-type="${p.type}">${p.name} - PKR ${p.sale}</option>`).join('');

        // Reset fields
        document.getElementById('newInstStockDiv').style.display = 'none';
        document.getElementById('newInstStockUnit').innerHTML = '';
        document.getElementById('newInstBasePrice').value = '';
        document.getElementById('newInstPercentage').value = 0;
        document.getElementById('newInstTotal').value = '';
        document.getElementById('newInstAdvance').value = 0;
        document.getElementById('newInstMonths').value = 1;
        document.getElementById('newInstPaymentDay').value = 10;
        
        calcNewInstallment();

        document.getElementById('newInstallmentModal').classList.remove('hidden');
    }

    function closeNewInstallmentModal() {
        document.getElementById('newInstallmentModal').classList.add('hidden');
    }

    function onNewInstProductChange() {
        const prodSelect = document.getElementById('newInstProduct');
        const option = prodSelect.options[prodSelect.selectedIndex];
        if (!option.value) {
            document.getElementById('newInstBasePrice').value = 0;
            document.getElementById('newInstStockDiv').style.display = 'none';
            calcNewInstallment();
            return;
        }

        const price = parseFloat(option.getAttribute('data-price') || 0);
        document.getElementById('newInstBasePrice').value = price;
        
        // Handle Stock Units (IMEI)
        const products = store.get('products', []);
        const prod = products.find(p => p.id == option.value);
        
        const stockSelect = document.getElementById('newInstStockUnit');
        if (prod && prod.stock_units && prod.stock_units.length > 0) {
            stockSelect.innerHTML = '<option value="">Select Unit (IMEI/Serial)</option>' + 
                prod.stock_units.filter(s => s.status === 'available').map(s => `<option value="${s.id}">${s.imeis || 'Unknown IMEI'}</option>`).join('');
            document.getElementById('newInstStockDiv').style.display = 'block';
        } else {
            stockSelect.innerHTML = '';
            document.getElementById('newInstStockDiv').style.display = 'none';
        }

        calcNewInstallment();
    }

    function calcNewInstallment() {
        const base = parseFloat(document.getElementById('newInstBasePrice').value || 0);
        const pct = parseFloat(document.getElementById('newInstPercentage').value || 0);
        
        let total = parseFloat(document.getElementById('newInstTotal').value || 0);
        
        if (document.activeElement.id === 'newInstPercentage' || document.activeElement.id === 'newInstProduct' || document.activeElement.id === '') {
            total = base + (base * (pct / 100));
            document.getElementById('newInstTotal').value = total.toFixed(2);
        } else if (document.activeElement.id === 'newInstTotal') {
            total = parseFloat(document.getElementById('newInstTotal').value || 0);
        }

        let advance = parseFloat(document.getElementById('newInstAdvance').value || 0);
        if (advance > total) {
            advance = total;
            document.getElementById('newInstAdvance').value = advance;
        }

        const remaining = total - advance;
        document.getElementById('newInstRemaining').value = remaining.toFixed(2);

        let months = parseInt(document.getElementById('newInstMonths').value || 1);
        if (months < 1) {
            months = 1;
            document.getElementById('newInstMonths').value = months;
        }

        const monthly = (remaining / months).toFixed(2);
        document.getElementById('newInstMonthlyAmount').value = monthly;
    }

    async function submitNewInstallment() {
        const custId = document.getElementById('newInstCustomer').value;
        const prodId = document.getElementById('newInstProduct').value;
        const stockId = document.getElementById('newInstStockUnit').value;
        
        if (!custId) return toast('Please select a customer', 'warning');
        if (!prodId) return toast('Please select a product', 'warning');
        
        const stockDivVisible = document.getElementById('newInstStockDiv').style.display === 'block';
        if (stockDivVisible && !stockId) return toast('Please select an IMEI/Unit', 'warning');

        const total = parseFloat(document.getElementById('newInstTotal').value || 0);
        const advance = parseFloat(document.getElementById('newInstAdvance').value || 0);
        const remaining = parseFloat(document.getElementById('newInstRemaining').value || 0);
        const months = parseInt(document.getElementById('newInstMonths').value || 1);
        const monthly = parseFloat(document.getElementById('newInstMonthlyAmount').value || 0);
        const payment_day = parseInt(document.getElementById('newInstPaymentDay').value || 10);
        const percentage = parseFloat(document.getElementById('newInstPercentage').value || 0);
        const base_price = parseFloat(document.getElementById('newInstBasePrice').value || 0);

        const payload = {
            buyer_id: custId,
            subtotal: base_price,
            tax: 0,
            discount: 0,
            total: total,
            paid_amount: advance,
            due_amount: remaining,
            payment_status: advance > 0 ? 'partial' : 'unpaid',
            payment_method: 'installment',
            save_to_ledger: 0,
            is_installment: 1,
            installment_down_payment: advance,
            installment_months: months,
            installment_monthly_amount: monthly,
            installment_payment_day: payment_day,
            installment_interest_percentage: percentage,
            installment_actual_price: base_price,
            items: [{
                product_id: prodId,
                qty: 1,
                price: base_price,
                stock_units: stockId ? [stockId] : []
            }]
        };

        const btn = document.getElementById('btnSubmitNewInst');
        btn.disabled = true;
        btn.innerHTML = 'Processing...';

        try {
            await api('/shop/api/orders', 'POST', payload);
            toast('Installment plan created successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } catch (e) {
            toast(e.message || 'Error creating installment', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Create Installment';
        }
    }
</script>

<!-- New Installment Modal -->
<div class="modal-overlay hidden" id="newInstallmentModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>Add New Installment</h3>
      <button class="modal-close" onclick="closeNewInstallmentModal()">×</button>
    </div>
    <div class="modal-body" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
      <div class="form-group" style="width: 100%; padding: 0 10px; margin-bottom: 15px;">
        <label>Customer</label>
        <div style="display:flex; gap:4px;">
          <select id="newInstCustomer" class="input" style="flex:1;">
            <option value="">Select Customer</option>
          </select>
          <button class="btn btn-primary" onclick="openCustModal()" title="Add Customer" style="padding:0 12px;font-size:18px;">+</button>
        </div>
      </div>
      
      <div class="form-group" style="width: 100%; padding: 0 10px; margin-bottom: 15px;">
        <label>Product</label>
        <select id="newInstProduct" class="input" onchange="onNewInstProductChange()">
          <option value="">Select Product</option>
        </select>
      </div>

      <div class="form-group" style="width: 100%; padding: 0 10px; margin-bottom: 15px; display: none;" id="newInstStockDiv">
        <label>Select Unit / IMEI</label>
        <select id="newInstStockUnit" class="input">
        </select>
      </div>

      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Actual Price (Base)</label>
        <input type="number" id="newInstBasePrice" class="input" readonly>
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Interest (%)</label>
        <input type="number" id="newInstPercentage" class="input" value="0" min="0" oninput="calcNewInstallment()">
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Total Amount</label>
        <input type="number" id="newInstTotal" class="input" oninput="calcNewInstallment()">
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Advance Payment (Down Payment)</label>
        <input type="number" id="newInstAdvance" class="input" value="0" oninput="calcNewInstallment()">
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Remaining Amount</label>
        <input type="number" id="newInstRemaining" class="input" readonly>
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Number of Months</label>
        <input type="number" id="newInstMonths" class="input" value="1" min="1" oninput="calcNewInstallment()">
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Monthly Installment</label>
        <input type="number" id="newInstMonthlyAmount" class="input" readonly>
      </div>
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Payment Day (1-30)</label>
        <input type="number" id="newInstPaymentDay" class="input" value="10" min="1" max="30" required>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeNewInstallmentModal()">Cancel</button>
      <button class="btn btn-primary" id="btnSubmitNewInst" onclick="submitNewInstallment()">Create Installment</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/customers.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/products.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/sales.js') }}?v={{ time() }}"></script>
@endsection
