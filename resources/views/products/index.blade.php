@extends('layouts.app')

@section('content')
<main class="page-content">

<!-- PRODUCTS -->
    <div class="page" id="page-products">
      <div class="card">
        <div class="card-header">
          <h3>Product Inventory</h3>
          <div class="header-actions">
            <input type="text" class="input input-sm" id="prodSearch" placeholder="Search products (Code)..." oninput="renderProducts()"/>
            <select class="input input-sm" id="prodConditionFilter" onchange="renderProducts()">
                <option value="">All Conditions</option>
                <option value="new">New</option>
                <option value="used">Used</option>
                <option value="refurbished">Refurbished</option>
            </select>
            @if((Auth::user()->storeSetting->business_type ?? 'mobile') === 'mobile')
            <select class="input input-sm" id="prodTypeFilter" onchange="renderProducts()">
                <option value="">All Types</option>
                <option value="mobile">Mobile</option>
                <option value="tablet">Tablet</option>
                <option value="laptop">Laptop</option>
                <option value="accessory">Accessory</option>
            </select>
            @endif
            <select class="input input-sm" id="prodCategoryFilter" onchange="window.prodCurrentPage = 1; renderProducts()">
                <option value="">All Categories</option>
            </select>
            <button class="btn btn-primary btn-sm" onclick="openProductModal()">+ Add Product</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead><tr><th>Image</th><th>Product Name</th>@if((Auth::user()->storeSetting->business_type ?? 'mobile') === 'mobile')<th>Type</th>@endif<th>Condition</th><th>Storage/Color</th><th>Sale Price</th><th>Status</th><th>Category</th><th>Actions</th></tr></thead>
            <tbody id="prodTbody"></tbody>
          </table>
        </div>
        <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border-top: 1px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 13px; color: var(--text-muted);">Rows per page:</span>
                <select class="input input-sm" id="prodPerPage" onchange="window.prodCurrentPage = 1; window.prodPerPage = parseInt(this.value); renderProducts()" style="width: auto; padding-right: 30px;">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div id="prodPagination" style="display: flex; gap: 5px; align-items: center;"></div>
        </div>
      </div>
    </div>

</main>
@endsection

@section('scripts')
<script>
    window.globalVariations = {!! json_encode($variations ?? []) !!};
    window.globalAddons = {!! json_encode($addons ?? []) !!};
</script>
<script src="{{ asset('assets/js/products.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/categories.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/customers.js') }}?v={{ time() }}"></script>
@endsection
