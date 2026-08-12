<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerLedger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerLedgerController extends Controller
{
    public function apiIndex(Customer $customer)
    {
        if ($customer->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ledgers = $customer->ledgers()->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
        return response()->json($ledgers);
    }

    public function store(Request $request, Customer $customer)
    {
        if ($customer->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'date' => 'required|date',
            'type' => 'required|string',
            'debit' => 'required|numeric|min:0',
            'credit' => 'required|numeric|min:0',
            'note' => 'nullable|string',
            'payment_proof' => 'nullable|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // Get the last balance
            $lastLedger = $customer->ledgers()->orderBy('date', 'desc')->orderBy('id', 'desc')->first();
            $previousBalance = $lastLedger ? $lastLedger->balance : 0;

            $debit = $request->debit;
            $credit = $request->credit;
            
            // Assuming balance = previousBalance + debit - credit
            // Positive balance means customer owes us (Debit)
            // Negative balance means we owe customer (Credit)
            $newBalance = $previousBalance + $debit - $credit;

            $proofPath = null;
            if ($request->hasFile('payment_proof')) {
                $proofPath = $request->file('payment_proof')->store('ledgers', 'public');
            }

            $ledger = $customer->ledgers()->create([
                'user_id' => Auth::id(),
                'date' => $request->date,
                'type' => $request->type,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $newBalance,
                'note' => $request->note,
                'payment_proof' => $proofPath,
            ]);

            DB::commit();
            return response()->json(['message' => 'Ledger entry added successfully', 'ledger' => $ledger]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error adding ledger entry: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Customer $customer, CustomerLedger $ledger)
    {
        if ($customer->user_id !== Auth::id() || $ledger->customer_id !== $customer->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $deletedDate = $ledger->date;
            $deletedId = $ledger->id;
            
            if ($ledger->payment_proof) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($ledger->payment_proof);
            }
            
            $ledger->delete();

            // Recalculate balances for ledgers after this one
            $subsequentLedgers = $customer->ledgers()
                ->where(function($query) use ($deletedDate, $deletedId) {
                    $query->where('date', '>', $deletedDate)
                          ->orWhere(function($q) use ($deletedDate, $deletedId) {
                              $q->where('date', '=', $deletedDate)
                                ->where('id', '>', $deletedId);
                          });
                })
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            // Find the balance before the deleted ledger
            $previousLedger = $customer->ledgers()
                ->where(function($query) use ($deletedDate, $deletedId) {
                    $query->where('date', '<', $deletedDate)
                          ->orWhere(function($q) use ($deletedDate, $deletedId) {
                              $q->where('date', '=', $deletedDate)
                                ->where('id', '<', $deletedId);
                          });
                })
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();

            $currentBalance = $previousLedger ? $previousLedger->balance : 0;

            foreach ($subsequentLedgers as $subLedger) {
                $currentBalance = $currentBalance + $subLedger->debit - $subLedger->credit;
                $subLedger->update(['balance' => $currentBalance]);
            }

            DB::commit();
            return response()->json(['message' => 'Ledger entry deleted successfully', 'new_customer_balance' => $currentBalance]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error deleting entry: ' . $e->getMessage()], 500);
        }
    }
    public function printLedger(Customer $customer)
    {
        if ($customer->user_id !== Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $ledgers = $customer->ledgers()->orderBy('date', 'asc')->orderBy('id', 'asc')->get();
        $invoiceSettings = \App\Models\InvoiceSetting::where('user_id', Auth::id())->first();
        
        return view('pos.print_ledger', compact('customer', 'ledgers', 'invoiceSettings'));
    }
}
