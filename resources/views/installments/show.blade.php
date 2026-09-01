@extends('layouts.app')

@section('content')
<main class="page-content">
<div class="page" style="padding: 20px; overflow-y: auto;">
    <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
        <div class="card" style="flex: 1; min-width: 300px; margin-bottom: 0;">
            <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                <h3 style="margin: 0;">Installment Plan Details - Order #{{ $installment->order_id }}</h3>
                <a href="{{ route('shop.installments.index') }}" class="btn btn-ghost">Back to List</a>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Total Amount</label>
                        <p>
                            <strong>PKR {{ number_format($installment->total_amount, 2) }}</strong>
                            @if($installment->interest_percentage > 0)
                                <br>
                                <span style="font-size: 12px; color: #6b7280;">Base: PKR {{ number_format($installment->actual_price, 2) }}</span>
                                <br>
                                <span style="font-size: 12px; color: #6b7280;">Interest: {{ $installment->interest_percentage }}%</span>
                            @endif
                        </p>
                    </div>
                    <div class="form-group">
                        <label>Down Payment (Advance)</label>
                        <p><strong>PKR {{ number_format($installment->down_payment, 2) }}</strong></p>
                    </div>
                    <div class="form-group">
                        <label>Agreed Monthly Installment</label>
                        <p><strong>PKR {{ number_format($installment->agreed_monthly_amount, 2) }}</strong></p>
                    </div>
                    <div class="form-group">
                        <label>Total Paid So Far</label>
                        <p><strong>PKR {{ number_format($totalPaid, 2) }}</strong></p>
                    </div>
                    <div class="form-group">
                        <label>Remaining Balance</label>
                        <p><strong style="color: {{ $remaining > 0 ? 'var(--danger)' : 'var(--success)' }};">PKR {{ number_format($remaining, 2) }}</strong></p>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <p>
                            <span class="badge {{ $installment->status === 'Completed' ? 'badge-success' : 'badge-warning' }}">
                                {{ $installment->status }}
                            </span>
                        </p>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1; margin-top: 10px;">
                        <label>Product(s) Detail</label>
                        <div style="margin-top: 5px;">
                            @if($installment->order && $installment->order->items)
                                @foreach($installment->order->items as $item)
                                    <span style="display: inline-block; background: #f3f4f6; padding: 6px 10px; border-radius: 4px; font-size: 13px; margin-right: 5px; margin-bottom: 5px;">
                                        <strong>{{ $item->product->name ?? 'Unknown Product' }}</strong>
                                        @if($item->product && $item->product->code)
                                            - Code: {{ $item->product->code }}
                                        @elseif($item->product && $item->product->barcode)
                                            - Code: {{ $item->product->barcode }}
                                        @endif
                                        @if(!empty($item->imeis))
                                            - IMEI: {{ $item->imeis }}
                                        @elseif(!empty($item->imei_number))
                                            - IMEI: {{ $item->imei_number }}
                                        @elseif($item->product && !empty($item->product->imei_serial))
                                            - IMEI: {{ $item->product->imei_serial }}
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" style="flex: 1; min-width: 300px; margin-bottom: 0;">
            <div class="card-header">
                <h3 style="margin: 0;">Customer Details</h3>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Name</label>
                        <p><strong>{{ $installment->customer->name ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <p><strong>{{ $installment->customer->phone ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="form-group">
                        <label>CNIC Number</label>
                        <p><strong>{{ $installment->customer->cnic_number ?? 'N/A' }}</strong></p>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Address</label>
                        <p><strong>{{ $installment->customer->address ?? 'N/A' }}</strong></p>
                    </div>
                    @if(!empty($installment->customer->cnic_front) || !empty($installment->customer->cnic_back))
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>CNIC Images</label>
                        <div style="display: flex; gap: 10px; margin-top: 5px;">
                            @if(!empty($installment->customer->cnic_front))
                                <a href="{{ asset('storage/' . $installment->customer->cnic_front) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $installment->customer->cnic_front) }}" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                                </a>
                            @endif
                            @if(!empty($installment->customer->cnic_back))
                                <a href="{{ asset('storage/' . $installment->customer->cnic_back) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $installment->customer->cnic_back) }}" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if(!empty($installment->customer->agreements_images) && is_array($installment->customer->agreements_images))
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Agreement Images</label>
                        <div style="display: flex; gap: 10px; margin-top: 5px; flex-wrap: wrap;">
                            @foreach($installment->customer->agreements_images as $img)
                                <a href="{{ asset('storage/' . $img) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $img) }}" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ccc;">
                                </a>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>



    <div class="pos-bottom-row" style="gap: 20px;">
        <!-- Left: Payment History -->
        <div class="card" style="flex: 2;">
            <div class="card-header">
                <h3 style="margin: 0;">Payment History</h3>
            </div>
            <div class="card-body" style="padding: 20px;">
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Notes</th>
                                <th style="width: 80px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Down Payment is conceptually the first payment -->
                            @if($installment->down_payment > 0)
                            <tr>
                                <td>{{ $installment->created_at->format('Y-m-d') }}</td>
                                <td>PKR {{ number_format($installment->down_payment, 2) }}</td>
                                <td>{{ ucfirst($installment->order->payment_method ?? 'cash') }}</td>
                                <td>Advance / Down Payment</td>
                                <td></td>
                            </tr>
                            @endif

                            <!-- Monthly Payments -->
                            @forelse($installment->payments as $payment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                                <td>PKR {{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>{{ $payment->notes }}</td>
                                <td>
                                    <div style="display:flex; gap:5px;">
                                        <button type="button" class="action-btn edit" style="background:#e0f2fe; color:#0284c7; border:none; cursor:pointer;" onclick="openEditPaymentModal({{ $payment->id }}, '{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}', {{ $payment->amount }}, '{{ addslashes($payment->notes) }}')" title="Edit Payment">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </button>
                                        <button type="button" class="action-btn delete" style="background:#fee2e2; color:#dc2626; border:none; cursor:pointer;" onclick="deletePayment({{ $payment->id }})" title="Delete Payment">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center" style="color:#6b7280;">No monthly payments received yet.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right: Receive Payment Form -->
        <div class="card" style="flex: 1;">
            <div class="card-header">
                <h3 style="margin: 0;">Receive Payment</h3>
            </div>
            <div class="card-body" style="padding: 20px;">
                @if($installment->status === 'Completed' || $remaining <= 0)
                    <div style="padding:20px; background:#d1fae5; color:#065f46; border-radius:8px; text-align:center;">
                        This installment plan is fully paid!
                    </div>
                @else
                    <form action="{{ route('shop.installments.addPayment', $installment->id) }}" method="POST">
                        @csrf
                        <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -10px;">
                            <div class="col-6 form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
                                <label>Payment Date</label>
                                <input type="date" name="payment_date" class="input" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6 form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
                                <label>Amount (PKR)</label>
                                <input type="number" name="amount" class="input" value="{{ $installment->agreed_monthly_amount }}" max="{{ $remaining }}" step="0.01" required>
                                <small style="color:#6b7280;">Max remaining: PKR {{ number_format($remaining, 2) }}</small>
                            </div>
                            <div class="col-6 form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
                                <label>Payment Method</label>
                                <select name="payment_method" class="input" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="online">Online Transfer</option>
                                </select>
                            </div>
                            <div class="col-6 form-group" style="width: 50%; padding: 0 10px; margin-bottom: 15px;">
                                <label>Notes (Optional)</label>
                                <input type="text" name="notes" class="input" placeholder="e.g. Month 1 Installment">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Record Payment</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
</main>

<!-- Edit Payment Modal -->
<div class="modal-overlay hidden" id="editPaymentModal">
  <div class="modal modal-sm">
    <div class="modal-header">
      <h3>Edit Payment</h3>
      <button class="modal-close" onclick="closeEditPaymentModal()">×</button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="editPaymentId">
      
      <div class="form-group" style="margin-bottom: 15px;">
        <label>Payment Date</label>
        <input type="date" id="editPaymentDate" class="input" required>
      </div>
      
      <div class="form-group" style="margin-bottom: 15px;">
        <label>Amount (PKR)</label>
        <input type="number" id="editPaymentAmount" class="input" min="1" step="0.01" required>
      </div>
      
      <div class="form-group" style="margin-bottom: 15px;">
        <label>Notes</label>
        <input type="text" id="editPaymentNotes" class="input">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeEditPaymentModal()">Cancel</button>
      <button class="btn btn-primary" id="btnSubmitEditPayment" onclick="submitEditPayment()">Save Changes</button>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
    function openEditPaymentModal(id, date, amount, notes) {
        document.getElementById('editPaymentId').value = id;
        document.getElementById('editPaymentDate').value = date;
        document.getElementById('editPaymentAmount').value = amount;
        document.getElementById('editPaymentNotes').value = notes;
        document.getElementById('editPaymentModal').classList.remove('hidden');
    }

    function closeEditPaymentModal() {
        document.getElementById('editPaymentModal').classList.add('hidden');
    }

    async function submitEditPayment() {
        const id = document.getElementById('editPaymentId').value;
        const date = document.getElementById('editPaymentDate').value;
        const amount = document.getElementById('editPaymentAmount').value;
        const notes = document.getElementById('editPaymentNotes').value;
        
        const payload = {
            payment_date: date,
            amount: amount,
            notes: notes
        };

        const btn = document.getElementById('btnSubmitEditPayment');
        btn.disabled = true;
        btn.innerHTML = 'Saving...';

        try {
            await api(`/shop/installments/payment/${id}`, 'PUT', payload);
            toast('Payment updated successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } catch (e) {
            toast(e.message || 'Error updating payment', 'danger');
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
        }
    }

    function deletePayment(id) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: "This payment will be reversed from your ledger/sales!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    processDelete(id);
                }
            });
        } else {
            if (confirm("Are you sure? This payment will be reversed from your ledger/sales!")) {
                processDelete(id);
            }
        }
    }

    async function processDelete(id) {
        try {
            await api(`/shop/installments/payment/${id}`, 'DELETE');
            toast('Payment reversed successfully!', 'success');
            setTimeout(() => window.location.reload(), 1000);
        } catch(e) {
            toast(e.message || 'Error deleting payment', 'danger');
        }
    }
</script>
@endsection
