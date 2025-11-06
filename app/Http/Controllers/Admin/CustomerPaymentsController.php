<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\SalePayment;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\ReceivingCheque;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;

class CustomerPaymentsController extends Controller
{
    // ------------------ INDEX ------------------
    public function index()
    {
        $customers = Customer::all();
        $banks = Bank::all();
        $loans = Loan::all();

        // Fetch cheques/payments (adjust model name if needed)
        $cheques = ReceivingCheque::with(['bank'])
            ->orderBy('id', 'desc')
            ->paginate(20);

        return view('admin.customer.payments.index', compact('customers', 'banks', 'loans', 'cheques'));
    }

    // Fetch payments and calculate totals for a single customer
    public function fetchCustomerPayments(Customer $customer)
    {
        $customer->load('sales.salePayments', 'customerPayments', 'loans');

        $totalSales = $customer->sales->sum('total_amount');
        $totalPaid = $customer->sales->flatMap->salePayments->sum('payment_paid') + $customer->customerPayments->sum('paid_amount');
        $totalLoanGiven = $customer->loans->where('type', 'given')->sum('amount');
        $totalLoanReceived = $customer->loans->where('type', 'received')->sum('amount');

        $dueAmount = $totalSales - $totalPaid;

        return response()->json([
            'customer' => $customer,
            'totalSales' => $totalSales,
            'totalPaid' => $totalPaid,
            'dueAmount' => $dueAmount,
            'totalLoanGiven' => $totalLoanGiven,
            'totalLoanReceived' => $totalLoanReceived,
        ]);
    }

    // Store new customer payment or loan
    public function store(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'invoice_id' => 'nullable|exists:sales,id',
            'paid_amount' => 'required|numeric|min:1',
            'paid_date' => 'required|date',
            'payment_method' => 'required|in:cash,cheque,account',
            'paid_bank_account_id' => 'nullable|exists:banks,id',
            'cheque_no' => 'nullable|string|max:50',
            'cheque_date' => 'nullable|date',
            'payment_type' => 'required|in:customer,loan,fund_transfer',
            'loan_id' => 'nullable|exists:loans,id',
            'from_bank_id' => 'nullable|exists:banks,id',
            'to_bank_id' => 'nullable|exists:banks,id',
        ]);

        DB::transaction(function () use ($validated, $customer) {
            if ($validated['payment_type'] === 'customer') {
                // Customer Payment
                $payment = CustomerPayment::create([
                    'customer_id' => $customer->id,
                    'invoice_id' => $validated['invoice_id'] ?? null,
                    'paid_amount' => $validated['paid_amount'],
                    'paid_date' => $validated['paid_date'],
                    'payment_method' => $validated['payment_method'],
                    'paid_bank_account_id' => $validated['paid_bank_account_id'] ?? null,
                ]);

                if (!empty($validated['invoice_id'])) {
                    SalePayment::create([
                        'sale_id' => $validated['invoice_id'],
                        'payment_method' => $validated['payment_method'],
                        'payment_amount' => $validated['paid_amount'],
                        'payment_paid' => $validated['paid_amount'],
                        'paid_by' => $customer->id,
                        'paid_date' => $validated['paid_date'],
                        'bank_account_id' => $validated['paid_bank_account_id'] ?? null,
                        'discount' => 0,
                        'discount_type' => 'amount',
                    ]);
                }

                if ($validated['payment_method'] === 'cheque') {
                    ReceivingCheque::create([
                        'cheque_no' => $validated['cheque_no'] ?? 'CHQ-' . rand(1000, 9999),
                        'bank_id' => $validated['paid_bank_account_id'] ?? null,
                        'paid_by' => $customer->id,
                        'status' => 'pending',
                        'paid_date' => $validated['paid_date'],
                        'cheque_date' => $validated['cheque_date'] ?? $validated['paid_date'],
                        'amount' => $validated['paid_amount'],
                        'reason' => 'Customer Payment',
                        'cheque_type' => 'customer',
                        'paid_bank_account_id' => $validated['paid_bank_account_id'] ?? null,
                    ]);
                }

                $customer->balance_due -= $validated['paid_amount'];
                $customer->total_paid += $validated['paid_amount'];
                $customer->save();
            } elseif ($validated['payment_type'] === 'loan') {
                // Loan Payment
                $loan = \App\Models\Loan::findOrFail($validated['loan_id']);
                $loan->paid_amount += $validated['paid_amount'];
                $loan->balance -= $validated['paid_amount'];
                $loan->save();
            } elseif ($validated['payment_type'] === 'fund_transfer') {
                // Fund Transfer
                $fromBank = \App\Models\Bank::findOrFail($validated['from_bank_id']);
                $toBank = \App\Models\Bank::findOrFail($validated['to_bank_id']);

                $fromBank->balance -= $validated['paid_amount'];
                $toBank->balance += $validated['paid_amount'];

                $fromBank->save();
                $toBank->save();
            }
        });

        return redirect()->route('admin.customers-payments.index')->with('success', 'Payment added successfully.');
    }
}
