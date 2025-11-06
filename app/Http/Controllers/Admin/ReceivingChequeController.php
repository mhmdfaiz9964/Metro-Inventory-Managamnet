<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReceivingCheque;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceivingChequeController extends Controller
{
    // Show pending cheques
    public function index(Request $request)
    {
        // Auto mark Cash Cheques as paid if cheque_date is today or past
        $this->autoMarkCashCheques();

        $query = ReceivingCheque::with('bank')->where('status', 'pending');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cheque_no', 'like', "%{$request->search}%")
                  ->orWhere('paid_by', 'like', "%{$request->search}%");
            });
        }

        // Order by nearest cheque date first
        $cheques = $query->orderBy('cheque_date', 'asc')->paginate(10);

        $banks = Bank::pluck('name', 'id');
        $bankAccounts = BankAccount::pluck('bank_name', 'id');

        return view('admin.Accounts.receiving_cheques.index', compact('cheques', 'banks', 'bankAccounts'));
    }

    // Data table for AJAX
    public function table(Request $request)
    {
        // Auto mark Cash Cheques as paid if cheque_date is today or past
        $this->autoMarkCashCheques();

        $query = ReceivingCheque::with('bank')->where('status', 'pending');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('cheque_no', 'like', "%{$request->search}%")
                  ->orWhere('paid_by', 'like', "%{$request->search}%");
            });
        }

        $cheques = $query->orderBy('cheque_date', 'asc')->paginate(10);

        $banks = Bank::pluck('name', 'id');
        $bankAccounts = BankAccount::pluck('bank_name', 'id');

        return view('admin.Accounts.receiving_cheques.table', compact('cheques', 'banks', 'bankAccounts'));
    }

    // Show form to create cheque
    public function create()
    {
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();
        return view('admin.Accounts.receiving_cheques.create', compact('banks', 'bankAccounts'));
    }

    // Store cheque
    public function store(Request $request)
    {
        $request->validate([
            'cheque_no' => 'required|string|max:255|unique:receiving_cheques,cheque_no',
            'bank_id' => 'required|exists:banks,id',
            'paid_by' => 'nullable|string|max:255',
            'status' => 'required|in:pending,paid',
            'paid_date' => 'nullable|date',
            'cheque_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'cheque_type' => 'required|in:Cash Cheque,Crossed Cheque',
            'paid_bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request) {
            $data = $request->all();

            // Cash Cheques remain pending until cheque_date
            $cheque = ReceivingCheque::create($data);

            // If it is a past Cash Cheque, auto mark
            if ($cheque->cheque_type === 'Cash Cheque' && Carbon::parse($cheque->cheque_date)->lte(Carbon::today())) {
                $cheque->status = 'paid';
                $cheque->paid_date = $cheque->cheque_date;
                $cheque->save();

                if ($cheque->paid_bank_account_id) {
                    $this->applyPaidChequeToBankAccount($cheque);
                }
            }
        });

        return redirect()->route('admin.accounts.menu')->with('success', 'Cheque added successfully!');
    }

    // Show form to edit
    public function edit(ReceivingCheque $receivingCheque)
    {
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();
        return view('admin.Accounts.receiving_cheques.edit', compact('receivingCheque', 'banks', 'bankAccounts'));
    }

    // Update cheque
    public function update(Request $request, ReceivingCheque $receivingCheque)
    {
        $request->validate([
            'cheque_no' => 'required|string|max:255|unique:receiving_cheques,cheque_no,' . $receivingCheque->id,
            'bank_id' => 'required|exists:banks,id',
            'paid_by' => 'nullable|string|max:255',
            'status' => 'required|in:pending,paid',
            'paid_date' => 'nullable|date',
            'cheque_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
            'cheque_type' => 'required|in:Cash Cheque,Crossed Cheque',
            'paid_bank_account_id' => 'nullable|exists:bank_accounts,id',
        ]);

        DB::transaction(function () use ($request, $receivingCheque) {
            $oldStatus = $receivingCheque->status;

            $receivingCheque->update($request->all());

            if ($oldStatus === 'paid' && $receivingCheque->status !== 'paid') {
                Transaction::where('cheque_id', $receivingCheque->id)->delete();
            }

            if ($receivingCheque->status === 'paid' && $receivingCheque->paid_bank_account_id) {
                $this->applyPaidChequeToBankAccount($receivingCheque);
            }
        });

        return redirect()->route('admin.accounts.menu')->with('success', 'Cheque updated successfully!');
    }

    // Delete cheque
    public function destroy(ReceivingCheque $receivingCheque)
    {
        DB::transaction(function () use ($receivingCheque) {
            if ($receivingCheque->status === 'paid') {
                Transaction::where('cheque_id', $receivingCheque->id)->delete();
            }
            $receivingCheque->delete();
        });

        return redirect()->route('admin.receiving-cheques.index')->with('success', 'Cheque deleted successfully!');
    }

    // Mark a cheque as received (used in index modal)
    public function markReceived(Request $request, $receivingId)
    {
        $request->validate([
            'paid_date' => 'required|date',
            'paid_bank_account_id' => 'required|exists:bank_accounts,id',
        ]);

        $receiving = ReceivingCheque::findOrFail($receivingId);

        DB::transaction(function () use ($receiving, $request) {
            $receiving->status = 'paid';
            $receiving->paid_date = $request->paid_date;
            $receiving->paid_bank_account_id = $request->paid_bank_account_id;
            $receiving->save();

            $this->applyPaidChequeToBankAccount($receiving);
        });

        return response()->json([
            'success' => true,
            'message' => 'Cheque marked as received',
        ]);
    }

    // Apply transaction and update bank balance
    private function applyPaidChequeToBankAccount(ReceivingCheque $cheque)
    {
        $bank = BankAccount::find($cheque->paid_bank_account_id);
        if ($bank) {
            $bank->increment('bank_balance', $cheque->amount);

            Transaction::updateOrCreate(
                ['cheque_id' => $cheque->id],
                [
                    'transaction_id' => Str::uuid(),
                    'cheque_id' => $cheque->id,
                    'created_by' => Auth::id(),
                    'amount' => $cheque->amount,
                    'from_bank_id' => null,
                    'to_bank_id' => $bank->id,
                    'type' => 'credited',
                    'reason' => "Cheque received: {$cheque->cheque_no}",
                ],
            );
        }
    }

    // Automatically mark cash cheques as paid on cheque_date
    private function autoMarkCashCheques()
    {
        $today = Carbon::today();

        ReceivingCheque::where('cheque_type', 'Cash Cheque')
            ->where('status', 'pending')
            ->whereDate('cheque_date', '<=', $today)
            ->get()
            ->each(function ($cheque) {
                $cheque->status = 'paid';
                $cheque->paid_date = $cheque->cheque_date;
                $cheque->save();

                if ($cheque->paid_bank_account_id) {
                    $this->applyPaidChequeToBankAccount($cheque);
                }
            });
    }
}
