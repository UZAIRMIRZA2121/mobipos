@extends('layouts.app')

@section('content')
<div class="page-content">
    <div class="page-header">
        <h1 class="page-title">Seller Commissions</h1>
    </div>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Seller</th>
                            <th>Store (Referred)</th>
                            <th>Package</th>
                            <th>Commission</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wallets as $wallet)
                        <tr>
                            <td>{{ $wallet->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                {{ $wallet->seller->name }}<br><small>{{ $wallet->seller->email }}</small>
                                @if($wallet->seller->payout_method)
                                    <div style="margin-top: 5px; font-size: 0.8rem; background: #f8fafc; padding: 5px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                        <strong>Payout:</strong> {{ $wallet->seller->payout_method }}<br>
                                        {{ $wallet->seller->payout_account_name }}<br>
                                        {{ $wallet->seller->payout_account_number }}
                                        @if($wallet->seller->payout_bank_name)<br>{{ $wallet->seller->payout_bank_name }}@endif
                                    </div>
                                @endif
                            </td>
                            <td>{{ $wallet->store->name }}<br><small>{{ $wallet->store->email }}</small></td>
                            <td>{{ $wallet->subscription && $wallet->subscription->package ? $wallet->subscription->package->name : 'N/A' }}</td>
                            <td style="font-weight:600; color:var(--primary)">PKR {{ number_format($wallet->c_amount, 2) }}</td>
                            <td>
                                @if($wallet->status === 'paid')
                                <span class="badge badge-success">Paid</span>
                                @else
                                <span class="badge badge-warning" style="background:#f59e0b; color:white;">Unpaid</span>
                                @endif
                            </td>
                            <td>
                                @if($wallet->status === 'unpaid')
                                <form action="{{ route('admin.wallets.mark-paid', $wallet->id) }}" method="POST" enctype="multipart/form-data" style="display:flex; flex-direction:column; gap:5px;" onsubmit="return confirm('Mark this commission as paid?');">
                                    @csrf
                                    <input type="file" name="receipt_image" accept="image/*" style="font-size: 0.75rem; padding: 2px;">
                                    <button type="submit" class="btn btn-sm btn-primary" style="align-self: flex-start;">Mark Paid</button>
                                </form>
                                @else
                                    @if($wallet->receipt_image)
                                        <a href="javascript:void(0)" onclick="showReceipt('{{ Storage::url($wallet->receipt_image) }}')" style="color: #4f46e5; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
                                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
                                            View Receipt
                                        </a>
                                    @else
                                        -
                                    @endif
                                @endif
                            </td>
                        </tr>
                        @endforeach
                        
                        @if($wallets->isEmpty())
                        <tr>
                            <td colspan="7" class="text-center" style="padding:20px;">No commissions recorded yet.</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function showReceipt(url) {
        Swal.fire({
            title: 'Payment Receipt',
            imageUrl: url,
            imageAlt: 'Receipt',
            imageWidth: '100%',
            width: 600,
            confirmButtonText: 'Close',
            confirmButtonColor: '#4f46e5'
        });
    }
</script>
@endsection
