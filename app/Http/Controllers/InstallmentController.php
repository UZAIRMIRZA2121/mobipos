<?php

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\InstallmentPayment;
use App\Models\CustomerLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstallmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Installment::with(['customer', 'order', 'payments'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('status_filter')) {
            $statusFilter = $request->status_filter;
            if ($statusFilter === 'this_month_paid') {
                $query->whereHas('payments', function($q) {
                    $q->whereMonth('payment_date', now()->month)
                      ->whereYear('payment_date', now()->year);
                });
            } elseif ($statusFilter === 'this_month_unpaid') {
                $query->whereDoesntHave('payments', function($q) {
                    $q->whereMonth('payment_date', now()->month)
                      ->whereYear('payment_date', now()->year);
                })->where('status', '!=', 'Completed');
            }
        }

        $installments = $query->get();
        
        $sumTotalAmount = $installments->sum('total_amount');
        $sumTotalPaid = $installments->sum('down_payment') + $installments->sum(function($inst) {
            return $inst->payments->sum('amount');
        });
        $sumUnpaidAmount = $sumTotalAmount - $sumTotalPaid;

        return view('installments.index', compact('installments', 'sumTotalAmount', 'sumTotalPaid', 'sumUnpaidAmount'));
    }

    public function show($id)
    {
        $installment = Installment::with(['customer', 'order', 'payments'])->findOrFail($id);
        
        $totalPaid = $installment->down_payment + $installment->payments->sum('amount');
        $remaining = $installment->total_amount - $totalPaid;
        
        return view('installments.show', compact('installment', 'totalPaid', 'remaining'));
    }

    public function addPayment(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string'
        ]);

        $installment = Installment::findOrFail($id);

        try {
            DB::beginTransaction();

            $payment = InstallmentPayment::create([
                'installment_id' => $installment->id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
            ]);

            // Add to Customer Ledger
            $lastLedger = CustomerLedger::where('customer_id', $installment->customer_id)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
                
            $previousBalance = $lastLedger ? $lastLedger->balance : 0;
            $newBalance = $previousBalance - $request->amount; // payment reduces balance

            CustomerLedger::create([
                'customer_id' => $installment->customer_id,
                'user_id' => Auth::id(),
                'date' => $request->payment_date,
                'type' => 'Installment Payment (Order #' . $installment->order_id . ')',
                'debit' => 0,
                'credit' => $request->amount,
                'balance' => $newBalance,
                'note' => $request->notes ?? 'Installment payment received',
                'payment_proof' => null
            ]);

            // Check if fully paid
            $totalPaid = $installment->down_payment + $installment->payments()->sum('amount');
            if ($totalPaid >= $installment->total_amount) {
                $installment->update(['status' => 'Completed']);
            }

            DB::commit();

            return redirect()->route('installments.show', $installment->id)->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error adding payment: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $installment = Installment::with(['customer', 'order.items.product', 'payments'])->findOrFail($id);
        
        $totalPaid = $installment->down_payment + $installment->payments->sum('amount');
        $remaining = $installment->total_amount - $totalPaid;
        
        return view('installments.print', compact('installment', 'totalPaid', 'remaining'));
    }
}
