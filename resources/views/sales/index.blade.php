@extends('layouts.app')

@section('content')
<main class="page-content">

<!-- SALES -->
    <div class="page" id="page-sales">
      <div class="card">
        <div class="card-header">
          <h3>Sales History</h3>
          <div class="header-actions">
            <input type="date" class="input input-sm" id="salesDateFilter"/>
            <input type="text" class="input input-sm" id="salesSearch" placeholder="Search customer..."/>
            <button class="btn btn-sm btn-ghost" onclick="exportSalesCSV()">Export CSV</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Invoice#</th><th>Customer</th><th>Items</th><th>Subtotal</th><th>Discount</th><th>Tax</th><th>Grand Total</th><th>Paid</th><th>Method</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody id="salesTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection
