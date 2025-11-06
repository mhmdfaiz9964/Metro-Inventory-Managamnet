<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\Bank;
use App\Models\Branch;

class BankAccountController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = BankAccount::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('owner_name', 'like', "%$search%")
                  ->orWhere('bank_name', 'like', "%$search%")
                  ->orWhere('account_number', 'like', "%$search%");
            });
        }

        if ($request->filled('bank')) {
            $query->where('bank_name', $request->bank);
        }

        $accounts = $query->orderBy('id', 'desc')->paginate(10);
        $banks = Bank::pluck('name');

        return view('admin.Accounts.Banks.index', compact('accounts', 'banks'));
    }
    public function table(Request $request)
    {
        $query = BankAccount::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('owner_name', 'like', "%$search%")
                  ->orWhere('bank_name', 'like', "%$search%")
                  ->orWhere('account_number', 'like', "%$search%");
            });
        }

        if ($request->filled('bank')) {
            $query->where('bank_name', $request->bank);
        }

        $accounts = $query->orderBy('id', 'desc')->paginate(10);
        $banks = Bank::pluck('name');

        return view('admin.Accounts.Banks.table', compact('accounts', 'banks'));
    }
    public function create()
    {
        $banks = Bank::pluck('name');
        return view('admin.Accounts.Banks.create', compact('banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name'      => 'required|string|max:255',
            'branch_name'    => 'required|string|max:255',
            'owner_name'     => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number',
            'bank_balance'   => 'required|numeric|min:0',
            'status'         => 'required|in:active,inactive',
        ]);

        BankAccount::create($validated);

        return redirect()->route('admin.bank-accounts.index')
                         ->with('success', 'Bank account created successfully.');
    }

    public function edit(BankAccount $bankAccount)
    {
        $banks = Bank::pluck('name');
        return view('admin.Accounts.Banks.edit', compact('bankAccount', 'banks'));
    }

    public function update(Request $request, BankAccount $bankAccount)
    {
        $validated = $request->validate([
            'bank_name'      => 'required|string|max:255',
            'branch_name'    => 'required|string|max:255',
            'owner_name'     => 'required|string|max:255',
            'account_number' => 'required|string|max:50|unique:bank_accounts,account_number,' . $bankAccount->id,
            'bank_balance'   => 'required|numeric|min:0',
            'status'         => 'required|in:active,inactive',
        ]);

        $bankAccount->update($validated);

        return redirect()->route('admin.bank-accounts.index')
                         ->with('success', 'Bank account updated successfully.');
    }

    public function destroy(BankAccount $bankAccount)
    {
        $bankAccount->delete();

        return redirect()->route('admin.bank-accounts.index')
                         ->with('success', 'Bank account deleted successfully.');
    }

    public function getBranches(Request $request)
    {
        $bankName = $request->bank_name;

        $bank = Bank::where('name', $bankName)->first();
        if(!$bank){
            return response()->json([]);
        }

        $branches = Branch::where('bank_id', $bank->id)->pluck('name');
        return response()->json($branches);
    }

}
