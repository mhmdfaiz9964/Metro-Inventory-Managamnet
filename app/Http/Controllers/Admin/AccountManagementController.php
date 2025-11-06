<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\Transaction;

class AccountManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $banks = BankAccount::with('transactionsTo.fromBank', 'transactionsFrom.toBank')->get();
        return view('admin.Accounts.index', compact('banks'));
    }
    public function table()
    {
        $banks = BankAccount::with('transactionsTo.fromBank', 'transactionsFrom.toBank')->get();
        return view('admin.Accounts.table', compact('banks'));
    }
}
