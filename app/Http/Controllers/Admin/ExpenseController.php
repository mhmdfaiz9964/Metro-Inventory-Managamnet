<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Cheques;
use Illuminate\Support\Str;
use Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('bankAccount')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('paid_by', 'like', "%{$search}%");
            });
        }

        if ($request->filled('bank')) {
            $query->where('bank_account_id', $request->bank);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $banks = BankAccount::where('status', 1)->get();

        return view('admin.expenses.index', compact('expenses', 'banks'));
    }

    public function table(Request $request)
    {
        $query = Expense::with('bankAccount')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                  ->orWhere('paid_by', 'like', "%{$search}%");
            });
        }

        if ($request->filled('bank')) {
            $query->where('bank_account_id', $request->bank);
        }

        $expenses = $query->paginate(20)->withQueryString();
        $banks = BankAccount::where('status', 1)->get();

        return view('admin.expenses.table', compact('expenses', 'banks'));
    }

    public function create()
    {
        $banks = BankAccount::where('status', 1)->get();
        return view('admin.expenses.create', compact('banks'));
    }

    public function store(Request $request)
    {
        // Conditional validation
        $rules = [
            'reason' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'paid_by' => 'required|string',
            'payment_method' => 'required|in:Cash,Fund Transfer,Cheque',
        ];

        if ($request->payment_method === 'Fund Transfer' || $request->payment_method === 'Cheque') {
            $rules['bank_account_id'] = 'required|exists:bank_accounts,id';
        }

        if ($request->payment_method === 'Cheque') {
            $rules['cheque_no'] = 'required|string';
            $rules['cheque_date'] = 'required|date';
        }

        $request->validate($rules);

        $bank = null;
        if ($request->payment_method === 'Fund Transfer' || $request->payment_method === 'Cheque') {
            $bank = BankAccount::findOrFail($request->bank_account_id);
            if ($bank->bank_balance < $request->amount) {
                return back()->withErrors(['amount' => 'Insufficient bank balance.'])->withInput();
            }
        }

        // Create Expense
        $expenseData = $request->only(['reason','date','amount','paid_by','payment_method','bank_account_id']);
        $expense = Expense::create($expenseData);

        // Deduct bank balance if applicable
        if ($bank) {
            $bank->decrement('bank_balance', $request->amount);
        }

        // Create Transaction
        Transaction::create([
            'transaction_id' => 'EXP' . str_pad($expense->id, 6, '0', STR_PAD_LEFT),
            'amount' => $request->amount,
            'from_bank_id' => $bank->id ?? null,
            'to_bank_id' => null,
            'type' => 'debited',
            'created_by' => Auth::id(),
        ]);

        // Create Cheque if payment method is Cheque
        if ($request->payment_method === 'Cheque') {
            Cheques::create([
                'reason' => $request->reason,
                'type' => 'debit',
                'note' => 'Expense via cheque',
                'cheque_date' => $request->cheque_date,
                'cheque_bank' => $request->bank_account_id,
                'amount' => $request->amount,
                'created_by' => Auth::id(),
                'status' => 'pending',
                'cheque_no' => $request->cheque_no,
                'paid_to' => $request->paid_by,
            ]);
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense added successfully.');
    }

    public function edit(Expense $expense)
    {
        $banks = BankAccount::where('status', 1)->get();
        return view('admin.expenses.edit', compact('expense', 'banks'));
    }

    public function update(Request $request, Expense $expense)
    {
        // Conditional validation
        $rules = [
            'reason' => 'required|string',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'paid_by' => 'required|string',
            'payment_method' => 'required|in:Cash,Fund Transfer,Cheque',
        ];

        if ($request->payment_method === 'Fund Transfer' || $request->payment_method === 'Cheque') {
            $rules['bank_account_id'] = 'required|exists:bank_accounts,id';
        }

        if ($request->payment_method === 'Cheque') {
            $rules['cheque_no'] = 'required|string';
            $rules['cheque_date'] = 'required|date';
        }

        $request->validate($rules);

        $oldAmount = $expense->amount;
        $bank = null;

        if ($request->payment_method === 'Fund Transfer' || $request->payment_method === 'Cheque') {
            $bank = BankAccount::findOrFail($request->bank_account_id);
            $balanceDiff = $request->amount - $oldAmount;
            if ($balanceDiff > 0 && $bank->bank_balance < $balanceDiff) {
                return back()->withErrors(['amount' => 'Insufficient bank balance for update.'])->withInput();
            }
            if ($balanceDiff != 0) {
                $bank->decrement('bank_balance', max($balanceDiff, 0));
                $bank->increment('bank_balance', max(-$balanceDiff, 0));
            }
        }

        $expense->update($request->only(['reason','date','amount','paid_by','payment_method','bank_account_id']));

        // Update Transaction
        $transaction = Transaction::where('transaction_id', 'EXP' . str_pad($expense->id, 6, '0', STR_PAD_LEFT))->first();
        if ($transaction) {
            $transaction->update([
                'amount' => $request->amount,
                'from_bank_id' => $bank->id ?? null,
            ]);
        }

        // Update or create Cheque record
        if ($request->payment_method === 'Cheque') {
            $cheque = Cheques::where('cheque_no', $request->cheque_no)->first();
            if ($cheque) {
                $cheque->update([
                    'reason' => $request->reason,
                    'cheque_date' => $request->cheque_date,
                    'cheque_bank' => $request->bank_account_id,
                    'amount' => $request->amount,
                    'paid_to' => $request->paid_by,
                ]);
            } else {
                Cheques::create([
                    'reason' => $request->reason,
                    'type' => 'debit',
                    'note' => 'Expense via cheque',
                    'cheque_date' => $request->cheque_date,
                    'cheque_bank' => $request->bank_account_id,
                    'amount' => $request->amount,
                    'created_by' => Auth::id(),
                    'status' => 'pending',
                    'cheque_no' => $request->cheque_no,
                    'paid_to' => $request->paid_by,
                ]);
            }
        }

        return redirect()->route('admin.expenses.index')->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense)
    {
        $bank = null;
        if ($expense->payment_method === 'Fund Transfer' || $expense->payment_method === 'Cheque') {
            $bank = BankAccount::findOrFail($expense->bank_account_id);
            $bank->increment('bank_balance', $expense->amount);
        }

        // Delete Transaction
        Transaction::where('transaction_id', 'EXP' . str_pad($expense->id, 6, '0', STR_PAD_LEFT))->delete();

        // Delete Cheque if exists
        if ($expense->payment_method === 'Cheque') {
            Cheques::where('cheque_no', $expense->cheque_no ?? null)->delete();
        }

        $expense->delete();

        return redirect()->route('admin.expenses.index')->with('success', 'Expense deleted successfully.');
    }
}
