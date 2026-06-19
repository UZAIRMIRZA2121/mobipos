@extends('layouts.app')

@section('content')
<main class="page-content">

    <div class="page" id="page-categories">
      <div class="card">
        <div class="card-header">
          <h3>Category Management</h3>
          <div class="header-actions">
            <input type="text" class="input input-sm" id="catSearch" placeholder="Search categories..."/>
            <button class="btn btn-primary btn-sm" onclick="openCatModal()">+ Add Category</button>
          </div>
        </div>
        <div class="table-wrap">
          <table class="table">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Color Code</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="catTbody"></tbody>
          </table>
        </div>
      </div>
    </div>

</main>
@endsection
