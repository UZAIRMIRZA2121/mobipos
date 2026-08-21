@extends('layouts.app')

@section('content')
<main class="page-content">

<!-- CUSTOMERS -->
    <div class="page" id="page-customers">
      <div class="card">
        <div class="card-header">
          <h3>Customers</h3>
          <div class="header-actions">
            <input type="text" class="input input-sm" id="custSearch" placeholder="Search customers..."/>
            <button class="btn btn-primary btn-sm" onclick="openCustModal()">+ Add Customer</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Name</th><th>Phone</th><th>CNIC Number</th><th>Address</th><th>CNIC Scans</th><th>Balance</th><th>Actions</th></tr></thead>
            <tbody id="custTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/customers.js') }}?v={{ time() }}"></script>
@endsection
