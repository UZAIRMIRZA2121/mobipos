@extends('layouts.app')

@section('content')
<main class="page-content">

<!-- PRODUCTS -->
    <div class="page" id="page-products">
      <div class="card">
        <div class="card-header">
          <h3>Product Inventory</h3>
          <div class="header-actions">
            <input type="text" class="input input-sm" id="prodSearch" placeholder="Search products (Name/IMEI)..."/>
            <select class="input input-sm" id="prodTypeFilter">
                <option value="">All Types</option>
                <option value="mobile">Mobile</option>
                <option value="tablet">Tablet</option>
                <option value="laptop">Laptop</option>
                <option value="accessory">Accessory</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="openProductModal()">+ Add Product</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Image</th><th>Product Name</th><th>Type</th><th>Condition</th><th>Storage/Color</th><th>Sale Price</th><th>Status</th><th>Category</th><th>Actions</th></tr></thead>
            <tbody id="prodTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection
