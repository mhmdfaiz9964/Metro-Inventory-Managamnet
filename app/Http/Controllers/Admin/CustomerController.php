<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;


class CustomerController extends Controller
{
    // List customers with search
    public function index(Request $request)
    {
        $search = $request->input('search');

        $customers = Customer::query()
            ->when(
                $search,
                fn($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('mobile_number_2', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"),
            )
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.customer.index', compact('customers', 'search'));
    }

    // AJAX table
    public function table(Request $request)
    {
        $search = $request->get('search');

        $customers = Customer::when(
            $search,
            fn($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('mobile_number', 'like', "%{$search}%")
                ->orWhere('mobile_number_2', 'like', "%{$search}%"),
        )
            ->latest()
            ->paginate(10);

        return view('admin.customer.table', compact('customers', 'search'))->render();
    }

    // Show create form
    public function create()
    {
        return view('admin.customer.create');
    }

    // Store new customer
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'mobile_number_2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'note' => 'nullable|string|max:1000',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        Customer::create([
            'name' => $request->name,
            'mobile_number' => $request->mobile_number,
            'mobile_number_2' => $request->mobile_number_2,
            'email' => $request->email,
            'note' => $request->note,
            'credit_limit' => $request->credit_limit ?? 0,
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    // Show edit form
    public function edit(Customer $customer)
    {
        return view('admin.customer.edit', compact('customer'));
    }

    // Update customer
    public function update(Request $request, Customer $customer)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:20',
            'mobile_number_2' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'note' => 'nullable|string|max:1000',
            'credit_limit' => 'nullable|numeric|min:0',
        ]);

        $customer->update([
            'name' => $request->name,
            'mobile_number' => $request->mobile_number,
            'mobile_number_2' => $request->mobile_number_2,
            'email' => $request->email,
            'note' => $request->note,
            'credit_limit' => $request->credit_limit ?? 0,
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    // Delete customer
    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('admin.customers.index')->with('success', 'Customer deleted successfully.');
    }

    // Customer History
    // Customer History
    public function history(Customer $customer)
    {
        $sales = $customer
            ->sales()
            ->with(['items.product', 'salePayments'])
            ->orderBy('sale_date', 'desc')
            ->get();

        $loans = $customer->loans()->orderBy('loan_date', 'desc')->get();
        $returns = $customer->saleReturns()->with('items')->orderBy('return_date', 'desc')->get();

        // Calculate summaries
        $totalPaid = $sales->flatMap(fn($s) => $s->salePayments)->sum('payment_paid');
        $balanceDue = $sales->sum('total_amount') - $totalPaid;

        // Get outstanding sales (where due > 0)
        $outstandingSales = $sales
            ->filter(function ($sale) {
                return $sale->total_amount > $sale->salePayments->sum('payment_paid');
            })
            ->sortByDesc('sale_date');

        $customers = Customer::all();
        $banks = Bank::all();

        return view('admin.customer.history', compact('customer', 'sales', 'loans', 'returns', 'totalPaid', 'balanceDue', 'outstandingSales', 'customers', 'banks'));
    }
    // Customer Ledger Modal
    public function ledger()
    {
        return view('admin.customer.ledger')->render();
    }

    // Live Search for Ledger Modal
    public function ledgerSearch(Request $request)
    {
        $search = $request->get('q');

        $customers = Customer::query()
            ->when(
                $search,
                fn($q) => $q
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"),
            )
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'mobile_number']);

        return response()->json($customers);
    }
}
