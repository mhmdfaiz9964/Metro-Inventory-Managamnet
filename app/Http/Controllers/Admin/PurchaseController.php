<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\PurchaseBomProduct;
use App\Models\PurchasePayment;
use App\Models\Supplier;
use App\Models\Products;
use App\Models\BomComponent;
use App\Models\BomStock;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\Cheques;
use App\Models\FundTransfer;
use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    /** Display list of purchases */
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'creator', 'bomProducts.bomComponent', 'payments.bank', 'payments.bankAccount'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.purchase.index', compact('purchases'));
    }

    /** Partial table (if used) */
    public function table()
    {
        $purchases = Purchase::with(['supplier', 'creator', 'bomProducts.bomComponent', 'payments.bank', 'payments.bankAccount'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.purchase.table', compact('purchases'));
    }

    /** Show create form */
    public function create()
    {
        $suppliers = Supplier::all();
        $products = Products::all();
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();

        return view('admin.purchase.create', compact('suppliers', 'products', 'banks', 'bankAccounts'));
    }

    /** Store new purchase */
    public function store(Request $request)
    {
        $request->validate(
            [
                'supplier_id' => 'required|exists:suppliers,id',
                'purchase_date' => 'required|date',
                'status' => 'required|in:pending,received',
                'overall_discount' => 'nullable|numeric|min:0',
                'overall_discount_type' => ['nullable', Rule::in(['amount', 'percentage'])],

                'bom_products' => 'required|array|min:1|max:50',
                'bom_products.*.bom_id' => 'required|exists:bom_components,id|distinct',
                'bom_products.*.qty' => 'required|numeric|min:1|max:999',
                'bom_products.*.cost_price' => 'required|numeric|min:0',
                'bom_products.*.discount' => 'nullable|numeric|min:0',
                'bom_products.*.discount_type' => ['nullable', Rule::in(['amount', 'percentage'])],

                'payments' => 'nullable|array|max:10',
                'payments.*.payment_method' => 'required_with:payments|in:cash,cheque,loan,fund_transfer',
                'payments.*.payment_amount' => 'required_with:payments|numeric|min:0',
                'payments.*.payment_date' => 'required_with:payments|date|before_or_equal:purchase_date',
                'payments.*.bank_id' => 'nullable|exists:banks,id|required_if:payments.*.payment_method,cheque',
                'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id|required_if:payments.*.payment_method,fund_transfer',
                'payments.*.cheque_no' => 'nullable|string|max:255|required_if:payments.*.payment_method,cheque',
                'payments.*.cheque_date' => 'nullable|date|required_if:payments.*.payment_method,cheque',
                'payments.*.transfer_ref' => 'nullable|string|max:255', // New: For fund_transfer ref
                'payments.*.due_date' => 'nullable|date|required_if:payments.*.payment_method,loan',
            ],
            [
                'payments.*.payment_date.before_or_equal' => 'Payment date must be on or before purchase date.',
            ],
        );

        DB::beginTransaction();

        try {
            $paidAmount = collect($request->payments ?? [])->sum('payment_amount');

            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'product_id' => $request->product_id,
                'purchase_date' => $request->purchase_date,
                'paid_amount' => $paidAmount,
                'notes' => $request->notes ?? null,
                'payment_status' => $paidAmount > 0 ? 'paid' : 'not paid',
                'status' => $request->status,
                'created_by' => Auth::id(),
            ]);

            // Store BOM products & update stock
            $subtotal = 0;
            foreach ($request->bom_products as $bomData) {
                $bomComponent = BomComponent::findOrFail($bomData['bom_id']);
                $bomComponent->price = $bomData['cost_price'];
                $bomComponent->save();

                $lineSubtotal = $bomData['qty'] * $bomData['cost_price'];
                $lineDiscount = $bomData['discount'] ?? 0;
                $lineDiscountType = $bomData['discount_type'] ?? 'amount';
                $lineDiscountAmount = $lineDiscountType === 'percentage' ? $lineSubtotal * ($lineDiscount / 100) : $lineDiscount;
                $lineDiscountAmount = min($lineDiscountAmount, $lineSubtotal);
                $lineTotal = max(0, $lineSubtotal - $lineDiscountAmount);

                PurchaseBomProduct::create([
                    'purchase_id' => $purchase->id,
                    'bom_id' => $bomComponent->id,
                    'qty' => $bomData['qty'],
                    'cost_price' => $bomData['cost_price'],
                    'discount' => $lineDiscount,
                    'discount_type' => $lineDiscountType,
                    'total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;

                $bomStock = BomStock::firstOrNew(['bom_component_id' => $bomComponent->id]);
                $bomStock->available_stock = ($bomStock->available_stock ?? 0) + $bomData['qty'];
                $bomStock->save();
            }

            // Apply overall discount
            $overallValue = $request->overall_discount ?? 0;
            $overallType = $request->overall_discount_type ?? 'amount';
            $overallDiscountAmount = $overallType === 'percentage' ? $subtotal * ($overallValue / 100) : $overallValue;
            $overallDiscountAmount = min($overallDiscountAmount, $subtotal);
            $grandTotal = max(0, $subtotal - $overallDiscountAmount);

            $purchase->update([
                'subtotal' => round($subtotal, 2),
                'overall_discount' => $overallValue,
                'overall_discount_type' => $overallType,
                'overall_discount_amount' => round($overallDiscountAmount, 2),
                'grand_total' => $grandTotal,
            ]);

            // Store payments
            if (!empty($request->payments)) {
                foreach ($request->payments as $paymentData) {
                    $method = $paymentData['payment_method'];
                    $amount = $method === 'loan' ? 0 : $paymentData['payment_amount'] ?? 0;

                    $payment = PurchasePayment::create([
                        'purchase_id' => $purchase->id,
                        'supplier_id' => $request->supplier_id,
                        'payment_method' => $method,
                        'payment_amount' => $amount,
                        'payment_date' => $paymentData['payment_date'],
                        'bank_id' => $method === 'cheque' ? $paymentData['bank_id'] ?? null : null,
                        'bank_account_id' => $method === 'fund_transfer' ? $paymentData['bank_account_id'] ?? null : null,
                        'notes' => $request->notes ?? null,
                    ]);

                    // Cheque
                    if ($method === 'cheque' && !empty($paymentData['bank_id'])) {
                        $bank = Bank::find($paymentData['bank_id']);
                        if ($bank) {
                            Cheques::create([
                                'reason' => 'Purchase Payment',
                                'type' => 'Supplier_payment',
                                'note' => $request->notes ?? null,
                                'cheque_date' => $paymentData['cheque_date'] ?? now(),
                                'cheque_bank' => $bank->id,
                                'amount' => $amount,
                                'created_by' => Auth::id(),
                                'status' => 'pending',
                                'cheque_no' => $paymentData['cheque_no'] ?? null,
                            ]);
                        }
                    }

                    // Fund transfer
                    if ($method === 'fund_transfer' && !empty($paymentData['bank_account_id'])) {
                        $bankAccount = BankAccount::find($paymentData['bank_account_id']);
                        if ($bankAccount) {
                            $bankAccount->bank_balance -= $amount;
                            $bankAccount->save();

                            $transferRef = $paymentData['transfer_ref'] ?? ($paymentData['cheque_no'] ?? null); // New: Prefer transfer_ref

                            FundTransfer::create([
                                'to_bank_id' => $bankAccount->id,
                                'amount' => $amount,
                                'transfer_date' => $paymentData['payment_date'],
                                'reason' => 'Purchase Payment',
                                'status' => 'pending',
                                'note' => $request->notes ?? null,
                                'created_by' => Auth::id(),
                                'transferred_by' => Auth::id(),
                                'transfer_ref' => $transferRef,
                            ]);
                        }
                    }
                }
            }

            // Remaining balance → Loan (handle overpay as 0)
            $remainingBalance = max(0, $grandTotal - $paidAmount);
            if ($remainingBalance > 0) {
                Loan::create([
                    'supplier_id' => $request->supplier_id,
                    'purchase_id' => $purchase->id,
                    'type' => Loan::TYPE_PAYABLE,
                    'amount' => $remainingBalance,
                    'loan_date' => now(),
                    'due_date' => now()->addDays(30),
                    'status' => Loan::STATUS_PENDING,
                    'note' => 'Remaining balance for purchase #' . $purchase->id,
                ]);
            }

            $this->updateSupplierBalance($request->supplier_id);

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Failed to create purchase. ' . $e->getMessage(),
                ]);
        }
    }
    /** Show edit form */
    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::all();
        $products = Products::all();
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();

        return view('admin.purchase.edit', compact('purchase', 'suppliers', 'products', 'banks', 'bankAccounts'));
    }
    /** Update purchase */
    public function update(Request $request, Purchase $purchase)
    {
        $request->validate(
            [
                'supplier_id' => 'required|exists:suppliers,id',
                'purchase_date' => 'required|date',
                'status' => 'required|in:pending,received',
                'overall_discount' => 'nullable|numeric|min:0',
                'overall_discount_type' => ['nullable', Rule::in(['amount', 'percentage'])],

                'bom_products' => 'required|array|min:1|max:50',
                'bom_products.*.bom_id' => 'required|exists:bom_components,id|distinct',
                'bom_products.*.qty' => 'required|numeric|min:1|max:999',
                'bom_products.*.cost_price' => 'required|numeric|min:0',
                'bom_products.*.discount' => 'nullable|numeric|min:0',
                'bom_products.*.discount_type' => ['nullable', Rule::in(['amount', 'percentage'])],

                'payments' => 'nullable|array|max:10',
                'payments.*.payment_method' => 'required_with:payments|in:cash,cheque,loan,fund_transfer',
                'payments.*.payment_amount' => 'required_with:payments|numeric|min:0',
                'payments.*.payment_date' => 'required_with:payments|date|before_or_equal:purchase_date',
                'payments.*.bank_id' => 'nullable|exists:banks,id|required_if:payments.*.payment_method,cheque',
                'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id|required_if:payments.*.payment_method,fund_transfer',
                'payments.*.cheque_no' => 'nullable|string|max:255|required_if:payments.*.payment_method,cheque',
                'payments.*.cheque_date' => 'nullable|date|required_if:payments.*.payment_method,cheque',
                'payments.*.transfer_ref' => 'nullable|string|max:255', // New
                'payments.*.due_date' => 'nullable|date|required_if:payments.*.payment_method,loan',
            ],
            [
                'payments.*.payment_date.before_or_equal' => 'Payment date must be on or before purchase date.',
            ],
        );

        DB::beginTransaction();

        try {
            // Rollback old BOM stock
            foreach ($purchase->bomProducts as $oldBom) {
                $bomStock = BomStock::where('bom_component_id', $oldBom->bom_id)->first();
                if ($bomStock) {
                    $bomStock->available_stock -= $oldBom->qty;
                    $bomStock->available_stock = max($bomStock->available_stock, 0);
                    $bomStock->save();
                }
            }

            // Delete old BOM & payments (also delete old loans/funds/cheques if needed)
            $purchase->bomProducts()->delete();
            $purchase->payments()->delete();

            // Update purchase
            $paidAmount = collect($request->payments ?? [])->sum('payment_amount');
            $purchase->update([
                'supplier_id' => $request->supplier_id,
                'product_id' => $request->product_id,
                'purchase_date' => $request->purchase_date,
                'paid_amount' => $paidAmount,
                'notes' => $request->notes ?? null,
                'payment_status' => $paidAmount > 0 ? 'paid' : 'not paid',
                'status' => $request->status,
            ]);

            // Store BOM products (same as store)
            $subtotal = 0;
            foreach ($request->bom_products as $bomData) {
                $bomComponent = BomComponent::findOrFail($bomData['bom_id']);
                $bomComponent->price = $bomData['cost_price'];
                $bomComponent->save();

                $lineSubtotal = $bomData['qty'] * $bomData['cost_price'];
                $lineDiscount = $bomData['discount'] ?? 0;
                $lineDiscountType = $bomData['discount_type'] ?? 'amount';
                $lineDiscountAmount = $lineDiscountType === 'percentage' ? $lineSubtotal * ($lineDiscount / 100) : $lineDiscount;
                $lineDiscountAmount = min($lineDiscountAmount, $lineSubtotal);
                $lineTotal = max(0, $lineSubtotal - $lineDiscountAmount);

                PurchaseBomProduct::create([
                    'purchase_id' => $purchase->id,
                    'bom_id' => $bomComponent->id,
                    'qty' => $bomData['qty'],
                    'cost_price' => $bomData['cost_price'],
                    'discount' => $lineDiscount,
                    'discount_type' => $lineDiscountType,
                    'total' => $lineTotal,
                ]);

                $subtotal += $lineTotal;

                $bomStock = BomStock::firstOrNew(['bom_component_id' => $bomComponent->id]);
                $bomStock->available_stock = ($bomStock->available_stock ?? 0) + $bomData['qty'];
                $bomStock->save();
            }

            // Apply overall discount
            $overallValue = $request->overall_discount ?? 0;
            $overallType = $request->overall_discount_type ?? 'amount';
            $overallDiscountAmount = $overallType === 'percentage' ? $subtotal * ($overallValue / 100) : $overallValue;
            $overallDiscountAmount = min($overallDiscountAmount, $subtotal);
            $grandTotal = max(0, $subtotal - $overallDiscountAmount);

            $purchase->update([
                'subtotal' => round($subtotal, 2),
                'overall_discount' => $overallValue,
                'overall_discount_type' => $overallType,
                'overall_discount_amount' => round($overallDiscountAmount, 2),
                'grand_total' => $grandTotal,
            ]);

            // Store payments (same as store)
            if (!empty($request->payments)) {
                foreach ($request->payments as $paymentData) {
                    $method = $paymentData['payment_method'];
                    $amount = $method === 'loan' ? 0 : $paymentData['payment_amount'] ?? 0;

                    $payment = PurchasePayment::create([
                        'purchase_id' => $purchase->id,
                        'supplier_id' => $request->supplier_id,
                        'payment_method' => $method,
                        'payment_amount' => $amount,
                        'payment_date' => $paymentData['payment_date'],
                        'bank_id' => $method === 'cheque' ? $paymentData['bank_id'] ?? null : null,
                        'bank_account_id' => $method === 'fund_transfer' ? $paymentData['bank_account_id'] ?? null : null,
                        'notes' => $request->notes ?? null,
                    ]);

                    // Cheque
                    if ($method === 'cheque' && !empty($paymentData['bank_id'])) {
                        $bank = Bank::find($paymentData['bank_id']);
                        if ($bank) {
                            Cheques::updateOrCreate(
                                ['cheque_no' => $paymentData['cheque_no'], 'cheque_bank' => $bank->id],
                                [
                                    'reason' => 'Purchase Payment',
                                    'type' => 'Supplier_paymen',
                                    'note' => $request->notes ?? null,
                                    'cheque_date' => $paymentData['cheque_date'] ?? now(),
                                    'amount' => $amount,
                                    'created_by' => Auth::id(),
                                    'status' => 'pending',
                                ],
                            );
                        }
                    }

                    // Fund transfer
                    if ($method === 'fund_transfer' && !empty($paymentData['bank_account_id'])) {
                        $bankAccount = BankAccount::find($paymentData['bank_account_id']);
                        if ($bankAccount) {
                            $bankAccount->bank_balance -= $amount;
                            $bankAccount->save();

                            $transferRef = $paymentData['transfer_ref'] ?? ($paymentData['cheque_no'] ?? null);

                            FundTransfer::updateOrCreate(
                                ['transfer_ref' => $transferRef, 'from_bank_id' => $bankAccount->id],
                                [
                                    'amount' => $amount,
                                    'transfer_date' => $paymentData['payment_date'],
                                    'reason' => 'Purchase Payment',
                                    'status' => 'pending',
                                    'note' => $request->notes ?? null,
                                    'transferred_by' => Auth::id(),
                                    'created_by' => Auth::id(),
                                ],
                            );
                        }
                    }
                }
            }

            // Remaining balance → Loan
            $remainingBalance = max(0, $grandTotal - $paidAmount);
            $loan = Loan::updateOrCreate(
                ['purchase_id' => $purchase->id],
                [
                    'supplier_id' => $request->supplier_id,
                    'type' => Loan::TYPE_PAYABLE,
                    'amount' => $remainingBalance,
                    'loan_date' => now(),
                    'due_date' => now()->addDays(30),
                    'status' => $remainingBalance > 0 ? Loan::STATUS_PENDING : Loan::STATUS_PAID,
                    'note' => 'Remaining balance for purchase #' . $purchase->id,
                ],
            );

            if ($remainingBalance <= 0) {
                $loan->delete();
            }

            $this->updateSupplierBalance($request->supplier_id);

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->withErrors([
                    'error' => 'Failed to update purchase. ' . $e->getMessage(),
                ]);
        }
    }

    /** Delete purchase */
    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        try {
            foreach ($purchase->bomProducts as $bom) {
                $stock = BomStock::where('bom_component_id', $bom->bom_id)->first();
                if ($stock) {
                    $stock->available_stock -= $bom->qty;
                    $stock->available_stock = max(0, $stock->available_stock);
                    $stock->save();
                }
            }

            $purchase->bomProducts()->delete();
            $purchase->payments()->delete();
            $purchase->delete();

            $this->updateSupplierBalance($purchase->supplier_id);

            DB::commit();
            return redirect()->route('admin.purchases.index')->with('success', 'Purchase deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to delete purchase. ' . $e->getMessage()]);
        }
    }

    /** Update supplier balance */
    protected function updateSupplierBalance($supplierId)
    {
        $supplier = Supplier::find($supplierId);
        if (!$supplier) {
            return;
        }

        $totalPurchases = Purchase::where('supplier_id', $supplierId)->sum('grand_total');
        $totalPaid = PurchasePayment::where('supplier_id', $supplierId)->sum('payment_amount');
        $supplier->balance_due = max(0, $totalPurchases - $totalPaid);
        $supplier->save();
    }
}
