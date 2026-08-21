@extends('layouts.app')

@section('content')
<div class="pos-container" style="padding: 20px;">
    <div class="card" style="margin-bottom: 20px;">
        <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
            <h3 style="margin: 0;">Installment Plan Details - Order #{{ $installment->order_id }}</h3>
            <a href="{{ route('shop.installments.index') }}" class="btn btn-ghost">Back to List</a>
        </div>
        <div class="card-body" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div class="form-group">
                    <label>Customer</label>
                    <p><strong>{{ $installment->customer->name ?? 'Unknown' }}</strong></p>
                </div>
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
                            </tr>
                            @endif

                            <!-- Monthly Payments -->
                            @forelse($installment->payments as $payment)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                                <td>PKR {{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>{{ $payment->notes }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center" style="color:#6b7280;">No monthly payments received yet.</td>
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
@endsection
