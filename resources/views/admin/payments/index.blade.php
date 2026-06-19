@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page-header" style="display:flex; justify-content:space-between; align-items:center;">
        <div>
            <h1 class="page-title">Payment Requests</h1>
            <p class="page-subtitle">Manage store subscription payments and approvals</p>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table" style="min-width: 600px;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Store (User)</th>
                        <th>Package</th>
                        <th>Status</th>
                        <th>Proof & Info</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                    <tr>
                        <td>{{ $req->created_at->format('M d, Y H:i') }}</td>
                        <td style="font-weight:500;">{{ $req->user->name ?? 'Unknown' }}</td>
                        <td>{{ $req->package->name ?? 'Unknown' }} (PKR {{ $req->package->price ?? 'Unknown' }})</td>
                        <td>
                            @if($req->status === 'approved')
                                <span class="badge badge-success" style="background:#d1fae5; color:#065f46; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">Approved</span>
                            @elseif($req->status === 'rejected')
                                <span class="badge badge-danger" style="background:#fee2e2; color:#b91c1c; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">Rejected</span>
                            @else
                                <span class="badge badge-warning" style="background:#fef3c7; color:#92400e; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem;">Pending</span>
                            @endif
                        </td>
                        <td>
                            <button class="btn btn-sm btn-ghost" style="padding: 2px 6px; font-size: 0.75rem; color: #4f46e5; border: 1px solid #4f46e5;" onclick="viewDetails({{ $req->id }}, '{{ asset('storage/' . $req->payment_proof) }}', `{{ htmlspecialchars($req->description) }}`, `{{ htmlspecialchars($req->admin_feedback) }}`)">
                                View Details
                            </button>
                        </td>
                        <td>
                            @if($req->status === 'pending')
                                <form id="approve-form-{{ $req->id }}" action="{{ route('admin.payments.approve', $req->id) }}" method="POST" style="display:inline;" onsubmit="confirmApprove(event, {{ $req->id }})">
                                    @csrf
                                    <button type="submit" class="btn btn-sm" style="background:#10b981; color:#fff; padding:4px 8px; border-radius:4px; font-size:0.75rem; border:none; cursor:pointer;">Approve</button>
                                </form>
                                <button class="btn btn-sm" style="background:#ef4444; color:#fff; padding:4px 8px; border-radius:4px; font-size:0.75rem; border:none; cursor:pointer; margin-left:5px;" onclick="openRejectModal({{ $req->id }})">Reject</button>
                            @else
                                <span style="font-size: 0.8rem; color: #94a3b8;">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                    @if($requests->isEmpty())
                    <tr>
                        <td colspan="6" class="empty-cell" style="text-align: center; padding: 20px;">No payment requests found.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Details Modal -->
<div class="modal-overlay hidden" id="detailsModalOverlay" style="z-index: 1000;">
  <div class="modal" style="max-width: 500px; text-align: center;">
    <div class="modal-header">
      <h3>Payment Details</h3>
      <button class="modal-close" onclick="closeDetailsModal()">×</button>
    </div>
    <div class="modal-body" style="padding: 20px; text-align: left;">
      <div style="margin-bottom: 15px;">
          <strong>User Description / Notes:</strong>
          <div id="detailsDescription" style="padding: 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; margin-top: 5px; font-size: 0.9rem; white-space: pre-wrap;"></div>
      </div>
      <div id="feedbackContainer" style="margin-bottom: 15px; display: none;">
          <strong>Admin Rejection Feedback:</strong>
          <div id="detailsFeedback" style="padding: 10px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 4px; margin-top: 5px; font-size: 0.9rem; color: #b91c1c; white-space: pre-wrap;"></div>
      </div>
      <div style="text-align: center;">
          <strong>Payment Screenshot:</strong><br><br>
          <img id="detailsImage" src="" alt="Payment Receipt" style="max-width: 100%; border-radius: 8px; border: 1px solid #ccc;"/>
      </div>
    </div>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay hidden" id="rejectModalOverlay" style="z-index: 1000;">
  <div class="modal" style="max-width: 400px;">
    <div class="modal-header">
      <h3>Reject Payment</h3>
      <button class="modal-close" onclick="closeRejectModal()">×</button>
    </div>
    <div class="modal-body">
      <form id="rejectForm" method="POST" action="">
        @csrf
        <div class="form-group">
            <label style="display: block; margin-bottom: 5px; font-weight: 500;">Reason for Rejection *</label>
            <textarea name="admin_feedback" class="input" rows="3" required placeholder="E.g., Amount mismatch, blurry screenshot, etc."></textarea>
            <small style="color: #64748b; font-size: 0.8rem; margin-top: 5px; display: block;">The user will see this message and can upload a new proof.</small>
        </div>
        <div class="modal-footer" style="margin-top: 20px;">
            <button type="button" class="btn btn-ghost" onclick="closeRejectModal()">Cancel</button>
            <button type="submit" class="btn btn-primary" style="background:#ef4444; border-color:#ef4444;">Reject Payment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
    function viewDetails(id, imgUrl, description, feedback) {
        document.getElementById('detailsImage').src = imgUrl;
        
        let descDiv = document.getElementById('detailsDescription');
        descDiv.textContent = description ? description : 'No description provided.';
        
        let fbContainer = document.getElementById('feedbackContainer');
        let fbDiv = document.getElementById('detailsFeedback');
        if (feedback) {
            fbDiv.textContent = feedback;
            fbContainer.style.display = 'block';
        } else {
            fbContainer.style.display = 'none';
        }

        document.getElementById('detailsModalOverlay').classList.remove('hidden');
    }
    
    function closeDetailsModal() {
        document.getElementById('detailsModalOverlay').classList.add('hidden');
    }

    function openRejectModal(id) {
        document.getElementById('rejectForm').action = '/admin/payments/' + id + '/reject';
        document.getElementById('rejectModalOverlay').classList.remove('hidden');
    }
    
    function closeRejectModal() {
        document.getElementById('rejectModalOverlay').classList.add('hidden');
    }

    function confirmApprove(event, id) {
        event.preventDefault();
        Swal.fire({
            title: 'Approve Payment?',
            text: "This will instantly activate the store's subscription.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('approve-form-' + id).submit();
            }
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection
