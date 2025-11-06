<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnCheque;
use App\Models\User;
use App\Models\BankAccount;

class ReturnChequeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of return cheques with filters
     */
    public function index(Request $request)
    {
        $query = ReturnCheque::query();

        // Filter by original cheque number
        if ($request->filled('cheque_no')) {
            $query->where('cheque_no', 'like', '%' . $request->cheque_no . '%');
        }

        // Filter by return cheque number
        if ($request->filled('return_cheque_no')) {
            $query->where('return_cheque_no', 'like', '%' . $request->return_cheque_no . '%');
        }

        // Filter by date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('return_date', [$request->from_date, $request->to_date]);
        }

        $returnCheques = $query->with('bank')->latest()->paginate(10);

        // Pass banks and users to the view
        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.Accounts.return_cheques.index', compact('returnCheques', 'banks', 'users'));
    }

    public function table(Request $request)
    {
        $query = ReturnCheque::query();

        // Filter by original cheque number
        if ($request->filled('cheque_no')) {
            $query->where('cheque_no', 'like', '%' . $request->cheque_no . '%');
        }

        // Filter by return cheque number
        if ($request->filled('return_cheque_no')) {
            $query->where('return_cheque_no', 'like', '%' . $request->return_cheque_no . '%');
        }

        // Filter by date range
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('return_date', [$request->from_date, $request->to_date]);
        }

        $returnCheques = $query->with('bank')->latest()->paginate(10);

        // Pass banks and users to the view
        $banks = BankAccount::all();
        $users = User::all();

        return view('admin.Accounts.return_cheques.table', compact('returnCheques', 'banks', 'users'));
    }


}
