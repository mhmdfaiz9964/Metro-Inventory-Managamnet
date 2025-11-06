<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cheques;
use App\Models\ReturnCheque;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\ReceivingCheque;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChequeController extends Controller
{
    /**
     * Display a listing of cheques with filters.
     */
    public function index(Request $request)
    {
        $query = Cheques::with(['bank', 'creator', 'approver']);

        if ($request->filled('cheque_no')) {
            $query->where('cheque_no', 'like', '%' . $request->cheque_no . '%');
        }

        if ($request->filled('bank_id')) {
            $query->where('cheque_bank', $request->bank_id);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('cheque_date', [$request->from_date, $request->to_date]);
        }

        $cheques = $query->latest()->paginate(10)->withQueryString();
        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.Accounts.cheques.index', compact('cheques', 'banks', 'users'));
    }

    public function table(Request $request)
    {
        $query = Cheques::with(['bank', 'creator', 'approver']);

        if ($request->filled('cheque_no')) {
            $query->where('cheque_no', 'like', '%' . $request->cheque_no . '%');
        }

        if ($request->filled('bank_id')) {
            $query->where('cheque_bank', $request->bank_id);
        }

        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('cheque_date', [$request->from_date, $request->to_date]);
        }

        $cheques = $query->latest()->paginate(10)->withQueryString();
        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.Accounts.cheques.table', compact('cheques', 'banks', 'users'));
    }

    /**
     * Show the form for creating a new cheque.
     */
    public function create()
    {
        $banks = BankAccount::all();
        $users = User::all();
        return view('admin.Accounts.cheques.create', compact('banks', 'users'));
    }

    /**
     * Store a newly created cheque.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'type' => 'required|in:supplier_payment,outsource,company_expenses,others',
            'note' => 'nullable|string',
            'cheque_date' => 'required|date',
            'cheque_bank' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_to' => 'nullable|string|max:255',
            'created_by' => 'required|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'status' => 'required|in:processing,pending,approved,rejected',
            'cheque_no' => 'required|string|max:100|unique:cheques,cheque_no',
        ]);

        DB::transaction(function () use ($validated) {
            $cheque = Cheques::create($validated);

            // Only process debit if status is approved
            if ($cheque->status === 'approved') {
                $this->processChequeTransaction($cheque);
            }
        });

        return redirect()->route('admin.cheques.index')->with('success', 'Cheque created successfully.');
    }

    /**
     * Show the form for editing a cheque.
     */
    public function edit(Cheques $cheque)
    {
        $banks = BankAccount::all();
        $users = User::all();
        return view('admin.Accounts.cheques.edit', compact('cheque', 'banks', 'users'));
    }

    /**
     * Update a cheque.
     */
    public function update(Request $request, Cheques $cheque)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:255',
            'type' => 'required|in:supplier_payment,outsource,company_expenses,others',
            'note' => 'nullable|string',
            'cheque_date' => 'required|date',
            'cheque_bank' => 'required|exists:bank_accounts,id',
            'amount' => 'required|numeric|min:0.01',
            'paid_to' => 'nullable|string|max:255',
            'created_by' => 'required|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'status' => 'required|in:processing,pending,approved,rejected',
            'cheque_no' => 'required|string|max:100|unique:cheques,cheque_no,' . $cheque->id,
        ]);

        DB::transaction(function () use ($validated, $cheque) {
            $oldStatus = $cheque->status;
            $oldBankId = $cheque->cheque_bank;
            $oldAmount = $cheque->amount;

            $cheque->update($validated);

            // Revert previous transaction if previously approved
            if ($oldStatus === 'approved') {
                $this->revertChequeTransaction($cheque->id, $oldBankId, $oldAmount);
            }

            // Process debit if now approved
            if ($cheque->status === 'approved') {
                $this->processChequeTransaction($cheque);
            }

            // If status is rejected, create return cheque automatically
            if ($cheque->status === 'rejected') {
                $this->createReturnCheque($cheque, 'Issued Cheque Bounce');
            }
        });

        return redirect()->route('admin.cheques.index')->with('success', 'Cheque updated successfully.');
    }

    /**
     * Delete a cheque.
     */
    public function destroy(Cheques $cheque)
    {
        DB::transaction(function () use ($cheque) {
            // Revert bank balance if approved
            if ($cheque->status === 'approved') {
                $this->revertChequeTransaction($cheque->id, $cheque->cheque_bank, $cheque->amount);
            }

            // Delete cheque
            $cheque->delete();
        });

        return redirect()->route('admin.cheques.index')->with('success', 'Cheque deleted successfully.');
    }

    /**
     * Process transaction: debit bank and create transaction.
     */
    private function processChequeTransaction(Cheques $cheque)
    {
        $bank = BankAccount::find($cheque->cheque_bank);
        if (!$bank) {
            return;
        }

        $bank->bank_balance -= $cheque->amount;
        $bank->save();

        Transaction::updateOrCreate(
            ['id' => $cheque->id],
            [
                'transaction_id' => uniqid('TXN-'),
                'id' => $cheque->id,
                'created_by' => $cheque->created_by,
                'updated_by' => $cheque->approved_by,
                'amount' => $cheque->amount,
                'from_bank_id' => $cheque->cheque_bank,
                'to_bank_id' => null,
                'type' => 'debited',
            ],
        );
    }

    /**
     * Revert transaction: add back amount to bank and delete transaction.
     */
    private function revertChequeTransaction($chequeId, $bankId, $amount)
    {
        $bank = BankAccount::find($bankId);
        if ($bank) {
            $bank->bank_balance += $amount;
            $bank->save();
        }

        Transaction::where('cheque_id', $chequeId)->delete();
    }

    /**
     * Create a ReturnCheque when cheque is rejected or bounced.
     */
    private function createReturnCheque(Cheques $cheque, $returnReason = 'Issued Cheque Bounce', $returnDate = null)
    {
        $exists = ReturnCheque::where('cheque_id', $cheque->id)->first();
        if ($exists) {
            return;
        }

        ReturnCheque::create([
            'cheque_id' => $cheque->id,
            'cheque_no' => $cheque->cheque_no,
            'return_date' => $returnDate ?? now(),
            'return_reason' => $returnReason,
            'amount' => $cheque->amount,
            'cheque_bank' => $cheque->cheque_bank,
            'type' => $type,
            'user_id' => auth()->id(),
        ]);
    }

    public function returnStore(Request $request, $chequeId)
    {
        $request->validate([
            'return_date' => 'required|date',
            'return_reason' => 'required|string|max:255',
            'type' => 'required|in:Cheque in Hand,Cheque It Out',
        ]);

        // Find cheque in either model
        $cheque = ReceivingCheque::find($chequeId) ?? Cheques::findOrFail($chequeId);

        // Save return cheque
        ReturnCheque::create([
            'cheque_id' => $cheque->id,
            'cheque_no' => $cheque->cheque_no,
            'return_date' => $request->return_date,
            'return_reason' => $request->return_reason,
            'amount' => $cheque->amount,
            'cheque_bank' => $cheque->bank_id ?? null, // bank_id from either model
            'type' => $request->type,
            'user_id' => auth()->id(),
        ]);

        // Mark the original cheque as rejected
        $cheque->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Return cheque created successfully.');
    }

    public function markPaid($chequeId)
    {
        $cheque = Cheque::findOrFail($chequeId);

        $cheque->status = 'paid';
        $cheque->save();

        return response()->json([
            'success' => true,
            'message' => 'Cheque marked as paid',
        ]);
    }
}
