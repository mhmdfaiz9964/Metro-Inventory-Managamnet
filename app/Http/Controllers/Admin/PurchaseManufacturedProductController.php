<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseManufacturedProduct;
use App\Models\PurchaseManufacturedProductItem;
use App\Models\PurchaseManufacturedProductPayment;
use App\Models\Supplier;
use App\Models\Products;
use App\Models\Bank;
use App\Models\BankAccount;
use App\Models\ProductCategory;
use App\Models\ProductBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PurchaseManufacturedProductController extends Controller
{
    public function index()
    {
        $purchases = PurchaseManufacturedProduct::with(['supplier', 'items.product', 'payments'])
            ->latest()
            ->paginate(10);

        return view('admin.manufactured_products.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Products::where('is_manufactured', 'yes')->get();
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();
        $categories = ProductCategory::all();
        $brands = ProductBrand::all();

        return view('admin.manufactured_products.create', compact(
            'suppliers',
            'products',
            'banks',
            'bankAccounts',
            'categories',
            'brands'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'total_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:amount,percentage',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.discount_type' => 'nullable|in:amount,percentage',
            'items.*.discount' => 'nullable|numeric|min:0',

            'payments' => 'nullable|array',
            'payments.*.payment_method' => [
                'required_with:payments',
                Rule::in(['cash', 'cheque', 'credit', 'fund_transfer'])
            ],
            'payments.*.payment_paid' => 'required_with:payments|numeric|min:0',
            'payments.*.paid_date' => 'required_with:payments|date',
            'payments.*.bank_id' => 'nullable|exists:banks,id|required_if:payments.*.payment_method,cheque',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id|required_if:payments.*.payment_method,fund_transfer',
            'payments.*.cheque_no' => 'nullable|string|max:50|required_if:payments.*.payment_method,cheque',
            'payments.*.cheque_date' => 'nullable|date|required_if:payments.*.payment_method,cheque',
            'payments.*.transfer_ref' => 'nullable|string|max:50',
            'payments.*.due_date' => 'nullable|date|required_if:payments.*.payment_method,credit',
        ]);

        DB::beginTransaction();
        try {
            // Create purchase
            $purchase = PurchaseManufacturedProduct::create([
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'total_price' => $request->total_price,
                'discount' => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'amount',
                'created_by' => Auth::id(),
            ]);

            // Store items
            foreach ($request->items as $item) {
                // Calculate line total
                $lineSubtotal = $item['qty'] * $item['cost_price'];
                $lineDiscount = ($item['discount_type'] ?? 'amount') === 'percentage'
                    ? $lineSubtotal * ($item['discount'] ?? 0) / 100
                    : ($item['discount'] ?? 0);
                $lineTotal = max(0, $lineSubtotal - min($lineDiscount, $lineSubtotal));

                PurchaseManufacturedProductItem::create([
                    'product_id' => $item['product_id'],
                    'cost_price' => $item['cost_price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'amount',
                    'total' => $lineTotal,
                ]);
            }

            // Store payments
            if (!empty($request->payments)) {
                foreach ($request->payments as $payment) {
                    $bankId = null;
                    $bankAccountId = null;
                    $dueDate = null;

                    if ($payment['payment_method'] === 'cheque') {
                        $bankId = $payment['bank_id'] ?? null;
                    } elseif ($payment['payment_method'] === 'fund_transfer') {
                        $bankAccountId = $payment['bank_account_id'] ?? null;
                    } elseif ($payment['payment_method'] === 'credit') {
                        $dueDate = $payment['due_date'] ?? null;
                    }

                    PurchaseManufacturedProductPayment::create([
                        'purchase_manufactured_product_id' => $purchase->id,
                        'payment_method' => $payment['payment_method'],
                        'bank_id' => $bankId,
                        'bank_account_id' => $bankAccountId,
                        'amount' => $payment['payment_paid'],
                        'date' => $payment['paid_date'],
                        'due_date' => $dueDate,
                        'cheque_no' => $payment['cheque_no'] ?? null,
                        'cheque_date' => $payment['cheque_date'] ?? null,
                        'transfer_ref' => $payment['transfer_ref'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.purchase_manufactured_products.index')
                ->with('success', 'Manufactured product purchase created successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);

        }
    }

    public function edit(PurchaseManufacturedProduct $manufactured_product)
    {
        $suppliers = Supplier::all();
        $products = Products::where('is_manufactured', 'yes')->get();
        $banks = Bank::all();
        $bankAccounts = BankAccount::all();
        $categories = ProductCategory::all();
        $brands = ProductBrand::all();

        $manufactured_product->load('items.product', 'payments');

        return view('admin.manufactured_products.edit', compact(
            'manufactured_product',
            'suppliers',
            'products',
            'banks',
            'bankAccounts',
            'categories',
            'brands'
        ));
    }

    public function update(Request $request, PurchaseManufacturedProduct $manufactured_product)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'total_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:amount,percentage',

            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.qty' => 'required|numeric|min:0.01',
            'items.*.discount_type' => 'nullable|in:amount,percentage',
            'items.*.discount' => 'nullable|numeric|min:0',

            'payments' => 'nullable|array',
            'payments.*.payment_method' => [
                'required_with:payments',
                Rule::in(['cash', 'cheque', 'credit', 'fund_transfer'])
            ],
            'payments.*.payment_paid' => 'required_with:payments|numeric|min:0',
            'payments.*.paid_date' => 'required_with:payments|date',
            'payments.*.bank_id' => 'nullable|exists:banks,id|required_if:payments.*.payment_method,cheque',
            'payments.*.bank_account_id' => 'nullable|exists:bank_accounts,id|required_if:payments.*.payment_method,fund_transfer',
            'payments.*.cheque_no' => 'nullable|string|max:50|required_if:payments.*.payment_method,cheque',
            'payments.*.cheque_date' => 'nullable|date|required_if:payments.*.payment_method,cheque',
            'payments.*.transfer_ref' => 'nullable|string|max:50',
            'payments.*.due_date' => 'nullable|date|required_if:payments.*.payment_method,credit',
        ]);

        DB::beginTransaction();
        try {
            $manufactured_product->update([
                'supplier_id' => $request->supplier_id,
                'date' => $request->date,
                'total_price' => $request->total_price,
                'discount' => $request->discount ?? 0,
                'discount_type' => $request->discount_type ?? 'amount',
            ]);

            // Delete old items and payments
            $manufactured_product->items()->delete();
            $manufactured_product->payments()->delete();

            // Store new items
            foreach ($request->items as $item) {
                // Calculate line total
                $lineSubtotal = $item['qty'] * $item['cost_price'];
                $lineDiscount = ($item['discount_type'] ?? 'amount') === 'percentage'
                    ? $lineSubtotal * ($item['discount'] ?? 0) / 100
                    : ($item['discount'] ?? 0);
                $lineTotal = max(0, $lineSubtotal - min($lineDiscount, $lineSubtotal));

                PurchaseManufacturedProductItem::create([
                    'purchase_manufactured_product_id' => $manufactured_product->id,
                    'product_id' => $item['product_id'],
                    'cost_price' => $item['cost_price'],
                    'qty' => $item['qty'],
                    'discount' => $item['discount'] ?? 0,
                    'discount_type' => $item['discount_type'] ?? 'amount',
                    'total' => $lineTotal,
                ]);
            }

            // Store new payments
            if (!empty($request->payments)) {
                foreach ($request->payments as $payment) {
                    $bankId = null;
                    $bankAccountId = null;
                    $dueDate = null;

                    if ($payment['payment_method'] === 'cheque') {
                        $bankId = $payment['bank_id'] ?? null;
                    } elseif ($payment['payment_method'] === 'fund_transfer') {
                        $bankAccountId = $payment['bank_account_id'] ?? null;
                    } elseif ($payment['payment_method'] === 'credit') {
                        $dueDate = $payment['due_date'] ?? null;
                    }

                    PurchaseManufacturedProductPayment::create([
                        'purchase_manufactured_product_id' => $manufactured_product->id,
                        'payment_method' => $payment['payment_method'],
                        'bank_id' => $bankId,
                        'bank_account_id' => $bankAccountId,
                        'amount' => $payment['payment_paid'],
                        'date' => $payment['paid_date'],
                        'due_date' => $dueDate,
                        'cheque_no' => $payment['cheque_no'] ?? null,
                        'cheque_date' => $payment['cheque_date'] ?? null,
                        'transfer_ref' => $payment['transfer_ref'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.purchase_manufactured_products.index')
                ->with('success', 'Manufactured product purchase updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->withErrors($request->validate()); // Use default validation errors
        }
    }

    public function destroy(PurchaseManufacturedProduct $manufactured_product)
    {
        DB::transaction(function () use ($manufactured_product) {
            $manufactured_product->payments()->delete();
            $manufactured_product->items()->delete();
            $manufactured_product->delete();
        });

        return redirect()->route('admin.purchase_manufactured_products.index')
            ->with('success', 'Manufactured product purchase deleted successfully.');
    }
}