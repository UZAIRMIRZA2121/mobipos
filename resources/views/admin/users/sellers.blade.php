@extends('layouts.app')

@section('content')
<main class="page-content">

    <div class="page" id="page-admin-sellers">
        @if(session('success'))
            <div style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                {{ session('success') }}
            </div>
        @endif

        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">
                <h3>Seller Accounts</h3>
                <div class="header-actions">
                    <input type="text" class="input input-sm userSearch" placeholder="Search sellers..."/>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Payout Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody class="userTbody">
                        @foreach($sellers as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->payout_method ? $user->payout_method . ' (' . $user->payout_account_name . ')' : 'Not Set' }}</td>
                            <td>
                                <button class="btn btn-ghost btn-sm" onclick="openEditUserModal({{ json_encode($user) }})">
                                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                        @if($sellers->isEmpty())
                        <tr><td colspan="4" style="text-align: center; padding: 15px; color: #64748b;">No seller accounts found.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<!-- Edit User Modal -->
<div class="modal-overlay hidden" id="editUserModalOverlay">
  <div class="modal" style="max-width: 500px;">
    <div class="modal-header">
      <h3 id="editUserModalTitle">Edit Seller</h3>
      <button class="modal-close" onclick="closeEditUserModal()">×</button>
    </div>
    <div class="modal-body">
      <form id="editUserForm" method="POST" action="">
        @csrf
        @method('PUT')
        
        <div class="form-grid">
          <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" id="editUserName" class="input" required/>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" id="editUserEmail" class="input" required/>
          </div>
          <input type="hidden" name="type" id="editUserType" value="seller"/>
          
          <div class="form-group form-full">
            <label>New Password (leave blank to keep current)</label>
            <input type="password" name="password" minlength="8" class="input"/>
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeEditUserModal()">Cancel</button>
      <button class="btn btn-primary" onclick="document.getElementById('editUserForm').submit()">Save Changes</button>
    </div>
  </div>
</div>

<script>
    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = '/admin/users/' + user.id;
        document.getElementById('editUserName').value = user.name;
        document.getElementById('editUserEmail').value = user.email;
        document.getElementById('editUserType').value = user.type;
        
        document.getElementById('editUserModalOverlay').classList.remove('hidden');
    }

    function closeEditUserModal() {
        document.getElementById('editUserModalOverlay').classList.add('hidden');
    }

    // Independent search filtering
    document.querySelectorAll('.userSearch').forEach(function(searchInput) {
        searchInput.addEventListener('input', function(e) {
            let term = e.target.value.toLowerCase();
            let tbody = e.target.closest('.card').querySelector('.userTbody');
            if (tbody) {
                let rows = tbody.querySelectorAll('tr');
                rows.forEach(row => {
                    let text = row.innerText.toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });
            }
        });
    });
</script>

@endsection