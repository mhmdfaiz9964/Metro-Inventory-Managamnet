<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FundTransfer;
use App\Models\BankAccount;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FundTransferController extends Controller
{
    /**
     * Display a listing of fund transfers.
     */
    public function index(Request $request)
    {
        $query = FundTransfer::with(['fromBank', 'toBank', 'transferredBy', 'approvedBy']);

        // Filters
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->filled('transferred_by')) {
            $query->where('transferred_by', $request->transferred_by);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('transfer_date', [$request->from_date, $request->to_date]);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%$search%")
                  ->orWhereHas('fromBank', fn($b) => $b->where('bank_name', 'like', "%$search%"))
                  ->orWhereHas('transferredBy', fn($u) => $u->where('name', 'like', "%$search%"));
            });
        }

        $fundTransfers = $query->latest()->paginate(10);
        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.Accounts.FundTransfer.index', compact('fundTransfers', 'banks', 'users'));
    }

    public function table(Request $request)
    {
        $query = FundTransfer::with(['fromBank', 'toBank', 'transferredBy', 'approvedBy']);

        // Filters
        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->filled('transferred_by')) {
            $query->where('transferred_by', $request->transferred_by);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('transfer_date', [$request->from_date, $request->to_date]);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%$search%")
                  ->orWhereHas('fromBank', fn($b) => $b->where('bank_name', 'like', "%$search%"))
                  ->orWhereHas('transferredBy', fn($u) => $u->where('name', 'like', "%$search%"));
            });
        }

        $fundTransfers = $query->latest()->paginate(10);
        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.Accounts.FundTransfer.table', compact('fundTransfers', 'banks', 'users'));
    }
    /**
     * Show create form.
     */
    public function create()
    {
        $banks = BankAccount::all();
        $users = User::all();
        return view('admin.Accounts.FundTransfer.create', compact('banks', 'users'));
    }

    /**
     * Store a new fund transfer.
     */
    public function store(Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'bank_id' => 'required|exists:bank_accounts,id',
            'to_bank_id' => 'required_if:type,bank_to_bank|nullable|exists:bank_accounts,id',
            'type' => 'required|in:bank_to_bank,outsource_payment,employee_payment,sales_payment',
            'transfer_date' => 'required|date',
            'transferred_by' => 'required|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,processing,pending_for_approval,completed',
            'note' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request) {
            $fundTransfer = FundTransfer::create($request->all());

            if ($request->status === 'completed') {
                $this->applyTransfer($fundTransfer);
            }
        });

        return redirect()->route('admin.fund-transfers.index')
                         ->with('success', 'Fund transfer created successfully.');
    }

    /**
     * Show edit form.
     */
    public function edit(FundTransfer $fundTransfer)
    {
        $banks = BankAccount::all();
        $users = User::all();
        return view('admin.Accounts.FundTransfer.edit', compact('fundTransfer', 'banks', 'users'));
    }

    /**
     * Update a fund transfer.
     */
    public function update(Request $request, FundTransfer $fundTransfer)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
            'bank_id' => 'required|exists:bank_accounts,id',
            'to_bank_id' => 'required_if:type,bank_to_bank|nullable|exists:bank_accounts,id',
            'type' => 'required|in:bank_to_bank,outsource_payment,employee_payment,sales_payment',
            'transfer_date' => 'required|date',
            'transferred_by' => 'required|exists:users,id',
            'approved_by' => 'nullable|exists:users,id',
            'status' => 'required|in:pending,processing,pending_for_approval,completed',
            'note' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($request, $fundTransfer) {
            // Revert old balances if previously completed
            if ($fundTransfer->status === 'completed') {
                $this->revertTransfer($fundTransfer);
            }

            $fundTransfer->update($request->all());

            // Apply new balances if now completed
            if ($fundTransfer->status === 'completed') {
                $this->applyTransfer($fundTransfer);
            }
        });

        return redirect()->route('admin.fund-transfers.index')
                         ->with('success', 'Fund transfer updated successfully.');
    }

    /**
     * Delete a fund transfer and revert balances.
     */
    public function destroy(FundTransfer $fundTransfer)
    {
        DB::transaction(function () use ($fundTransfer) {
            if ($fundTransfer->status === 'completed') {
                $this->revertTransfer($fundTransfer);
            }

            $fundTransfer->delete();
        });

        return redirect()->route('admin.fund-transfers.index')
                         ->with('success', 'Fund transfer deleted successfully.');
    }

    /**
     * Apply bank balance changes and create transactions.
     */
    protected function applyTransfer(FundTransfer $fundTransfer)
    {
        $amount = $fundTransfer->amount;

        // Deduct from From Bank
        $fromBank = BankAccount::find($fundTransfer->bank_id);
        if ($fromBank) {
            $fromBank->decrement('bank_balance', $amount);

            Transaction::updateOrCreate(
                [
                    'type' => 'debited',
                    'from_bank_id' => $fromBank->id,
                    'to_bank_id' => $fundTransfer->type === 'bank_to_bank' ? $fundTransfer->to_bank_id : null,
                    'amount' => $amount,
                ],
                [
                    'transaction_id' => Str::uuid(),
                    'cheque_id' => null,
                    'created_by' => $fundTransfer->transferred_by,
                ]
            );
        }

        // Credit To Bank if bank-to-bank
        if ($fundTransfer->type === 'bank_to_bank' && $fundTransfer->to_bank_id) {
            $toBank = BankAccount::find($fundTransfer->to_bank_id);
            if ($toBank) {
                $toBank->increment('bank_balance', $amount);

                Transaction::updateOrCreate(
                    [
                        'type' => 'credited',
                        'to_bank_id' => $toBank->id,
                        'amount' => $amount,
                    ],
                    [
                        'transaction_id' => Str::uuid(),
                        'cheque_id' => null,
                        'created_by' => $fundTransfer->transferred_by,
                    ]
                );
            }
        }
    }

    /**
     * Revert bank balance changes.
     */
    protected function revertTransfer(FundTransfer $fundTransfer)
    {
        $amount = $fundTransfer->amount;

        // Revert From Bank
        $fromBank = BankAccount::find($fundTransfer->bank_id);
        if ($fromBank) {
            $fromBank->increment('bank_balance', $amount);
            Transaction::where('from_bank_id', $fromBank->id)
                       ->where('amount', $amount)
                       ->delete();
        }

        // Revert To Bank
        if ($fundTransfer->type === 'bank_to_bank' && $fundTransfer->to_bank_id) {
            $toBank = BankAccount::find($fundTransfer->to_bank_id);
            if ($toBank) {
                $toBank->decrement('bank_balance', $amount);
                Transaction::where('to_bank_id', $toBank->id)
                           ->where('amount', $amount)
                           ->delete();
            }
        }
    }
}
