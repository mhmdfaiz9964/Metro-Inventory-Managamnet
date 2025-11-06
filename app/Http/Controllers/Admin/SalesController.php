<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Products;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\ReceivingCheque;
use App\Models\Customer;
use App\Models\SalePayment;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Barryvdh\DomPDF\Facade\Pdf;
use Milon\Barcode\DNS2D;

class SalesController extends Controller
{
    // ------------------ INDEX ------------------
    public function index(Request $request)
    {
        $query = Sale::with(['salesperson', 'items.product', 'salePayments.bank', 'customer']);

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->salesperson_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('items.product', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(10);
        $salespersons = User::role('Sales Rep')->get();

        return view('admin.sales.index', compact('sales', 'salespersons'));
    }

    //------------------ TABLE ------------------
    public function table(Request $request)
    {
        $query = Sale::with(['salesperson', 'items.product', 'salePayments.bank', 'customer']);

        if ($request->filled('salesperson_id')) {
            $query->where('salesperson_id', $request->salesperson_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('items.product', fn($q) => $q->where('name', 'like', "%$search%"));
        }

        $sales = $query->orderBy('sale_date', 'desc')->paginate(10);
        $salespersons = User::role('Sales Rep')->get();

        return view('admin.sales.table', compact('sales', 'salespersons'));
    }

    // ------------------ CREATE ------------------
    public function create()
    {
        $products = Products::all();
        $salespersons = User::role('Sales Rep')->get();
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();
        $customers = Customer::all();
        $sale = new Sale();

        return view('admin.sales.create', compact('products', 'salespersons', 'banks', 'bankAccounts', 'sale', 'customers'));
    }

    // ------------------ EDIT ------------------
    public function edit(Sale $sale)
    {
        $products = Products::all();
        $salespersons = User::role('Sales Rep')->get();
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();
        $customers = Customer::all();

        return view('admin.sales.create', compact('sale', 'products', 'salespersons', 'banks', 'bankAccounts', 'customers'));
    }

    // ------------------ STORE ------------------
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'salesperson_id' => 'nullable|exists:users,id',
            'customer_id' => 'required|exists:customers,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.sale_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
            'products.*.discount_type' => 'nullable|in:amount,percentage',
            'overall_discount' => 'nullable|numeric|min:0',
            'overall_discount_type' => 'nullable|in:amount,percentage',
            'payments' => 'nullable|array',
            'payments.*.payment_method' => 'required|in:cash,cheque,loan,fund_transfer',
            'payments.*.payment_paid' => 'required|numeric|min:0',
            'payments.*.paid_date' => 'required|date',
            'payments.*.bank_id' => 'nullable|integer',
            'payments.*.cheque_no' => 'nullable|string|max:50',
            'payments.*.cheque_date' => 'nullable|date',
            'payments.*.due_date' => 'nullable|date',
        ]);

        // Validate bank_id based on payment method
        $paymentErrors = [];
        if (!empty($validated['payments'])) {
            foreach ($validated['payments'] as $i => $pay) {
                if ($pay['payment_method'] === 'cheque' && $pay['bank_id']) {
                    if (!Bank::find($pay['bank_id'])) {
                        $paymentErrors["payments.{$i}.bank_id"] = 'Invalid bank selected for cheque.';
                    }
                } elseif ($pay['payment_method'] === 'fund_transfer' && $pay['bank_id']) {
                    if (!BankAccount::find($pay['bank_id'])) {
                        $paymentErrors["payments.{$i}.bank_id"] = 'Invalid bank account selected for fund transfer.';
                    }
                }
            }
        }

        if (!empty($paymentErrors)) {
            return back()->withErrors($paymentErrors)->withInput();
        }

        $customer = Customer::findOrFail($validated['customer_id']);

        // Pre-calculate totals and validate stock and credit limit
        $totalAmount = 0;
        $stockErrors = [];
        foreach ($request->products as $index => $p) {
            $product = Products::findOrFail($p['product_id']);
            $available = $product->stock ? $product->stock->available_stock : 0;
            if ($available < $p['quantity']) {
                $stockErrors[$index . '.quantity'] = "Quantity is only available {$available} for {$product->name}.";
            }

            $itemTotal = $p['quantity'] * $p['sale_price'];
            $itemDiscount = $p['discount'] ?? 0;
            $itemDiscountType = $p['discount_type'] ?? 'amount';

            if ($itemDiscountType === 'percentage') {
                $itemTotal -= ($itemTotal * $itemDiscount) / 100;
            } else {
                $itemTotal -= $itemDiscount;
            }

            $totalAmount += $itemTotal;
        }

