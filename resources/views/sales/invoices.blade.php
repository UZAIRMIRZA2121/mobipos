@extends('layouts.app')

@section('content')
<main class="page-content">

<!-- INVOICES -->
    <div class="page" id="page-invoices">
      <div class="card">
        <div class="card-header">
          <h3>Invoice History</h3>
          <div class="header-actions">
            <input type="text" class="input input-sm" id="invoiceSearch" placeholder="Search invoice or customer..."/>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Invoice#</th><th>Customer</th><th>Items</th><th>Total</th><th>Paid</th><th>Due</th><th>Payment</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody id="invoicesTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection
