@extends('layouts.app')

@section('content')
<main class="page-content">
    <div class="page active" id="page-expenses">
        <div class="card">
            <div class="card-header">
                <h3>Store Expenses</h3>
                <div class="header-actions">
                    <input type="date" class="input input-sm" id="expStartDate" title="Start Date" onchange="renderExpenses()"/>
                    <input type="date" class="input input-sm" id="expEndDate" title="End Date" onchange="renderExpenses()"/>
                    <input type="text" class="input input-sm" id="expSearch" placeholder="Search expenses..."/>
                    <button class="btn btn-primary btn-sm" onclick="openExpenseModal()">+ Add Expense</button>
                </div>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Amount (PKR)</th>
                            <th>Added By</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="expensesTbody">
                        <tr><td colspan="6" class="empty-cell">Loading expenses...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/expenses.js') }}?v={{ time() }}"></script>
@endsection
