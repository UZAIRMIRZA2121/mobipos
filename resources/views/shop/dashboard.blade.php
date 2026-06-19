@extends('layouts.app')

@section('content')
<main class="page-content">

    <div class="page" id="page-dashboard">
      <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 24px;">
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
          <div style="color: var(--text-muted); font-size: 13px; font-weight: 600;">TOTAL PRODUCTS</div>
          <div id="dashTotalProds" style="font-size: 28px; font-weight: 700; color: var(--primary);">0</div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
          <div style="color: var(--text-muted); font-size: 13px; font-weight: 600;">TOTAL INVENTORY VALUE</div>
          <div id="dashTotalValue" style="font-size: 28px; font-weight: 700; color: #10b981;">PKR 0</div>
        </div>
        <div class="stat-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
          <div style="color: var(--text-muted); font-size: 13px; font-weight: 600;">RECENT SALES</div>
          <div id="dashTotalSales" style="font-size: 28px; font-weight: 700; color: #f59e0b;">0</div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>Recently Added Products</h3>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Type</th>
                    <th>Condition</th>
                    <th>Sale Price</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="dashRecentProdsTbody">
                <tr><td colspan="5" class="empty-cell">No products added yet</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection
