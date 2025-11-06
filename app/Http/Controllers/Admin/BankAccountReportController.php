<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Cheques;
use App\Models\ReceivingCheque;
use App\Models\FundTransfer;
use App\Models\ReceivingPayment;
use App\Models\SalePayment;
use PDF;

class BankAccountReportController extends Controller
{
    public function index(Request $request)
    {
        $query = BankAccount::query();

        // Filters
        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', '%'.$request->bank_name.'%');
        }

        if ($request->filled('owner_name')) {
            $query->where('owner_name', 'like', '%'.$request->owner_name.'%');
        }

        $bankAccounts = $query->with([
            'transactionsFrom', 
            'transactionsTo',
            'cheques',
            'receivingCheques',
            'fundTransfersFrom',
            'fundTransfersTo',
            'payments',
        ])->get();

        return view('admin.reports.bank_accounts', compact('bankAccounts'));
    }

    public function exportPdf(Request $request)
    {
        $query = BankAccount::query();

        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', '%'.$request->bank_name.'%');
        }

        if ($request->filled('owner_name')) {
            $query->where('owner_name', 'like', '%'.$request->owner_name.'%');
        }

        $bankAccounts = $query->with([
            'transactionsFrom', 
            'transactionsTo',
            'cheques',
            'receivingCheques',
            'fundTransfersFrom',
            'fundTransfersTo',
            'payments',
        ])->get();

        $pdf = PDF::loadView('admin.reports.bank_accounts_pdf', compact('bankAccounts'));
        return $pdf->download('bank_accounts_report.pdf');
    }

}
