<?php

namespace App\Http\Controllers\Admin\Accounts;

use App\Http\Controllers\Controller;
use App\Models\CustomerLoan;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    /**
     * Display a listing of the loans.
     */
    public function index()
    {
        $loans = CustomerLoan::with('bankAccount')->latest()->paginate(10);
        $bankAccounts = BankAccount::all();

        return view('admin.accounts.loans.index', compact('loans', 'bankAccounts'));
    }

    /**
     * Store a newly created loan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference_number' => 'required|unique:customer_loans,reference_number',
            'customer_name' => 'required|string|max:255',
            'date' => 'required|date',
            'loan_due_date' => 'nullable|date',
            'paid_date' => 'nullable|date',
            'reason' => 'nullable|string',
            'amount' => 'required|numeric',
            'from_bank_account_id' => 'nullable|exists:bank_accounts,id',
            'status' => 'required|in:proceeding,given_to_customer,waiting_due_date,customer_paid',
        ]);

        $loan = CustomerLoan::create($validated);

        // Handle transactions
        if ($loan->status === 'given_to_customer' && $loan->from_bank_account_id) {
            $this->debitTransaction($loan);
        }

        if ($loan->status === 'customer_paid' && $loan->from_bank_account_id) {
            if (!$loan->paid_date) {
                $loan->paid_date = Carbon::today()->toDateString();
                $loan->save();
            }
            $this->creditTransaction($loan);
        }

        return redirect()->back()->with('success', 'Loan added successfully!');
    }

    /**
     * Update an existing loan.
     */
    public function update(Request $request, $id)
    {
        $loan = CustomerLoan::findOrFail($id);

        $validated = $request->validate([
            'reference_number' => 'required|unique:customer_loans,reference_number,' . $loan->id,
            'customer_name' => 'required|string|max:255',
            'date' => 'required|date',
            'loan_due_date' => 'nullable|date',
            'paid_date' => 'nullable|date',
            'reason' => 'nullable|string',
            'amount' => 'required|numeric',
            'from_bank_account_id' => 'nullable|exists:bank_accounts,id',
            'status' => 'required|in:proceeding,given_to_customer,waiting_due_date,customer_paid',
        ]);

        $oldStatus = $loan->status;
        $loan->update($validated);

        // Handle transactions based on status change
        if ($loan->status === 'given_to_customer' && $oldStatus !== 'given_to_customer' && $loan->from_bank_account_id) {
            $this->debitTransaction($loan);
        }

        if ($loan->status === 'customer_paid' && $oldStatus !== 'customer_paid' && $loan->from_bank_account_id) {
            if (!$loan->paid_date) {
                $loan->paid_date = Carbon::today()->toDateString();
                $loan->save();
            }
            $this->creditTransaction($loan);
        }

        return redirect()->back()->with('success', 'Loan updated successfully!');
    }

    /**
     * Remove a loan.
     */
    public function destroy($id)
    {
        $loan = CustomerLoan::findOrFail($id);
        $loan->delete();

        return redirect()->back()->with('success', 'Loan deleted successfully!');
    }

    /**
     * Update only the status of a loan.
     */
    public function updateStatus(Request $request, $id)
    {
        $loan = CustomerLoan::findOrFail($id);

        $request->validate([
            'status' => 'required|in:proceeding,given_to_customer,waiting_due_date,customer_paid',
        ]);

        $oldStatus = $loan->status;
        $loan->status = $request->status;

        if ($loan->status === 'customer_paid' && !$loan->paid_date) {
            $loan->paid_date = Carbon::today()->toDateString();
        }

        $loan->save();

        // Handle transactions
        if ($loan->from_bank_account_id) {
            if ($loan->status === 'given_to_customer' && $oldStatus !== 'given_to_customer') {
                $this->debitTransaction($loan);
            }
            if ($loan->status === 'customer_paid' && $oldStatus !== 'customer_paid') {
                $this->creditTransaction($loan);
            }
        }

        return redirect()->back()->with('success', 'Loan status updated successfully!');
    }

    /**
     * Debit transaction (loan given to customer).
     */
    protected function debitTransaction(CustomerLoan $loan)
    {
        $bank = BankAccount::find($loan->from_bank_account_id);
        if (!$bank) return;

        $bank->bank_balance -= $loan->amount;
        $bank->save();

        Transaction::create([
            'transaction_id' => Str::uuid(),
            'amount' => $loan->amount,
            'from_bank_id' => $loan->from_bank_account_id,
            'to_bank_id' => null,
            'type' => 'debited', // must match enum
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }

    /**
     * Credit transaction (customer paid back the loan).
     */
    protected function creditTransaction(CustomerLoan $loan)
    {
        $bank = BankAccount::find($loan->from_bank_account_id);
        if (!$bank) return;

        $bank->bank_balance += $loan->amount;
        $bank->save();

        Transaction::create([
            'transaction_id' => Str::uuid(),
            'amount' => $loan->amount,
            'from_bank_id' => null,
            'to_bank_id' => $loan->from_bank_account_id,
            'type' => 'credited', // must match enum
            'created_by' => Auth::id(),
            'updated_by' => Auth::id(),
        ]);
    }
}
