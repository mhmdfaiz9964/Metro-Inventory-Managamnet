<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;

class SupplierPaymentsController extends Controller
{
    // ------------------ INDEX ------------------
    public function index()
    {
        $suppliers = Supplier::all();
        $banks = Bank::all();
        $payments = SupplierPayment::with(['supplier', 'purchase', 'bank'])
            ->latest()
            ->paginate(20);

        return view('admin.supplier.payments.index', compact('suppliers', 'banks', 'payments'));
    }

    // Fetch supplier payments + balance due for selected purchase
    public function fetchSupplierPurchases(Supplier $supplier)
    {
        // Load purchases with balance calculation
        $purchases = $supplier
            ->purchases()
            ->with('payments')
            ->get()
            ->map(function ($purchase) {
                $totalPaid = $purchase->payments->sum('payment_amount');
                $balanceDue = $purchase->total_amount - $totalPaid; // assuming you have total_amount column
                $purchase->balance_due = $balanceDue;
                return $purchase;
            });

        return response()->json([
            'supplier' => $supplier,
            'purchases' => $purchases,
        ]);
    }

    // Store new supplier payment
    public function store(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'payment_amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,account',
            'bank_id' => 'nullable|exists:banks,id',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $supplier) {
            SupplierPayment::create([
                'supplier_id' => $supplier->id,
                'purchase_id' => $validated['purchase_id'],
                'payment_amount' => $validated['payment_amount'],
                'payment_date' => $validated['payment_date'],
                'payment_method' => $validated['payment_method'],
                'bank_id' => $validated['bank_id'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return redirect()->route('admin.supplier-payments.index')->with('success', 'Supplier payment added successfully.');
    }
}
