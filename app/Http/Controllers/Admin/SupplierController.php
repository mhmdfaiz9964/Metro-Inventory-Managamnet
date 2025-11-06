<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Models\Bank;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $suppliers = $query->latest()->paginate(10);

        $suppliers->appends($request->all());

        return view('admin.Supplier.index', compact('suppliers'));
    }
    public function table(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $suppliers = $query->latest()->paginate(10);

        $suppliers->appends($request->all());

        return view('admin.supplier.table', compact('suppliers'));
    }
    public function create()
    {
        return view('admin.supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        Supplier::create($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier created successfully.');
    }

    public function edit(Supplier $supplier)
    {
        return view('admin.supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $supplier->update($request->all());

        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('admin.suppliers.index')->with('success', 'Supplier deleted successfully.');
    }
    public function history(Supplier $supplier)
    {
        // Get all purchases for this supplier
        $purchases = $supplier
            ->purchases()
            ->with(['bomProducts.bomComponent', 'payments'])
            ->orderBy('purchase_date', 'desc')
            ->get();

        // Get all payments
        $payments = $supplier->payments()->orderBy('payment_date', 'desc')->get();

        // Get all loans related to this supplier
        $loans = $supplier->loans()->orderBy('loan_date', 'desc')->get();

        // Calculate summaries
        $totalPaid = $payments->sum('amount');
        $balanceDue = $purchases->sum('total_amount') - $totalPaid;

        // Get outstanding purchases (where due > 0)
        $outstandingPurchases = $purchases
            ->filter(function ($purchase) {
                return $purchase->total_amount > $purchase->payments->sum('amount');
            })
            ->sortByDesc('purchase_date');

        $suppliers = Supplier::all();
        $banks = Bank::all();

        return view('admin.supplier.history', compact('supplier', 'purchases', 'payments', 'loans', 'totalPaid', 'balanceDue', 'outstandingPurchases', 'suppliers', 'banks'));
    }
}