        if (!empty($stockErrors)) {
            return back()->withErrors($stockErrors)->withInput();
        }

        $overallDiscount = $validated['overall_discount'] ?? 0;
        $overallDiscountType = $validated['overall_discount_type'] ?? 'amount';
        $discountAmount = $overallDiscount > 0 ? ($overallDiscountType === 'percentage' ? ($overallDiscount / 100) * $totalAmount : $overallDiscount) : 0;
        $finalTotal = $totalAmount - $discountAmount;

        // Calculate cash paid and loan amount
        $cashPaid = 0;
        $loanAmount = 0;
        if (!empty($validated['payments'])) {
            foreach ($validated['payments'] as $pay) {
                if ($pay['payment_method'] !== 'loan') {
                    $cashPaid += $pay['payment_paid'];
                } else {
                    $loanAmount += $pay['payment_paid'];
                }
            }
        }

        $unpaid = $finalTotal - $cashPaid;
        if ($loanAmount > 0 && abs($loanAmount - $unpaid) > 0.01) {
            return back()->withErrors(['payments' => 'The total credit amount must match the unpaid balance.'])->withInput();
        }

        $projected_due = $customer->balance_due + $loanAmount;

        if ($customer->credit_limit && $projected_due > $customer->credit_limit) {
            return back()->withErrors(['customer_id' => "Customer credit limit has exceeded. Projected due: " . number_format($projected_due, 2) . ", Limit: " . number_format($customer->credit_limit, 2)])->withInput();
        }

        $salespersonId = Auth::user()->hasRole('Sales Rep') ? Auth::id() : $validated['salesperson_id'];

        DB::transaction(function () use ($request, $validated, $customer, $salespersonId, $finalTotal, $cashPaid, $loanAmount) {
            // Create Sale
            $sale = Sale::create([
                'salesperson_id' => $salespersonId,
                'customer_id' => $customer->id,
                'sale_date' => $validated['sale_date'],
                'total_amount' => $finalTotal,
            ]);

            // Create sale items
            foreach ($request->products as $p) {
                $itemTotal = $p['quantity'] * $p['sale_price'];
                $itemDiscount = $p['discount'] ?? 0;
                $itemDiscountType = $p['discount_type'] ?? 'amount';

                if ($itemDiscountType === 'percentage') {
                    $itemTotal -= ($itemTotal * $itemDiscount) / 100;
                } else {
                    $itemTotal -= $itemDiscount;
                }

                $sale->items()->create([
                    'product_id' => $p['product_id'],
                    'quantity' => $p['quantity'],
                    'sale_price' => $p['sale_price'],
                    'total' => $itemTotal,
                    'discount' => $itemDiscount,
                    'discount_type' => $itemDiscountType,
                ]);

                // Reduce stock
                $product = Products::find($p['product_id']);
                if ($product && $product->stock) {
                    $product->stock->available_stock -= $p['quantity'];
                    $product->stock->save();
                }
            }

            // Handle payments
            if (!empty($validated['payments'])) {
                foreach ($validated['payments'] as $pay) {
                    $salePayment = SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_method' => $pay['payment_method'],
                        'payment_amount' => $finalTotal,
                        'discount' => $validated['overall_discount'] ?? 0,
                        'discount_type' => $validated['overall_discount_type'] ?? 'amount',
                        'payment_paid' => $pay['payment_paid'],
                        'paid_by' => $customer->id,
                        'paid_date' => $pay['paid_date'],
                        'bank_account_id' => $pay['bank_id'] ?? null,
                        'due_date' => $pay['due_date'] ?? null,
                    ]);

                    // Handle cheque payments
                    if ($pay['payment_method'] === 'cheque') {
                        ReceivingCheque::create([
                            'cheque_no' => $pay['cheque_no'] ?? 'CHEQUE-' . $sale->id . '-' . rand(1000, 9999),
                            'bank_id' => $pay['bank_id'] ?? null,
                            'paid_by' => $customer->name,
                            'status' => 'pending',
                            'paid_date' => $pay['paid_date'],
                            'cheque_date' => $pay['cheque_date'] ?? $pay['paid_date'],
                            'amount' => $pay['payment_paid'],
                            'reason' => 'Payment for Sale ID: ' . $sale->id,
                        ]);
                    }

                    // Handle loan payments
                    if ($pay['payment_method'] === 'loan') {
                        Loan::create([
                            'reference_number' => 'LN-' . $sale->id . '-' . rand(1000, 9999),
                            'customer_id' => $customer->id,
                            'invoice_id' => $sale->id,
                            'type' => Loan::TYPE_GIVEN,
                            'amount' => $pay['payment_paid'],
                            'note' => 'Loan created from sale payment',
                            'loan_date' => now(),
                            'due_date' => $pay['due_date'] ?? now()->addDays(30),
                            'status' => Loan::STATUS_PENDING,
                        ]);
                    }
                }
            }

