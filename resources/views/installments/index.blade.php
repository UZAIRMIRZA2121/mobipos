@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<style>
  .ts-control { border-radius: 8px; border: 1px solid var(--border); padding: 8px 12px; font-size: 14px; min-height: 42px; background: #fff; }
  .ts-control > input { font-family: inherit; font-size: 14px; }
  .ts-dropdown { border-radius: 8px; border: 1px solid var(--border); box-shadow: 0 4px 6px rgba(0,0,0,0.1); margin-top: 4px; }
  .ts-dropdown .option { padding: 10px 12px; }
  .ts-dropdown .active { background-color: #f3f4f6; color: var(--text); }
  .tom-select-wrap { flex: 1; }
</style>
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

        <!-- Card 4 -->
        <div class="card" style="flex:1; min-width:200px; border-radius: 12px; padding: 20px; display:flex; flex-direction:row; align-items:center; gap:15px; border:none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #e0e7ff; color: #4338ca; display:flex; justify-content:center; align-items:center; font-size: 24px; font-weight:bold;">
                $
            </div>
            <div>
                <h4 style="margin:0; font-size: 18px; font-weight:700; color: #111827;">PKR {{ number_format($sumActualProfit ?? 0, 2) }}</h4>
                <p style="margin:0; font-size: 13px; color: #6b7280;">Actual Profit</p>
            </div>
        </div>

        <!-- Card 5 -->
        <div class="card" style="flex:1; min-width:200px; border-radius: 12px; padding: 20px; display:flex; flex-direction:row; align-items:center; gap:15px; border:none; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <div style="width: 50px; height: 50px; border-radius: 12px; background: #fce7f3; color: #be185d; display:flex; justify-content:center; align-items:center; font-size: 24px; font-weight:bold;">
                $
            </div>
            <div>
                <h4 style="margin:0; font-size: 18px; font-weight:700; color: #111827;">PKR {{ number_format($sumPendingProfit ?? 0, 2) }}</h4>
                <p style="margin:0; font-size: 13px; color: #6b7280;">Pending Profit</p>
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
                            <th>Profit</th>
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
                                $paidThisMonth = 0;
                                
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

                                $firstItem = $installment->order ? $installment->order->items->first() : null;
                                $imeiStr = $firstItem ? $firstItem->imeis : null;
                                if ($imeiStr && is_string($imeiStr) && strpos($imeiStr, '[') === 0) {
                                    $parsed = json_decode($imeiStr, true);
                                    if (is_array($parsed)) {
                                        $imeiStr = implode(', ', $parsed);
                                    }
                                }
                                $editData = [
                                    'id' => $installment->id,
                                    'customer_name' => $installment->customer->name ?? 'Unknown',
                                    'product_name' => $firstItem->product->name ?? 'Unknown Product',
                                    'imei' => $imeiStr ?: 'N/A',
                                    'actual_price' => $installment->actual_price,
                                    'interest_percentage' => $installment->interest_percentage,
                                    'total_amount' => $installment->total_amount,
                                    'down_payment' => $installment->down_payment,
                                    'months' => $installment->order->installment_months ?? 1,
                                    'payment_day' => $installment->payment_day
                                ];
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
                                    <div>Act: PKR {{ number_format($installment->actual_profit ?? 0, 2) }}</div>
                                    <div style="font-size: 11px; color: #6b7280; margin-top: 2px;">Pend: PKR {{ number_format($installment->pending_profit ?? 0, 2) }}</div>
                                </td>
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
                                        <button type="button" class="action-btn edit" style="background:#e0f2fe; color:#0284c7; border:none; cursor:pointer;" data-edit='{{ json_encode($editData, JSON_HEX_APOS) }}' onclick="openEditInstallmentModal(this)" title="Edit Installment Setup">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        <button type="button" class="action-btn delete" style="background:#fee2e2; color:#dc2626; border:none; cursor:pointer;" onclick="deleteInstallment({{ $installment->order_id }})" title="Delete Installment Setup">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">No installment plans found.</td>
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

    let instCustSelect, instProdSelect, instStockSelect;

    // New Installment Modal Logic
    function openNewInstallmentModal() {
        const customers = store.get('customers', []);
        const products = store.get('products', []);
        
        const custSelect = document.getElementById('newInstCustomer');
        custSelect.innerHTML = '<option value="">Select Customer</option>' + 
            customers.map(c => `<option value="${c.id}">${c.name} (${c.phone || ''})</option>`).join('');
            
        const prodSelect = document.getElementById('newInstProduct');
        prodSelect.innerHTML = '<option value="">Select Product</option>' + 
            products.map(p => `<option value="${p.id}" data-price="${p.sale}" data-type="${p.type}" data-custom="${p.code || ''} ${p.barcode || ''}">${p.code ? '[' + p.code + '] ' : ''}${p.name} - PKR ${p.sale}</option>`).join('');

        if (instCustSelect) instCustSelect.destroy();
        if (instProdSelect) instProdSelect.destroy();
        if (instStockSelect) { instStockSelect.destroy(); instStockSelect = null; }

        instCustSelect = new TomSelect("#newInstCustomer", {
            create: false,
            sortField: { field: "text", direction: "asc" }
        });

        instProdSelect = new TomSelect("#newInstProduct", {
            create: false,
            searchField: ['text', 'custom'],
            sortField: { field: "text", direction: "asc" }
        });

        instProdSelect.on('change', function() {
            onNewInstProductChange();
        });

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
        const prodId = document.getElementById('newInstProduct').value;
        if (!prodId) {
            document.getElementById('newInstBasePrice').value = 0;
            document.getElementById('newInstStockDiv').style.display = 'none';
            if (instStockSelect) { instStockSelect.destroy(); instStockSelect = null; }
            calcNewInstallment();
            return;
        }

        const products = store.get('products', []);
        const prod = products.find(p => p.id == prodId);
        
        const price = prod ? parseFloat(prod.sale || 0) : 0;
        document.getElementById('newInstBasePrice').value = price;
        
        // Handle Stock Units (IMEI)
        const stockSelect = document.getElementById('newInstStockUnit');
        if (prod && prod.stock_units && prod.stock_units.length > 0) {
            stockSelect.innerHTML = '<option value="">Select Unit (IMEI/Serial)</option>' + 
                prod.stock_units.filter(s => s.status === 'available').map(s => `<option value="${s.id}">${s.imeis || 'Unknown IMEI'}</option>`).join('');
            document.getElementById('newInstStockDiv').style.display = 'block';
            
            if (instStockSelect) instStockSelect.destroy();
            instStockSelect = new TomSelect("#newInstStockUnit", {
                create: false,
                sortField: { field: "text", direction: "asc" }
            });
        } else {
            stockSelect.innerHTML = '';
            document.getElementById('newInstStockDiv').style.display = 'none';
            if (instStockSelect) { instStockSelect.destroy(); instStockSelect = null; }
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

        let advanceIsFirst = document.getElementById('newAdvanceIsFirst');
        let monthly = 0;
        if (advanceIsFirst && advanceIsFirst.checked) {
            monthly = (total / months).toFixed(2);
        } else {
            monthly = (remaining / months).toFixed(2);
        }
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

    // Listen for new product creation to auto-select it
    document.addEventListener('productSaved', function(e) {
        const products = store.get('products', []);
        
        let newProductId = null;
        if (e.detail && e.detail.product && e.detail.product.id) {
            newProductId = e.detail.product.id;
        } else if (e.detail && e.detail.id) {
            newProductId = e.detail.id;
        } else if (products.length > 0) {
            // fallback to the most recently added product by max ID
            newProductId = products.reduce((max, p) => p.id > max.id ? p : max, products[0]).id;
        }

        if (instProdSelect) {
            instProdSelect.destroy();
        }
        
        const prodSelect = document.getElementById('newInstProduct');
        prodSelect.innerHTML = '<option value="">Select Product</option>' + 
            products.map(p => `<option value="${p.id}" data-price="${p.sale}" data-type="${p.type}" data-custom="${p.code || ''} ${p.barcode || ''}">${p.code ? '[' + p.code + '] ' : ''}${p.name} - PKR ${p.sale}</option>`).join('');
            
        instProdSelect = new TomSelect("#newInstProduct", {
            create: false,
            searchField: ['text', 'custom'],
            sortField: { field: "text", direction: "asc" }
        });

        instProdSelect.on('change', function() {
            onNewInstProductChange();
        });
        
        if (newProductId && document.getElementById('newInstallmentModal') && !document.getElementById('newInstallmentModal').classList.contains('hidden')) {
            instProdSelect.setValue(newProductId);
        }
    });
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
        <div style="display:flex; gap:4px; align-items:flex-start;">
          <div class="tom-select-wrap">
            <select id="newInstCustomer" class="input">
              <option value="">Select Customer</option>
            </select>
          </div>
          <button class="btn btn-primary" onclick="openCustModal()" title="Add Customer" style="padding:0 12px;font-size:18px;height:42px;">+</button>
        </div>
      </div>
      
      <div class="form-group" style="width: 100%; padding: 0 10px; margin-bottom: 15px;">
        <label>Product</label>
        <div style="display:flex; gap:4px; align-items:flex-start;">
          <div class="tom-select-wrap">
            <select id="newInstProduct" class="input" onchange="onNewInstProductChange()">
              <option value="">Select Product</option>
            </select>
          </div>
          <button class="btn btn-primary" onclick="editProduct()" title="Add Product" style="padding:0 12px;font-size:18px;height:42px;">+</button>
        </div>
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
        <div style="margin-top: 5px; font-size: 12px;">
          <label style="display:flex; align-items:center; gap:5px; cursor:pointer; color:#4b5563;">
            <input type="checkbox" id="newAdvanceIsFirst" onchange="calcNewInstallment()" checked>
            Advance is 1st Installment (Keep Monthly Rate Fixed)
          </label>
        </div>
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

<!-- Edit Installment Modal -->
<div class="modal-overlay hidden" id="editInstallmentModal">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>Edit Installment Setup</h3>
      <button class="modal-close" onclick="closeEditInstallmentModal()">×</button>
    </div>
    <div class="modal-body" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
      <input type="hidden" id="editInstId">
      
      <div class="form-group" style="width: 100%; padding: 0 10px; margin-bottom: 15px;">
        <label>Customer</label>
        <div id="editInstCustomerName" style="padding: 10px 12px; background: #f9fafb; border-radius: 8px; border: 1px solid var(--border); font-weight: 500;"></div>
      </div>
      
      <div class="form-group" style="width: 100%; padding: 0 10px; margin-bottom: 15px;">
        <label>Product</label>
        <div id="editInstProductName" style="padding: 10px 12px; background: #f9fafb; border-radius: 8px; border: 1px solid var(--border); font-weight: 500;"></div>
      </div>

      <div class="form-group" style="width: 100%; padding: 0 10px; margin-bottom: 15px; display: none;" id="editInstStockDiv">
        <label>Unit / IMEI</label>
        <div id="editInstImei" style="padding: 10px 12px; background: #f9fafb; border-radius: 8px; border: 1px solid var(--border); font-weight: 500;"></div>
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Actual Price (Base)</label>
        <input type="number" id="editInstBasePrice" class="input" readonly>
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Interest (%)</label>
        <input type="number" id="editInstPercentage" class="input" min="0" oninput="calcEditInstallment()">
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Total Amount</label>
        <input type="number" id="editInstTotal" class="input" oninput="calcEditInstallment()">
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Advance Payment (Down Payment)</label>
        <input type="number" id="editInstAdvance" class="input" readonly title="Cannot edit down payment after creation">
        <div style="margin-top: 5px; font-size: 12px;">
          <label style="display:flex; align-items:center; gap:5px; cursor:pointer; color:#4b5563;">
            <input type="checkbox" id="editAdvanceIsFirst" onchange="calcEditInstallment()" checked>
            Advance is 1st Installment
          </label>
        </div>
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Remaining Amount</label>
        <input type="number" id="editInstRemaining" class="input" readonly>
      </div>

      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Number of Months</label>
        <input type="number" id="editInstMonths" class="input" min="1" oninput="calcEditInstallment()">
      </div>

      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Monthly Installment</label>
        <input type="number" id="editInstMonthlyAmount" class="input" readonly>
      </div>
      
      <div class="form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
        <label>Payment Day (1-30)</label>
        <input type="number" id="editInstPaymentDay" class="input" min="1" max="31">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeEditInstallmentModal()">Cancel</button>
      <button class="btn btn-primary" id="btnSubmitEditInst" onclick="submitEditInstallment()">Save Changes</button>
    </div>
  </div>
</div>

<script>
    function openEditInstallmentModal(btn) {
        const data = JSON.parse(btn.getAttribute('data-edit'));
        document.getElementById('editInstId').value = data.id;
        document.getElementById('editInstCustomerName').textContent = data.customer_name;
        document.getElementById('editInstProductName').textContent = data.product_name;
        
        if (data.imei && data.imei !== 'N/A' && data.imei !== '[]' && data.imei !== 'null') {
            document.getElementById('editInstImei').textContent = data.imei;
            document.getElementById('editInstStockDiv').style.display = 'block';
        } else {
            document.getElementById('editInstStockDiv').style.display = 'none';
        }

        document.getElementById('editInstBasePrice').value = data.actual_price;
        document.getElementById('editInstPercentage').value = data.interest_percentage;
        document.getElementById('editInstTotal').value = data.total_amount;
        document.getElementById('editInstAdvance').value = data.down_payment;
        document.getElementById('editInstMonths').value = data.months;
        document.getElementById('editInstPaymentDay').value = data.payment_day;
        
        calcEditInstallment();
        document.getElementById('editInstallmentModal').classList.remove('hidden');
    }

    function calcEditInstallment() {
        const base = parseFloat(document.getElementById('editInstBasePrice').value || 0);
        const pct = parseFloat(document.getElementById('editInstPercentage').value || 0);
        let total = parseFloat(document.getElementById('editInstTotal').value || 0);
        
        if (document.activeElement.id === 'editInstPercentage' || document.activeElement.id === '') {
            total = base + (base * (pct / 100));
            document.getElementById('editInstTotal').value = total.toFixed(2);
        } else if (document.activeElement.id === 'editInstTotal') {
            total = parseFloat(document.getElementById('editInstTotal').value || 0);
            if(base > 0) {
                let newPct = ((total - base) / base) * 100;
                document.getElementById('editInstPercentage').value = newPct.toFixed(2);
            }
        }

        let advance = parseFloat(document.getElementById('editInstAdvance').value || 0);
        if (advance > total) {
            advance = total;
        }

        let remaining = total - advance;
        if(remaining < 0) remaining = 0;
        document.getElementById('editInstRemaining').value = remaining.toFixed(2);

        let months = parseInt(document.getElementById('editInstMonths').value || 1);
        if (months < 1) {
            months = 1;
            document.getElementById('editInstMonths').value = months;
        }

        let advanceIsFirst = document.getElementById('editAdvanceIsFirst');
        let monthly = 0;
        if (advanceIsFirst && advanceIsFirst.checked) {
            monthly = (total / months).toFixed(2);
        } else {
            monthly = (remaining / months).toFixed(2);
        }
        document.getElementById('editInstMonthlyAmount').value = monthly;
    }

    function closeEditInstallmentModal() {
        document.getElementById('editInstallmentModal').classList.add('hidden');
    }

    async function submitEditInstallment() {
        const id = document.getElementById('editInstId').value;
        const interest = document.getElementById('editInstPercentage').value;
        const months = document.getElementById('editInstMonths').value;
        const paymentDay = document.getElementById('editInstPaymentDay').value;
        
        const payload = {
            interest_percentage: interest,
            installment_months: months,
            payment_day: paymentDay
        };

        const btn = document.getElementById('btnSubmitEditInst');
        btn.disabled = true;
        btn.innerHTML = 'Saving...';

        try {
            await api(`/shop/installments/${id}`, 'PUT', payload);
            toast('Installment updated successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } catch (e) {
            toast(e.message || 'Error updating installment', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
        }
    }
</script>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/customers.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/products.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/sales.js') }}?v={{ time() }}"></script>
@endsection
