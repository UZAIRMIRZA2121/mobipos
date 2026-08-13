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
                                <td>PKR {{ number_format($installment->total_amount, 2) }}</td>
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
</script>
@endsection
