<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Expense;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index()
    {
        return view('shop.expenses');
    }

    public function apiIndex(Request $request)
    {
        $query = Expense::where('user_id', Auth::id())->with('user')->orderBy('expense_date', 'desc');
        
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('expense_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('expense_date', '<=', $request->end_date);
        }
        
        $expenses = $query->get();
        return response()->json($expenses);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();

        $expense = Expense::create($validated);
        return response()->json(['message' => 'Expense added successfully', 'expense' => $expense]);
    }

    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'description' => 'nullable|string',
        ]);

        $expense->update($validated);
        return response()->json(['message' => 'Expense updated successfully', 'expense' => $expense]);
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return response()->json(['message' => 'Expense deleted successfully']);
    }
}
