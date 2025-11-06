<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReceivingPayment;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ReceivingPaymentsController extends Controller
{
    /**
     * Display a listing of receiving payments.
     */
    public function index(Request $request)
    {
        $query = ReceivingPayment::with('bank');

        // Search filter
        if ($request->filled('search')) {
            $query->where('reason', 'like', "%{$request->search}%")->orWhere('paid_by', 'like', "%{$request->search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(10);
        $banks = BankAccount::all();

        return view('admin.Accounts.receiving_payments.index', compact('payments', 'banks'));
    }

    public function table(Request $request)
    {
        $query = ReceivingPayment::with('bank');

        // Search filter
        if ($request->filled('search')) {
            $query->where('reason', 'like', "%{$request->search}%")->orWhere('paid_by', 'like', "%{$request->search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest()->paginate(10);
        $banks = BankAccount::all();

        return view('admin.Accounts.receiving_payments.table', compact('payments', 'banks'));
    }

    /**
     * Show create form.
     */
    public function create()
    {
        $banks = BankAccount::all();
        return view('admin.Accounts.receiving_payments.create', compact('banks'));
    }

    /**
     * Store new payment.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'paid_date' => 'required|date',
            'paid_by' => 'required|string|max:255',
            'status' => 'required|in:paid,pending',
            'bank_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $payment = ReceivingPayment::create($request->all());

        // If status = paid → update bank balance & create transaction
        if ($payment->status === 'paid') {
            $bank = BankAccount::findOrFail($payment->bank_id);
            $bank->bank_balance += $payment->amount;
            $bank->save();

            Transaction::create([
                'transaction_id' => Str::uuid(), // auto unique id
                'cheque_id' => null,
                'created_by' => Auth::id(),
                'updated_by' => null,
                'amount' => $payment->amount,
                'from_bank_id' => null,
                'to_bank_id' => $payment->bank_id,
                'type' => 'credited',
            ]);
        }

        return redirect()->route('admin.receiving-payments.index')->with('success', 'Receiving payment added successfully!');
    }

    /**
     * Edit form.
     */
    public function edit(ReceivingPayment $receivingPayment)
    {
        $banks = BankAccount::all();
        return view('admin.Accounts.receiving_payments.edit', compact('receivingPayment', 'banks'));
    }

    /**
     * Update payment.
     */
    public function update(Request $request, ReceivingPayment $receivingPayment)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'paid_date' => 'required|date',
            'paid_by' => 'required|string|max:255',
            'status' => 'required|in:paid,pending',
            'bank_id' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $receivingPayment) {
            // Previous amount and bank (for adjustment)
            $oldAmount = $receivingPayment->amount;
            $oldBankId = $receivingPayment->bank_id;

            // Update the payment
            $receivingPayment->update($request->all());

            // Only proceed if status is paid
            if ($receivingPayment->status === 'paid') {
                $bank = BankAccount::findOrFail($receivingPayment->bank_id);

                // Calculate difference in bank balance
                if ($receivingPayment->id) {
                    $amountDiff = $receivingPayment->amount;

                    // If the bank changed or amount changed, adjust old bank balance
                    if ($oldBankId != $receivingPayment->bank_id) {
                        // Reduce old bank
                        if ($oldBankId) {
                            $oldBank = BankAccount::find($oldBankId);
                            if ($oldBank) {
                                $oldBank->bank_balance -= $oldAmount;
                                $oldBank->save();
                            }
                        }
                        $bank->bank_balance += $receivingPayment->amount;
                    } else {
                        // Same bank → just adjust difference
                        $diff = $receivingPayment->amount - $oldAmount;
                        $bank->bank_balance += $diff;
                    }
                } else {
                    $bank->bank_balance += $receivingPayment->amount;
                }

                $bank->save();

                // Update or create transaction
                $transaction = Transaction::where('to_bank_id', $receivingPayment->bank_id)->where('amount', $oldAmount)->where('type', 'credited')->where('created_by', Auth::id())->first();

                if ($transaction) {
                    // Update existing transaction
                    $transaction->update([
                        'amount' => $receivingPayment->amount,
                    ]);
                } else {
                    // Create new transaction if none exists
                    Transaction::create([
                        'transaction_id' => Str::uuid(),
                        'cheque_id' => null,
                        'created_by' => Auth::id(),
                        'updated_by' => null,
                        'amount' => $receivingPayment->amount,
                        'from_bank_id' => null,
                        'to_bank_id' => $receivingPayment->bank_id,
                        'type' => 'credited',
                    ]);
                }
            }
        });

        return redirect()->route('admin.receiving-payments.index')->with('success', 'Receiving payment updated successfully!');
    }
}