            // Update customer balances
            $customer->total_paid += $cashPaid;
            $customer->balance_due += $loanAmount;
            $customer->save();
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale created successfully.');
    }

    // ------------------ UPDATE ------------------
    public function update(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'sale_date' => 'required|date',
            'salesperson_id' => 'nullable|exists:users,id',
            'customer_id' => 'required|exists:customers,id',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.sale_price' => 'required|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
            'products.*.discount_type' => 'nullable|in:amount,percentage',
            'overall_discount' => 'nullable|numeric|min:0',
            'overall_discount_type' => 'nullable|in:amount,percentage',
            'payments' => 'nullable|array',
            'payments.*.payment_method' => 'required|in:cash,cheque,loan,fund_transfer',
            'payments.*.payment_paid' => 'required|numeric|min:0',
            'payments.*.paid_date' => 'required|date',
            'payments.*.bank_id' => 'nullable|integer',
            'payments.*.cheque_no' => 'nullable|string|max:50',
            'payments.*.cheque_date' => 'nullable|date',
            'payments.*.due_date' => 'nullable|date',
        ]);

        // Validate bank_id based on payment method
        $paymentErrors = [];
        if (!empty($validated['payments'])) {
            foreach ($validated['payments'] as $i => $pay) {
                if ($pay['payment_method'] === 'cheque' && $pay['bank_id']) {
                    if (!Bank::find($pay['bank_id'])) {
                        $paymentErrors["payments.{$i}.bank_id"] = 'Invalid bank selected for cheque.';
                    }
                } elseif ($pay['payment_method'] === 'fund_transfer' && $pay['bank_id']) {
                    if (!BankAccount::find($pay['bank_id'])) {
                        $paymentErrors["payments.{$i}.bank_id"] = 'Invalid bank account selected for fund transfer.';
                    }
                }
            }
        }

        if (!empty($paymentErrors)) {
            return back()->withErrors($paymentErrors)->withInput();
        }

        // Pre-calculate totals and validate stock and credit limit
        $oldCustomer = $sale->customer;
        $oldTotalPaid = 0;
        foreach ($sale->salePayments as $oldPay) {
            if ($oldPay->payment_method !== 'loan') {
                $oldTotalPaid += $oldPay->payment_paid;
            }
        }
        $oldUnpaid = $sale->total_amount - $oldTotalPaid;
        $oldLoanAmount = $sale->total_amount - $oldTotalPaid; // Actually old loan

        $customer = Customer::findOrFail($validated['customer_id']);

        $totalAmount = 0;
        $stockErrors = [];
        $oldQtys = [];
        foreach ($sale->items as $item) {
            $oldQtys[$item->product_id] = ($oldQtys[$item->product_id] ?? 0) + $item->quantity;
        }

        foreach ($request->products as $index => $p) {
            $product = Products::findOrFail($p['product_id']);
            $available = ($product->stock ? $product->stock->available_stock : 0) + ($oldQtys[$p['product_id']] ?? 0);
            if ($available < $p['quantity']) {
                $stockErrors[$index . '.quantity'] = "Quantity is only available {$available} for {$product->name}.";
            }

            $itemTotal = $p['quantity'] * $p['sale_price'];
            $itemDiscount = $p['discount'] ?? 0;
            $itemDiscountType = $p['discount_type'] ?? 'amount';

            if ($itemDiscountType === 'percentage') {
                $itemTotal -= ($itemTotal * $itemDiscount) / 100;
            } else {
                $itemTotal -= $itemDiscount;
            }

            $totalAmount += $itemTotal;
        }

        if (!empty($stockErrors)) {
            return back()->withErrors($stockErrors)->withInput();
        }

        $overallDiscount = $validated['overall_discount'] ?? 0;
        $overallDiscountType = $validated['overall_discount_type'] ?? 'amount';
        $discountAmount = $overallDiscount > 0 ? ($overallDiscountType === 'percentage' ? ($overallDiscount / 100) * $totalAmount : $overallDiscount) : 0;
        $finalTotal = $totalAmount - $discountAmount;

        // Calculate cash paid and loan amount
        $cashPaid = 0;
        $loanAmount = 0;
        if (!empty($validated['payments'])) {
            foreach ($validated['payments'] as $pay) {
                if ($pay['payment_method'] !== 'loan') {
                    $cashPaid += $pay['payment_paid'];
                } else {
                    $loanAmount += $pay['payment_paid'];
                }
            }
        }

        $unpaid = $finalTotal - $cashPaid;
        if ($loanAmount > 0 && abs($loanAmount - $unpaid) > 0.01) {
            return back()->withErrors(['payments' => 'The total credit amount must match the unpaid balance.'])->withInput();
        }

        $loanAmount = $unpaid; // Use calculated

        if ($oldCustomer->id === $customer->id) {
            $projected_due = $customer->balance_due - $oldUnpaid + $loanAmount;
        } else {
            $projected_due = $customer->balance_due + $loanAmount;
        }

        if ($customer->credit_limit && $projected_due > $customer->credit_limit) {
            return back()->withErrors(['customer_id' => "Customer credit limit has exceeded. Projected due: " . number_format($projected_due, 2) . ", Limit: " . number_format($customer->credit_limit, 2)])->withInput();
        }

        DB::transaction(function () use ($request, $validated, $sale, $customer, $cashPaid, $loanAmount) {
            $salespersonId = Auth::user()->hasRole('Sales Rep') ? Auth::id() : $validated['salesperson_id'];

            // Restore stock from old items
            foreach ($sale->items as $item) {
                if ($item->product && $item->product->stock) {
                    $item->product->stock->available_stock += $item->quantity;
                    $item->product->stock->save();
                }
            }

            // Reverse previous customer balances
            $oldCashPaid = 0;
            foreach ($sale->salePayments as $oldPay) {
                if ($oldPay->payment_method !== 'loan') {
                    $oldCashPaid += $oldPay->payment_paid;
                }
            }
            $oldLoanAmount = $sale->total_amount - $oldCashPaid;
            $sale->customer->total_paid -= $oldCashPaid;
            $sale->customer->balance_due -= $oldLoanAmount;
            $sale->customer->save();

            // Delete old items & payments
            $sale->items()->delete();
            foreach ($sale->salePayments as $payment) {
                if ($payment->payment_method === 'cheque') {
                    ReceivingCheque::where('cheque_no', $payment->cheque_no)->delete();
                }
                if ($payment->payment_method === 'loan') {
                    Loan::where('invoice_id', $sale->id)->delete(); // Assume one per sale, or adjust
                }
                $payment->delete();
            }

            // Recreate sale items
            $totalAmount = 0;
            foreach ($request->products as $p) {
                $itemTotal = $p['quantity'] * $p['sale_price'];
                $itemDiscount = $p['discount'] ?? 0;
                $itemDiscountType = $p['discount_type'] ?? 'amount';

                if ($itemDiscountType === 'percentage') {
                    $itemTotal -= ($itemTotal * $itemDiscount) / 100;
                } else {
                    $itemTotal -= $itemDiscount;
                }

                $sale->items()->create([
                    'product_id' => $p['product_id'],
                    'quantity' => $p['quantity'],
                    'sale_price' => $p['sale_price'],
                    'total' => $itemTotal,
                    'discount' => $itemDiscount,
                    'discount_type' => $itemDiscountType,
                ]);

                $totalAmount += $itemTotal;

                // Update stock
                $product = Products::find($p['product_id']);
                if ($product && $product->stock) {
                    $product->stock->available_stock -= $p['quantity'];
                    $product->stock->save();
                }
            }

            // Overall discount
            $overallDiscount = $validated['overall_discount'] ?? 0;
            $overallDiscountType = $validated['overall_discount_type'] ?? 'amount';
            $discountAmount = $overallDiscount > 0 ? ($overallDiscountType === 'percentage' ? ($overallDiscount / 100) * $totalAmount : $overallDiscount) : 0;
            $finalTotal = $totalAmount - $discountAmount;

            $sale->update([
                'salesperson_id' => $salespersonId,
                'customer_id' => $customer->id,
                'sale_date' => $validated['sale_date'],
                'total_amount' => $finalTotal,
            ]);

            // Handle new payments
            if (!empty($validated['payments'])) {
                foreach ($validated['payments'] as $pay) {
                    $salePayment = SalePayment::create([
                        'sale_id' => $sale->id,
                        'payment_method' => $pay['payment_method'],
                        'payment_amount' => $finalTotal,
                        'discount' => $overallDiscount,
                        'discount_type' => $overallDiscountType,
                        'payment_paid' => $pay['payment_paid'],
                        'paid_by' => $customer->id,
                        'paid_date' => $pay['paid_date'],
                        'bank_account_id' => $pay['bank_id'] ?? null,
                        'due_date' => $pay['due_date'] ?? null,
                    ]);

                    // Cheque payments
                    if ($pay['payment_method'] === 'cheque') {
                        ReceivingCheque::create([
                            'cheque_no' => $pay['cheque_no'] ?? 'CHEQUE-' . $sale->id . '-' . rand(1000, 9999),
                            'bank_id' => $pay['bank_id'] ?? null,
                            'paid_by' => $customer->name,
                            'status' => 'pending',
                            'paid_date' => $pay['paid_date'],
                            'cheque_date' => $pay['cheque_date'] ?? $pay['paid_date'],
                            'amount' => $pay['payment_paid'],
                            'reason' => 'Payment for Sale ID: ' . $sale->id,
                        ]);
                    }

                    // Loan payments
                    if ($pay['payment_method'] === 'loan') {
                        Loan::create([
                            'reference_number' => 'LN-' . $sale->id . '-' . rand(1000, 9999),
                            'customer_id' => $customer->id,
                            'invoice_id' => $sale->id,
                            'type' => Loan::TYPE_GIVEN,
                            'amount' => $pay['payment_paid'],
                            'note' => 'Loan created from sale payment',
                            'loan_date' => now(),
                            'due_date' => $pay['due_date'] ?? now()->addDays(30),
                            'status' => Loan::STATUS_PENDING,
                        ]);
                    }
                }
            }

            // Update customer balances after new payments
            $customer->total_paid += $cashPaid;
            $customer->balance_due += $loanAmount;
            $customer->save();
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale updated successfully.');
    }

    // ------------------ DESTROY ------------------
    public function destroy(Sale $sale)
    {
        DB::transaction(function () use ($sale) {
            $customer = $sale->customer;

            // Restore stock
            foreach ($sale->items as $item) {
                if ($item->product && $item->product->stock) {
                    $item->product->stock->available_stock += $item->quantity;
                    $item->product->stock->save();
                }
            }

            // Reverse payments
            $cashPaid = 0;
            foreach ($sale->salePayments as $payment) {
                if ($payment->payment_method !== 'loan') {
                    $cashPaid += $payment->payment_paid;
                }
            }
            $loanAmount = $sale->total_amount - $cashPaid;
            $customer->total_paid -= $cashPaid;
            $customer->balance_due -= $loanAmount;
            $customer->save();

            foreach ($sale->salePayments as $payment) {
                if ($payment->payment_method === 'cheque') {
                    ReceivingCheque::where('cheque_no', $payment->cheque_no)->delete();
                }
                if ($payment->payment_method === 'loan') {
                    Loan::where('invoice_id', $sale->id)->delete();
                }
                $payment->delete();
            }

            $sale->delete();
        });

        return redirect()->route('admin.sales.index')->with('success', 'Sale deleted successfully.');
    }

    // ------------------ PRINT ------------------
    public function print(Sale $sale)
    {
        $sale->load(['salesperson', 'items.product', 'salePayments.bank', 'customer']);

        $d2 = new DNS2D();
        $qrCode = $d2->getBarcodePNG(route('admin.sales.print', $sale->id), 'QRCODE');

        $pdf = Pdf::loadView('admin.sales.print', compact('sale', 'qrCode'));

        return $pdf->stream('sale_' . $sale->id . '.pdf');
    }

    public function show(Sale $sale)
    {
        $sale->load(['salesperson', 'items.product', 'salePayments.bank', 'customer']);

        $d2 = new DNS2D();
        $qrCode = $d2->getBarcodePNG(route('admin.sales.print', $sale->id), 'QRCODE');

        return view('admin.sales.show', compact('sale', 'qrCode'));
    }
}