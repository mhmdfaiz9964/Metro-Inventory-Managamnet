<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Loan;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    /**
     * Display a listing of loans.
     */
    public function index(Request $request)
    {
        // Load related models: customer, supplier, purchase, sale (invoice)
        $query = Loan::with(['customer', 'supplier', 'purchase', 'sale']);

        // Filter by type (given / received)
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Filter by status (pending / paid / partially_paid)
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by search term (customer/supplier name)
        if ($search = $request->input('search')) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhereHas('supplier', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Paginate results
        $loans = $query->latest()->paginate(10);
        $loans->appends($request->all());

        return view('admin.loan.index', compact('loans'));
    }

    /**
     * Display details of a single loan.
     */
    public function show(Loan $loan)
    {
        // Load all relationships
        $loan->load(['customer', 'supplier', 'purchase', 'sale']);

        return view('admin.loan.show', compact('loan'));
    }
}