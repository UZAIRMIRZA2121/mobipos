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
        $query = Installment::with(['customer', 'order.items.product', 'payments'])->where('user_id', Auth::id())->latest();

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

        $sumActualProfit = 0;
        $sumPendingProfit = 0;

        foreach ($installments as $installment) {
            $totalCost = $installment->order ? $installment->order->items->sum(function($item) {
                return $item->buy_price * $item->qty;
            }) : 0;
            
            $expectedProfit = $installment->total_amount - $totalCost;
            $totalPaid = $installment->down_payment + $installment->payments->sum('amount');
            
            $profitMargin = $installment->total_amount > 0 ? ($expectedProfit / $installment->total_amount) : 0;
            $actualProfit = $totalPaid * $profitMargin;
            $pendingProfit = $expectedProfit - $actualProfit;
            
            $installment->actual_profit = $actualProfit;
            $installment->pending_profit = $pendingProfit;
            $installment->expected_profit = $expectedProfit;
            
            $sumActualProfit += $actualProfit;
            $sumPendingProfit += $pendingProfit;
        }

        return view('installments.index', compact('installments', 'sumTotalAmount', 'sumTotalPaid', 'sumUnpaidAmount', 'sumActualProfit', 'sumPendingProfit'));
    }

    public function show($id)
    {
        $installment = Installment::with(['customer', 'order', 'payments'])->where('user_id', Auth::id())->findOrFail($id);
        
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

        $installment = Installment::where('user_id', Auth::id())->findOrFail($id);

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

            if ($installment->customer && !empty($installment->customer->phone)) {
                $remaining = max(0, $installment->total_amount - $totalPaid);
                $msg = "Hello {$installment->customer->name}, we have received your installment payment of PKR {$request->amount} for Order #{$installment->order_id}.\n";
                $msg .= "Remaining Balance: PKR {$remaining}\n";
                $msg .= "Thank you!";
                \App\Services\UltramsgService::sendMessage(Auth::id(), $installment->customer->phone, $msg);
            }

            return redirect()->route('installments.show', $installment->id)->with('success', 'Payment added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error adding payment: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $installment = Installment::with(['customer', 'order.items.product', 'payments'])->where('user_id', Auth::id())->findOrFail($id);
        
        $totalPaid = $installment->down_payment + $installment->payments->sum('amount');
        $remaining = $installment->total_amount - $totalPaid;
        
        return view('installments.print', compact('installment', 'totalPaid', 'remaining'));
    }
    public function update(Request $request, $id)
    {
        $installment = Installment::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'interest_percentage' => 'required|numeric|min:0',
            'installment_months' => 'required|integer|min:1',
            'payment_day' => 'required|integer|min:1|max:31'
        ]);

        $basePrice = $installment->actual_price;
        $interest = $request->interest_percentage;
        $total = $basePrice + ($basePrice * ($interest / 100));
        
        $totalPaid = $installment->down_payment + $installment->payments()->sum('amount');
        $remainingForInstallments = $total - $installment->down_payment;
        
        if ($remainingForInstallments < 0) {
            $remainingForInstallments = 0;
        }

        $monthlyAmount = $remainingForInstallments / $request->installment_months;

        $installment->update([
            'interest_percentage' => $interest,
            'total_amount' => $total,
            'agreed_monthly_amount' => $monthlyAmount,
            'payment_day' => $request->payment_day
        ]);

        if ($installment->order) {
            $installment->order->update([
                'total' => $total,
                'due_amount' => max(0, $total - $totalPaid),
                'installment_months' => $request->installment_months,
                'installment_monthly_amount' => $monthlyAmount,
                'installment_interest_percentage' => $interest,
                'installment_payment_day' => $request->payment_day
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Installment updated successfully']);
    }

    public function updatePayment(Request $request, $paymentId)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string'
        ]);

        $payment = InstallmentPayment::findOrFail($paymentId);
        $installment = Installment::where('user_id', Auth::id())->findOrFail($payment->installment_id);

        try {
            DB::beginTransaction();

            $oldAmount = $payment->amount;
            $newAmount = $request->amount;
            $difference = $newAmount - $oldAmount;

            $payment->update([
                'amount' => $newAmount,
                'payment_date' => $request->payment_date,
                'notes' => $request->notes,
            ]);

            if ($difference != 0) {
                // Adjust Ledger
                $lastLedger = CustomerLedger::where('customer_id', $installment->customer_id)
                    ->orderBy('date', 'desc')
                    ->orderBy('id', 'desc')
                    ->first();
                $previousBalance = $lastLedger ? $lastLedger->balance : 0;
                $newBalance = $previousBalance - $difference;

                CustomerLedger::create([
                    'customer_id' => $installment->customer_id,
                    'user_id' => Auth::id(),
                    'date' => now()->toDateString(),
                    'type' => 'Installment Payment Adjustment (Order #' . $installment->order_id . ')',
                    'debit' => $difference < 0 ? abs($difference) : 0,
                    'credit' => $difference > 0 ? $difference : 0,
                    'balance' => $newBalance,
                    'note' => 'Adjusted payment amount for ' . $payment->payment_date,
                    'payment_proof' => null
                ]);
            }

            // Update status
            $totalPaid = $installment->down_payment + $installment->payments()->sum('amount');
            if ($totalPaid >= $installment->total_amount) {
                $installment->update(['status' => 'Completed']);
            } else {
                $installment->update(['status' => 'Active']);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment updated successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroyPayment($paymentId)
    {
        $payment = InstallmentPayment::findOrFail($paymentId);
        $installment = Installment::where('user_id', Auth::id())->findOrFail($payment->installment_id);

        try {
            DB::beginTransaction();

            $amount = $payment->amount;
            
            // Reversal in Ledger
            $lastLedger = CustomerLedger::where('customer_id', $installment->customer_id)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->first();
            $previousBalance = $lastLedger ? $lastLedger->balance : 0;
            $newBalance = $previousBalance + $amount;

            CustomerLedger::create([
                'customer_id' => $installment->customer_id,
                'user_id' => Auth::id(),
                'date' => now()->toDateString(),
                'type' => 'Installment Payment Reversal (Order #' . $installment->order_id . ')',
                'debit' => $amount,
                'credit' => 0,
                'balance' => $newBalance,
                'note' => 'Payment reversed for ' . $payment->payment_date,
                'payment_proof' => null
            ]);

            $payment->delete();

            // Update status
            $totalPaid = $installment->down_payment + $installment->payments()->sum('amount');
            if ($totalPaid < $installment->total_amount) {
                $installment->update(['status' => 'Active']);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Payment reversed successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
