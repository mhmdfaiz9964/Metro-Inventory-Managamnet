<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    /**
     * Display a listing of transactions with filters.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['fromBank', 'toBank', 'creator', 'updater']);

        // Filters
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('bank_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_bank_id', $request->bank_id)->orWhere('to_bank_id', $request->bank_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('type', $request->status); // type: credited / debited
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        $transactions = $query->latest()->paginate(10);
        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.transactions.index', compact('transactions', 'banks', 'users'));
    }

    public function table(Request $request)
    {
        $query = Transaction::with(['fromBank', 'toBank', 'creator', 'updater']);

        // Filters
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        if ($request->filled('bank_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('from_bank_id', $request->bank_id)->orWhere('to_bank_id', $request->bank_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('type', $request->status); // type: credited / debited
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        $transactions = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return view('admin.transactions.table', compact('transactions'))->render();
        }

        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.transactions.table', compact('transactions', 'banks', 'users'));
    }
}
