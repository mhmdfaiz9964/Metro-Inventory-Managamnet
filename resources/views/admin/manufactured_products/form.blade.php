@php
    // Prepare items array
    $items = old(
        'items',
        isset($manufactured_product) && $manufactured_product->items
            ? $manufactured_product->items
                ->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'cost_price' => $item->cost_price,
                        'qty' => $item->qty ?? 1,
                        'discount' => $item->discount ?? 0,
                        'discount_type' => $item->discount_type ?? 'amount',
                        'total' => $item->total ?? 0,
                    ];
                })
                ->toArray()
            : [],
    );

    if (empty($items)) {
        $items = [
            [
                'product_id' => '',
                'cost_price' => 0,
                'qty' => 1,
                'discount' => 0,
                'discount_type' => 'amount',
                'total' => 0,
            ],
        ];
    }

    // Prepare payments array
    $payments = old(
        'payments',
        isset($manufactured_product) && $manufactured_product->payments
            ? $manufactured_product->payments
                ->map(function ($payment) {
                    return [
                        'payment_method' => $payment->payment_method,
                        'payment_paid' => $payment->amount,  // Map from amount
                        'paid_date' => $payment->date,
                        'bank_id' => $payment->bank_id,
                        'bank_account_id' => $payment->bank_account_id,
                        'cheque_no' => $payment->cheque_no,
                        'cheque_date' => $payment->cheque_date,
                        'transfer_ref' => $payment->transfer_ref,
                        'due_date' => $payment->due_date ?? null,
                    ];
                })
                ->toArray()
            : [],
    );

    if (empty($payments)) {
        $payments = [
            [
                'payment_method' => 'cash',
                'payment_paid' => 0,
                'paid_date' => date('Y-m-d'),
                'bank_id' => null,
                'bank_account_id' => null,
                'cheque_no' => '',
                'cheque_date' => date('Y-m-d'),
                'transfer_ref' => '',
                'due_date' => date('Y-m-d'),
            ],
        ];
    }

    // Prepare overall discount
    $overallDiscount = old('discount', isset($manufactured_product) ? $manufactured_product->discount ?? 0 : 0);
    $overallDiscountType = old(
        'discount_type',
        isset($manufactured_product) ? $manufactured_product->discount_type ?? 'amount' : 'amount',
    );

    // Prepare products options HTML for JS
    $productsOptions = '';
    foreach ($products as $product) {
        $productsOptions .= '<option value="' . $product->id . '">' . $product->name . '</option>';
    }

    // Prepare error message for SweetAlert
    $sessionError = session('error');
    $validationErrors = $errors->all();
    $hasErrors = !empty($sessionError) || !empty($validationErrors);
    $errorHtml = '';
    if ($sessionError) {
        $errorHtml = '<p>' . $sessionError . '</p>';
    } elseif (!empty($validationErrors)) {
        $errorHtml =
            '<ul class="mb-0">' .
            implode('', array_map(fn($error) => '<li>' . $error . '</li>', $validationErrors)) .
            '</ul>';
    }
@endphp

@if (isset($manufactured_product))
    <form method="POST" action="{{ route('admin.purchase_manufactured_products.update', $manufactured_product) }}"
        enctype="multipart/form-data">
        @method('PUT')
    @else
        <form method="POST" action="{{ route('admin.purchase_manufactured_products.store') }}"
            enctype="multipart/form-data">
@endif
@csrf

<div class="container-fluid py-4">
    <div class="row g-3">

        {{-- Supplier Selection --}}
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
            <div class="input-group">
                <select name="supplier_id" id="supplier-select"
                    class="form-select {{ $errors->has('supplier_id') ? 'is-invalid' : '' }}" required>
                    <option value="">-- Select a Supplier --</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            {{ old('supplier_id', isset($manufactured_product) ? $manufactured_product->supplier_id : '') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }} ({{ $supplier->phone ?? '' }})
                        </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-success" id="add-new-supplier" title="Add New Supplier">
                    <i class="bi bi-plus-circle"></i>
                </button>
            </div>
            <div class="form-text">Select an existing supplier or add a new one.</div>
            @error('supplier_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Purchase Date --}}
        <div class="col-12 col-md-4">
            <label for="date" class="form-label fw-semibold">Purchase Date <span
                    class="text-danger">*</span></label>
            <input type="date" name="date" id="date"
                value="{{ old('date', isset($manufactured_product) ? $manufactured_product->date : date('Y-m-d')) }}"
                class="form-control {{ $errors->has('date') ? 'is-invalid' : '' }}" required>
            <div class="form-text">Date of the purchase transaction.</div>
            @error('date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Items Section --}}
        <div class="col-12 {{ $errors->has('items') ? 'is-invalid' : '' }}">
            <label class="form-label fw-semibold mb-2">Manufactured Product Items <span
                    class="text-danger">*</span></label>

            {{-- Product Search Bar --}}
            <div class="mb-3 position-relative">
                <label for="product-search" class="form-label">Quick Search & Add Product</label>
                <input type="text" id="product-search" class="form-control" placeholder="Search products by name...">
                <div id="product-search-results"
                    class="position-absolute z-3 w-100 bg-white border rounded shadow top-100 start-0"
                    style="display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-2">
                <button type="button" id="add-new-product" class="btn btn-primary btn-sm me-2 shadow-sm"
                    title="Add New Product" style="font-weight: bold;">
                    <i class="bi bi-plus-circle"></i> New Product
                </button>
                <button type="button" id="add-item" class="btn btn-success btn-sm shadow-sm"
                    title="Add a new item to the purchase" style="font-weight: bold;">
                    <i class="bi bi-plus-circle"></i> Add Item
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="items-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 30%;">Product</th>
                                <th scope="col" style="width: 10%;">Qty</th>
                                <th scope="col" style="width: 15%;">Unit Cost</th>
                                <th scope="col" style="width: 20%;">Discount</th>
                                <th scope="col" style="width: 15%;">Disc Amt</th>
                                <th scope="col" style="width: 10%;">Line Total</th>
                                <th scope="col" style="width: 10%;">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                                <tr class="align-middle">
                                    <td>
                                        <select name="items[{{ $index }}][product_id]"
                                            class="form-select item-select {{ $errors->has("items.$index.product_id") ? 'is-invalid' : '' }}"
                                            required>
                                            <option value="">-- Select Product --</option>
                                            @foreach ($products as $product)
                                                <option value="{{ $product->id }}"
                                                    {{ $item['product_id'] == $product->id ? 'selected' : '' }}>
                                                    {{ $product->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][qty]"
                                            class="form-control qty-input text-center {{ $errors->has("items.$index.qty") ? 'is-invalid' : '' }}"
                                            value="{{ $item['qty'] }}" min="0.01" max="999" step="0.01"
                                            placeholder="0.01" title="Enter quantity">
                                    </td>
                                    <td class="text-end">
                                        <input type="number" name="items[{{ $index }}][cost_price]"
                                            class="form-control cost-input text-end {{ $errors->has("items.$index.cost_price") ? 'is-invalid' : '' }}"
                                            value="{{ $item['cost_price'] }}" step="0.01" placeholder="0.00"
                                            title="Enter cost price">
                                    </td>
                                    <td>
                                        <div class="input-group" style="gap: 2px;">
                                            <!-- Discount input -->
                                            <input type="number" name="items[{{ $index }}][discount]"
                                                class="form-control discount-input {{ $errors->has("items.$index.discount") ? 'is-invalid' : '' }}"
                                                value="{{ $item['discount'] }}" step="0.01" min="0"
                                                placeholder="0.00" title="Enter discount amount or percentage"
                                                style="width: 80px;">

                                            <!-- Discount type select -->
                                            <select name="items[{{ $index }}][discount_type]"
                                                class="form-select discount-type-input rounded-start-0 {{ $errors->has("items.$index.discount_type") ? 'is-invalid' : '' }}"
                                                style="width: 40px;">
                                                <option value="percentage"
                                                    {{ ($item['discount_type'] ?? 'amount') == 'percentage' ? 'selected' : '' }}>
                                                    %</option>
                                                <option value="amount"
                                                    {{ ($item['discount_type'] ?? 'amount') == 'amount' ? 'selected' : '' }}>
                                                    Amt</option>
                                            </select>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <span class="disc-amt fw-semibold text-muted">0.00</span>
                                    </td>
                                    <td class="text-end">
                                        <input type="number" name="items[{{ $index }}][total]"
                                            class="form-control total-input text-end fw-semibold"
                                            value="{{ $item['total'] }}" step="0.01" readonly placeholder="0.00"
                                            title="Auto-calculated total">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm p-1 remove-item"
                                            title="Remove this item line">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <th colspan="5" class="text-end">Subtotal:</th>
                                <th class="text-end"><input type="number" id="subtotal"
                                        class="form-control fw-bold bg-transparent border-0 text-end" value=""
                                        step="0.01" readonly placeholder="0.00"></th>
                                <th></th>
                            </tr>
                            <tr class="fw-semibold">
                                <th colspan="5" class="text-end text-danger">Line Discounts:</th>
                                <th class="text-end"><input type="number" id="total_discount"
                                        class="form-control fw-bold text-danger bg-transparent border-0 text-end"
                                        value="" step="0.01" readonly placeholder="0.00"></th>
                                <th></th>
                            </tr>
                            <tr class="fw-semibold text-dark" style="background-color: #d7dbdf !important;">
                                <th colspan="3" class="text-end pe-0">
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="discount" id="overall_discount_value"
                                            class="form-control fw-bold text-end {{ $errors->has('discount') ? 'is-invalid' : '' }}"
                                            value="{{ $overallDiscount }}" step="0.01" min="0"
                                            placeholder="0.00" title="Overall discount value">
                                        <select name="discount_type" id="overall_discount_type"
                                            class="form-select form-select-sm {{ $errors->has('discount_type') ? 'is-invalid' : '' }}">
                                            <option value="amount"
                                                {{ $overallDiscountType == 'amount' ? 'selected' : '' }}>Amt</option>
                                            <option value="percentage"
                                                {{ $overallDiscountType == 'percentage' ? 'selected' : '' }}>%</option>
                                        </select>
                                    </div>
                                </th>
                                <th class="text-end">Overall Discount:</th>
                                <th class="text-end"><input type="number" id="overall_discount_amount"
                                        class="form-control fw-bold bg-transparent border-0 text-end" value=""
                                        step="0.01" readonly placeholder="0.00"></th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr class="bg-primary text-white">
                                <th colspan="6" class="text-end">Final Grand Total:</th>
                                <th class="text-end"><input type="number" id="grand_total"
                                        class="form-control fw-bold text-white bg-transparent border-0 text-end fs-5"
                                        value="" step="0.01" readonly placeholder="0.00"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <input type="hidden" name="total_price" id="total_price"
                value="{{ old('total_price', isset($manufactured_product) ? $manufactured_product->total_price : 0) }}">
            <div class="form-text small text-muted mt-1">Apply overall discount on the subtotal after line discounts.
                Changes auto-update the final total.</div>
            @error('items')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @error('total_price')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        {{-- Payments Section --}}
        <div class="col-12 {{ $errors->has('payments') ? 'is-invalid' : '' }}">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-semibold mb-0">Payments</label>
                <button type="button" id="add-payment"
                    class="btn btn-outline-success btn-sm d-flex align-items-center gap-1 shadow-sm"
                    title="Add a new payment method">
                    <i class="bi bi-plus-circle"></i> Add Payment
                </button>
            </div>
            <div id="payments-wrapper" class="border rounded-3 p-3 bg-light">
                @foreach ($payments as $i => $pay)
                    <div class="row g-2 mb-3 payment-row align-items-end">
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold">Payment Method <span
                                    class="text-danger">*</span></label>
                            <select name="payments[{{ $i }}][payment_method]"
                                class="form-select payment-method-select {{ $errors->has("payments.$i.payment_method") ? 'is-invalid' : '' }}"
                                required>
                                <option value="cash" {{ $pay['payment_method'] == 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="cheque" {{ $pay['payment_method'] == 'cheque' ? 'selected' : '' }}>Cheque
                                </option>
                                <option value="credit" {{ $pay['payment_method'] == 'credit' ? 'selected' : '' }}>Credit
                                </option>
                                <option value="fund_transfer"
                                    {{ $pay['payment_method'] == 'fund_transfer' ? 'selected' : '' }}>Fund Transfer</option>
                            </select>
                            @error("payments.$i.payment_method")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold">Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="payments[{{ $i }}][payment_paid]"
                                value="{{ $pay['payment_paid'] ?? '' }}"
                                class="form-control payment-paid-input text-end {{ $errors->has("payments.$i.payment_paid") ? 'is-invalid' : '' }}"
                                placeholder="0.00" step="0.01" min="0" required
                                title="Enter payment amount">
                            @error("payments.$i.payment_paid")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold">Payment Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="payments[{{ $i }}][paid_date]"
                                value="{{ $pay['paid_date'] ?? date('Y-m-d') }}"
                                class="form-control {{ $errors->has("payments.$i.paid_date") ? 'is-invalid' : '' }}"
                                required title="Payment date">
                            @error("payments.$i.paid_date")
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12 col-md-4 cheque-fields"
                            style="display: {{ $pay['payment_method'] == 'cheque' ? 'block' : 'none' }};">
                            <label class="form-label small fw-semibold">Cheque Details</label>
                            <select name="payments[{{ $i }}][bank_id]"
                                class="form-select {{ $errors->has("payments.$i.bank_id") ? 'is-invalid' : '' }}"
                                title="Select bank">
                                <option value="">-- Select Bank --</option>
                                @foreach ($banks as $bank)
                                    <option value="{{ $bank->id }}"
                                        {{ ($pay['bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="payments[{{ $i }}][cheque_no]"
                                value="{{ $pay['cheque_no'] ?? '' }}"
                                class="form-control mt-1 {{ $errors->has("payments.$i.cheque_no") ? 'is-invalid' : '' }}"
                                placeholder="Cheque No" title="Enter cheque number">
                            <input type="date" name="payments[{{ $i }}][cheque_date]"
                                value="{{ $pay['cheque_date'] ?? date('Y-m-d') }}"
                                class="form-control mt-1 {{ $errors->has("payments.$i.cheque_date") ? 'is-invalid' : '' }}"
                                title="Cheque issue date">
                        </div>
                        <div class="col-12 col-md-4 fund-transfer-fields"
                            style="display: {{ $pay['payment_method'] == 'fund_transfer' ? 'block' : 'none' }};">
                            <label class="form-label small fw-semibold">Transfer Details <span
                                    class="text-danger">*</span></label>
                            <select name="payments[{{ $i }}][bank_account_id]"
                                class="form-select {{ $errors->has("payments.$i.bank_account_id") ? 'is-invalid' : '' }}"
                                title="Select bank account">
                                <option value="">-- Select Bank Account --</option>
                                @foreach ($bankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}"
                                        {{ ($pay['bank_account_id'] ?? '') == $bankAccount->id ? 'selected' : '' }}>
                                        {{ $bankAccount->bank_name }} ({{ $bankAccount->account_number }})</option>
                                @endforeach
                            </select>
                            <input type="text" name="payments[{{ $i }}][transfer_ref]"
                                value="{{ $pay['transfer_ref'] ?? '' }}" class="form-control mt-1"
                                placeholder="Transfer Ref" title="Enter transfer reference">
                        </div>
                        <div class="col-12 col-md-4 credit-fields"
                            style="display: {{ $pay['payment_method'] == 'credit' ? 'block' : 'none' }};">
                            <label class="form-label small fw-semibold">Due Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="payments[{{ $i }}][due_date]"
                                value="{{ $pay['due_date'] ?? date('Y-m-d') }}"
                                class="form-control {{ $errors->has("payments.$i.due_date") ? 'is-invalid' : '' }}"
                                title="Due date for credit">
                        </div>
                        <div class="col-auto">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100 p-0 remove-payment"
                                title="Remove this payment" style="height: 38px;">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <strong>Total Paid:</strong> <span id="total-paid" class="fw-bold text-success">0.00</span>
                </div>
                <div class="col-md-6">
                    <strong>Balance Due:</strong> <span id="balance-due" class="fw-bold text-danger">0.00</span>
                </div>
            </div>
            @error('payments')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Submit Button --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="mt-4 d-flex justify-content-between">
                <a href="javascript:void(0)" onclick="window.history.back()"
                    class="btn btn-outline-secondary">Back</a>
                <button type="submit" id="submit-purchase" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    {{ isset($manufactured_product) ? 'Update Purchase' : 'Create Purchase' }}
                </button>
            </div>
        </div>
    </div>
</div>

</form>

{{-- New Supplier Modal --}}
<div class="modal fade" id="newSupplierModal" tabindex="-1" aria-labelledby="newSupplierModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newSupplierModalLabel">Add New Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newSupplierForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="supplier_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="supplier_name" name="name" required
                            maxlength="255">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="supplier_phone" class="form-label">Phone</label>
                        <input type="text" class="form-control" id="supplier_phone" name="phone">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="supplier_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="supplier_email" name="email">
                        <div class="invalid-feedback"></div>
                    </div>
                    <input type="hidden" name="status" value="active">
                    <div id="supplier-errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- New Product Modal --}}
<div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newProductModalLabel">Add New Manufactured Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="newProductForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="product_name" class="form-label">Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_name" name="name" required
                                maxlength="255">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="product_code" class="form-label">Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_code" name="code" required
                                maxlength="100">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="product_category_id" class="form-label">Category <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="product_category_id" name="product_category_id" required>
                                <option value="">-- Select Category --</option>
                                @foreach ($categories ?? [] as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="product_supplier_id" class="form-label">Supplier</label>
                            <select class="form-select" id="product_supplier_id" name="supplier_id">
                                <option value="">-- Select Supplier (Optional) --</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="product_brand_id" class="form-label">Brand</label>
                            <select class="form-select" id="product_brand_id" name="product_brand_id">
                                <option value="">-- Select Brand (Optional) --</option>
                                @foreach ($brands ?? [] as $brand)
                                    <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label for="product_status" class="form-label">Status <span
                                    class="text-danger">*</span></label>
                            <select class="form-select" id="product_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                                <option value="draft">Draft</option>
                            </select>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="product_description" class="form-label">Description</label>
                            <textarea class="form-control" id="product_description" name="description" rows="2"></textarea>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="product_regular_price" class="form-label">Regular Price <span
                                    class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="product_regular_price"
                                name="regular_price" required min="0" step="0.01">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="product_wholesale_price" class="form-label">Wholesale Price</label>
                            <input type="number" class="form-control" id="product_wholesale_price"
                                name="wholesale_price" min="0" step="0.01">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-4">
                            <label for="product_sale_price" class="form-label">Sale Price</label>
                            <input type="number" class="form-control" id="product_sale_price" name="sale_price"
                                min="0" step="0.01">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Warranty <span class="text-danger">*</span></label>
                            <select name="warranty" class="form-select @error('warranty') is-invalid @enderror"
                                required>
                                @foreach (['No warranty', '1 month', '3 months', '6 months', '12 months', '24 months'] as $w)
                                    <option value="{{ $w }}"
                                        {{ old('warranty', $product->warranty ?? '') == $w ? 'selected' : '' }}>
                                        {{ $w }}
                                    </option>
                                @endforeach
                            </select>
                            @error('warranty')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="product_weight" class="form-label">Weight</label>
                            <input type="number" class="form-control" id="product_weight" name="weight"
                                min="0" step="0.01">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="product_model" class="form-label">Model</label>
                            <input type="text" class="form-control" id="product_model" name="model"
                                maxlength="255">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="col-12">
                            <label for="product_image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="product_image" name="image"
                                accept="image/*">
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div id="product-errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .cost-input,
    .total-input,
    .payment-paid-input,
    .qty-input {
        min-width: 80px !important;
    }

    .discount-input {
        min-width: 120px !important;
    }

    .input-group input {
        min-width: 60px !important;
    }
</style>

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if ($hasErrors)
            Swal.fire({
                icon: 'error',
                title: 'Validation Error!',
                html: {!! json_encode($errorHtml) !!},
                confirmButtonText: 'OK'
            });
        @endif

        document.addEventListener('DOMContentLoaded', function() {
            let itemIndex = {{ count($items) }};
            let paymentIndex = {{ count($payments) }};

            // All products data for search
            const allProducts = {!! json_encode(
                $products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                    ];
                }),
            ) !!};

            // Products options HTML
            let productsOptions = `{!! addslashes($productsOptions) !!}`;

            // Handle number input focus/blur to clear default 0
            document.addEventListener('focus', function(e) {
                if (e.target.type === 'number' && (e.target.value === '0' || e.target.value === '0.00' || e
                        .target.value === '')) {
                    e.target.value = '';
                }
            }, true);

            document.addEventListener('blur', function(e) {
                if (e.target.type === 'number' && (e.target.value === '' || isNaN(parseFloat(e.target
                        .value)))) {
                    if (e.target.classList.contains('qty-input')) {
                        e.target.value = '1';
                    } else {
                        e.target.value = '0.00';
                    }
                } else if (e.target.type === 'number' && !e.target.readOnly) {
                    // Format to 2 decimals without leading zeros
                    const num = parseFloat(e.target.value);
                    if (!isNaN(num)) {
                        e.target.value = num.toFixed(2).replace(/^0+/, '');
                        if (e.target.value === '.') e.target.value = '0.00';
                    }
                }
            }, true);

            // Update payment fields visibility and required attributes
            function updatePaymentFields(paymentRow) {
                const methodSelect = paymentRow.querySelector('.payment-method-select');
                const method = methodSelect.value;
                const amountInput = paymentRow.querySelector('.payment-paid-input');
                const chequeFields = paymentRow.querySelector('.cheque-fields');
                const fundFields = paymentRow.querySelector('.fund-transfer-fields');
                const creditFields = paymentRow.querySelector('.credit-fields');

                // Cheque fields
                if (chequeFields) {
                    chequeFields.style.display = method === 'cheque' ? 'block' : 'none';
                    const bankCheque = chequeFields.querySelector('select[name*="[bank_id]"]');
                    if (bankCheque) bankCheque.required = method === 'cheque';
                    const chequeNo = chequeFields.querySelector('input[name*="[cheque_no]"]');
                    if (chequeNo) chequeNo.required = method === 'cheque';
                    const chequeDate = chequeFields.querySelector('input[name*="[cheque_date]"]');
                    if (chequeDate) chequeDate.required = method === 'cheque';
                }

                // Fund transfer fields
                if (fundFields) {
                    fundFields.style.display = method === 'fund_transfer' ? 'block' : 'none';
                    const bankFund = fundFields.querySelector('select[name*="[bank_account_id]"]');
                    if (bankFund) bankFund.required = method === 'fund_transfer';
                    const transferRef = fundFields.querySelector('input[name*="[transfer_ref]"]');
                    if (transferRef) transferRef.required = false; // Optional
                }

                // Credit fields
                if (creditFields) {
                    creditFields.style.display = method === 'credit' ? 'block' : 'none';
                    const dueDate = creditFields.querySelector('input[name*="[due_date]"]');
                    if (dueDate) dueDate.required = method === 'credit';
                }

                recalcTotalPaid();
            }

            // Calculate total paid
            function calculateTotalPaid() {
                let total = 0;
                document.querySelectorAll('.payment-paid-input').forEach(input => {
                    total += parseFloat(input.value) || 0;
                });
                return total;
            }

            // Recalculate total paid and balance due
            function recalcTotalPaid() {
                const totalPaid = calculateTotalPaid();
                document.getElementById('total-paid').textContent = totalPaid.toFixed(2);
                const grandTotal = parseFloat(document.getElementById('grand_total').value) || 0;
                const balanceDue = Math.max(0, grandTotal - totalPaid);
                document.getElementById('balance-due').textContent = balanceDue.toFixed(2);
            }

            // Check if has credit payment
            function hasCreditPayment() {
                return Array.from(document.querySelectorAll('.payment-method-select')).some(select => select
                    .value === 'credit');
            }

            // Update submit button state
            function updateSubmitButton() {
                const btn = document.getElementById('submit-purchase');
                const isValid = isFormValid();
                if (!isValid) {
                    btn.disabled = true;
                    btn.classList.add('disabled');
                } else {
                    btn.disabled = false;
                    btn.classList.remove('disabled');
                }
            }

            // Simple form validity check (expand as needed)
            function isFormValid() {
                // Check required fields, etc.
                return true; // Placeholder; implement full check if needed
            }

            // Submit handler for full payment check
            document.getElementById('submit-purchase').addEventListener('click', function(e) {
                if (!isFormValid()) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error!',
                        text: 'Please fill all required fields correctly.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const grandTotal = parseFloat(document.getElementById('grand_total').value) || 0;
                const totalPaid = calculateTotalPaid();
                const balanceDue = grandTotal - totalPaid;
                const hasCredit = hasCreditPayment();

                if (balanceDue > 0 && !hasCredit) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Payment Not Fully Paid!',
                        text: 'Please add full payment or select Credit as a payment method to proceed with remaining balance.',
                        confirmButtonText: 'OK'
                    });
                    return;
                }
            });

            // Product Search Functionality
            const searchInput = document.getElementById('product-search');
            const searchResults = document.getElementById('product-search-results');

            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                searchResults.innerHTML = '';
                searchResults.style.display = 'none';

                if (query.length < 2) return;

                const matches = allProducts.filter(product => product.name.toLowerCase().includes(query));
                if (matches.length === 0) return;

                matches.forEach(product => {
                    const div = document.createElement('div');
                    div.className =
                        'p-2 border-bottom cursor-pointer hover-bg-light d-flex align-items-center';
                    div.innerHTML = `
                        <div class="flex-grow-1">
                            <div class="fw-semibold">${product.name}</div>
                        </div>
                    `;
                    div.addEventListener('click', () => addProductFromSearch(product));
                    searchResults.appendChild(div);
                });

                searchResults.style.display = 'block';
            });

            // Handle Enter key to add first matching product
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && this.value.length >= 2) {
                    e.preventDefault();
                    const query = this.value.toLowerCase().trim();
                    const matches = allProducts.filter(product => product.name.toLowerCase().includes(
                        query));
                    if (matches.length > 0) {
                        addProductFromSearch(matches[0]);
                    }
                }
            });

            // Hide results on outside click
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });

            function addProductFromSearch(product) {
                if (document.querySelectorAll('#items-table tbody tr').length >= 50) {
                    alert('Maximum 50 items allowed per purchase.');
                    return;
                }
                // Check if product already exists in a row; if yes, duplicate the row with same product
                const existingRows = document.querySelectorAll('#items-table tbody tr .item-select');
                let duplicateRow = null;
                existingRows.forEach(select => {
                    if (select.value == product.id) {
                        duplicateRow = select.closest('tr').cloneNode(true);
                        // Update index for duplicate
                        const newIndex = itemIndex;
                        duplicateRow.querySelectorAll('input, select').forEach(el => {
                            const name = el.name.replace(/\[(\d+)\]/, `[${newIndex}]`);
                            el.name = name;
                        });
                        itemIndex++;
                        document.querySelector('#items-table tbody').appendChild(duplicateRow);
                        recalcAllItems();
                        return;
                    }
                });
                if (!duplicateRow) {
                    // Add new row if no duplicate found
                    addEmptyItemRow(product.id);
                }
                searchInput.value = '';
                searchResults.style.display = 'none';
            }

            // Recalculate a single row total
            function recalcItemRow(row) {
                const qtyInput = row.querySelector('.qty-input');
                let qty = parseFloat(qtyInput.value) || 0;
                if (qty < 0.01) {
                    qtyInput.value = 0.01;
                    qty = 0.01;
                }
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const discount = parseFloat(row.querySelector('.discount-input')?.value) || 0;
                const type = row.querySelector('.discount-type-input')?.value || 'amount';
                let lineSubtotal = qty * cost;
                let discAmount = 0;
                if (type === 'percentage') {
                    discAmount = lineSubtotal * (discount / 100);
                } else {
                    discAmount = discount;
                }
                discAmount = Math.min(discAmount, lineSubtotal);
                const lineTotal = Math.max(0, lineSubtotal - discAmount);
                row.querySelector('.total-input').value = lineTotal.toFixed(2);
                row.querySelector('.disc-amt').textContent = discAmount.toFixed(2);
            }

            // Recalculate all item totals including overall discount
            function recalcAllItems() {
                let subtotal = 0,
                    totalItemDiscount = 0;
                document.querySelectorAll('#items-table tbody tr').forEach(row => {
                    recalcItemRow(row); // Ensure each row is recalculated with min qty check
                    const qty = parseFloat(row.querySelector('.qty-input').value) || 0.01;
                    const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                    const discount = parseFloat(row.querySelector('.discount-input')?.value) || 0;
                    const type = row.querySelector('.discount-type-input')?.value || 'amount';
                    const lineSubtotal = qty * cost;
                    let discAmount = 0;
                    if (type === 'percentage') {
                        discAmount = lineSubtotal * (discount / 100);
                    } else {
                        discAmount = discount;
                    }
                    discAmount = Math.min(discAmount, lineSubtotal);
                    subtotal += lineSubtotal;
                    totalItemDiscount += discAmount;
                });

                // Calculate overall discount
                const overallValue = parseFloat(document.getElementById('overall_discount_value').value) || 0;
                const overallType = document.getElementById('overall_discount_type').value || 'amount';
                let overallDiscountAmount = 0;
                if (overallType === 'percentage') {
                    overallDiscountAmount = subtotal * (overallValue / 100);
                } else {
                    overallDiscountAmount = overallValue;
                }
                overallDiscountAmount = Math.min(overallDiscountAmount, subtotal);
                document.getElementById('overall_discount_amount').value = overallDiscountAmount.toFixed(2);

                // Final grand total
                const finalGrand = Math.max(0, subtotal - totalItemDiscount - overallDiscountAmount);
                document.getElementById('subtotal').value = subtotal.toFixed(2);
                document.getElementById('total_discount').value = Math.max(0, totalItemDiscount).toFixed(2);
                document.getElementById('grand_total').value = finalGrand.toFixed(2);
                document.getElementById('total_price').value = finalGrand.toFixed(2);

                recalcTotalPaid();
                updateSubmitButton();
            }

            // Add empty item row
            function addEmptyItemRow(productId = null) {
                if (document.querySelectorAll('#items-table tbody tr').length >= 50) {
                    alert('Maximum 50 items allowed per purchase.');
                    return;
                }
                const tbody = document.querySelector('#items-table tbody');
                const tr = document.createElement('tr');
                tr.classList.add('align-middle');
                const optionsHtml = '<option value="">-- Select Product --</option>' + productsOptions;
                tr.innerHTML = `
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-select item-select" required>
                    ${optionsHtml}
                </select>
            </td>
            <td><input type="number" name="items[${itemIndex}][qty]" class="form-control qty-input text-center" value="" min="0.01" max="999" step="0.01" placeholder="0.01" title="Enter quantity"></td>
            <td class="text-end"><input type="number" name="items[${itemIndex}][cost_price]" class="form-control cost-input text-end" value="" step="0.01" placeholder="0.00" title="Enter cost price"></td>
<td>
    <div class="input-group" style="gap: 2px;">
        <!-- Discount input -->
        <input type="number" 
               name="items[${itemIndex}][discount]" 
               class="form-control discount-input" 
               value="" 
               step="0.01" 
               min="0" 
               placeholder="0.00" 
               title="Discount"
               style="width: 80px; height: 28px;">

        <!-- Discount type select -->
        <select name="items[${itemIndex}][discount_type]" 
                class="form-select discount-type-input rounded-start-0" 
                style="width: 40px; height: 28px;">
            <option value="percentage">%</option>
            <option value="amount">Amt</option>
        </select>
    </div>
</td>

            <td class="text-end"><span class="disc-amt fw-semibold text-muted">0.00</span></td>
            <td class="text-end"><input type="number" name="items[${itemIndex}][total]" class="form-control total-input text-end fw-semibold" value="" step="0.01" readonly placeholder="0.00" title="Line total"></td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm p-1 remove-item" title="Remove line">
                    <i class="bi bi-trash"></i>
                </button>
            </td>`;
                tbody.appendChild(tr);
                itemIndex++;
                if (productId) {
                    const newSelect = tr.querySelector('.item-select');
                    newSelect.value = productId;
                }
                recalcAllItems();
                tr.querySelector('.qty-input').focus();
            }

            // Add Item Row (manual)
            document.getElementById('add-item').addEventListener('click', () => {
                addEmptyItemRow();
            });

            // Remove Item or Payment
            document.addEventListener('click', e => {
                if (e.target.closest('.remove-item')) {
                    const row = e.target.closest('tr');
                    const rows = document.querySelectorAll('#items-table tbody tr');
                    if (rows.length > 1 || confirm('This will remove the only item. Continue?')) {
                        row.remove();
                        recalcAllItems();
                    }
                    e.preventDefault();
                }
                if (e.target.closest('.remove-payment')) {
                    const row = e.target.closest('.payment-row');
                    const rows = document.querySelectorAll('.payment-row');
                    if (rows.length > 1 || confirm('This will remove the only payment. Continue?')) {
                        row.remove();
                        recalcTotalPaid();
                    }
                    e.preventDefault();
                }
            });

            // Event Listeners for Dynamic Changes
            document.addEventListener('change', e => {
                if (e.target.classList.contains('item-select')) {
                    recalcAllItems();
                }
                if (e.target.classList.contains('payment-method-select')) {
                    updatePaymentFields(e.target.closest('.payment-row'));
                }
                if (e.target.classList.contains('discount-type-input')) {
                    const row = e.target.closest('tr');
                    recalcItemRow(row);
                    recalcAllItems();
                }
                if (e.target.id === 'overall_discount_type') {
                    recalcAllItems();
                }
            });

            document.addEventListener('input', e => {
                const row = e.target.closest('tr');
                if (row && (e.target.classList.contains('qty-input') || e.target.classList.contains(
                        'cost-input'))) {
                    recalcItemRow(row);
                    recalcAllItems();
                }
                if (e.target.classList.contains('discount-input')) {
                    if (parseFloat(e.target.value) < 0) e.target.value = 0;
                    const row = e.target.closest('tr');
                    if (row) {
                        recalcItemRow(row);
                        recalcAllItems();
                    }
                }
                if (e.target.id === 'overall_discount_value') {
                    if (parseFloat(e.target.value) < 0) e.target.value = 0;
                    recalcAllItems();
                }
                if (e.target.classList.contains('payment-paid-input')) {
                    if (parseFloat(e.target.value) < 0) e.target.value = 0;
                    recalcTotalPaid();
                }
            });

            // Initialize payment fields
            document.querySelectorAll('.payment-row').forEach(row => {
                updatePaymentFields(row);
            });

            // Add Payment Row
            document.getElementById('add-payment').addEventListener('click', () => {
                if (document.querySelectorAll('.payment-row').length >= 10) {
                    alert('Maximum 10 payments allowed.');
                    return;
                }
                const wrapper = document.getElementById('payments-wrapper');
                const div = document.createElement('div');
                div.classList.add('row', 'g-2', 'mb-3', 'payment-row', 'align-items-end');
                div.innerHTML = `
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold">Payment Method <span class="text-danger">*</span></label>
                <select name="payments[${paymentIndex}][payment_method]" class="form-select payment-method-select" required>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="credit">Credit</option>
                    <option value="fund_transfer">Fund Transfer</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
                <input type="number" name="payments[${paymentIndex}][payment_paid]" class="form-control payment-paid-input text-end" placeholder="0.00" step="0.01" min="0" required title="Amount paid">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold">Payment Date <span class="text-danger">*</span></label>
                <input type="date" name="payments[${paymentIndex}][paid_date]" class="form-control" value="{{ date('Y-m-d') }}" required title="Payment date">
            </div>
            <div class="col-12 col-md-4 cheque-fields" style="display:none;">
                <label class="form-label small fw-semibold">Cheque Details</label>
                <select name="payments[${paymentIndex}][bank_id]" class="form-select" title="Select bank">
                    <option value="">-- Select Bank --</option>
                    @foreach ($banks as $bank)
                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                    @endforeach
                </select>
                <input type="text" name="payments[${paymentIndex}][cheque_no]" class="form-control mt-1" placeholder="Cheque No" title="Cheque number">
                <input type="date" name="payments[${paymentIndex}][cheque_date]" class="form-control mt-1" value="{{ date('Y-m-d') }}" title="Cheque date">
            </div>
            <div class="col-12 col-md-4 fund-transfer-fields" style="display:none;">
                <label class="form-label small fw-semibold">Transfer Details <span class="text-danger">*</span></label>
                <select name="payments[${paymentIndex}][bank_account_id]" class="form-select" title="Select bank account">
                    <option value="">-- Select Bank Account --</option>
                    @foreach ($bankAccounts as $bankAccount)
                    <option value="{{ $bankAccount->id }}">{{ $bankAccount->bank_name }} ({{ $bankAccount->account_number }})</option>
                    @endforeach
                </select>
                <input type="text" name="payments[${paymentIndex}][transfer_ref]" class="form-control mt-1" placeholder="Transfer Ref" title="Transfer reference">
            </div>
            <div class="col-12 col-md-4 credit-fields" style="display:none;">
                <label class="form-label small fw-semibold">Due Date <span class="text-danger">*</span></label>
                <input type="date" name="payments[${paymentIndex}][due_date]" class="form-control" value="{{ date('Y-m-d') }}" title="Due date for credit">
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger btn-sm w-100 p-0 remove-payment" title="Remove payment" style="height: 38px;">
                    <i class="bi bi-trash"></i>
                </button>
            </div>`;
                wrapper.appendChild(div);
                updatePaymentFields(div);
                paymentIndex++;
                div.querySelector('.payment-paid-input').focus();
                updateSubmitButton();
            });

            // New Supplier Modal Functionality
            const newSupplierModalEl = document.getElementById('newSupplierModal');
            if (newSupplierModalEl) {
                const newSupplierModal = new bootstrap.Modal(newSupplierModalEl);
                const newSupplierForm = document.getElementById('newSupplierForm');
                document.getElementById('add-new-supplier').addEventListener('click', () => {
                    if (newSupplierForm) {
                        newSupplierForm.reset();
                    }
                    const errorsDiv = document.getElementById('supplier-errors');
                    if (errorsDiv) {
                        errorsDiv.style.display = 'none';
                    }
                    newSupplierModal.show();
                });

                if (newSupplierForm) {
                    newSupplierForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content');
                        if (csrfToken) {
                            formData.append('_token', csrfToken);
                        }
                        const submitBtn = this.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
                        }

                        fetch('{{ route('admin.api-suppliers.store') }}', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => {
                                        throw err;
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // Add new supplier to dropdown
                                    const select = document.getElementById('supplier-select');
                                    if (select) {
                                        const newOption = new Option(data.data.name + (data.data.phone ?
                                                ' (' + data.data.phone + ')' : ''), data.data.id,
                                            true,
                                            true);
                                        select.appendChild(newOption);
                                    }
                                    newSupplierModal.hide();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Supplier created successfully!',
                                        confirmButtonText: 'OK'
                                    });
                                    updateSubmitButton();
                                } else {
                                    // Handle validation errors
                                    const errorsDiv = document.getElementById('supplier-errors');
                                    if (errorsDiv) {
                                        errorsDiv.innerHTML =
                                            '<strong>Please fix the following errors:</strong><ul class="mb-0 mt-1">' +
                                            Object.values(data.errors || {}).flat().map(err =>
                                                `<li>${err}</li>`).join('') +
                                            '</ul>';
                                        errorsDiv.style.display = 'block';
                                    }
                                    // Highlight fields
                                    Object.keys(data.errors || {}).forEach(field => {
                                        const input = document.getElementById(
                                            `supplier_${field.replace('_', '-')}`);
                                        if (input) {
                                            input.classList.add('is-invalid');
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                if (error.message && typeof error.message === 'object' && error.message
                                    .message) {
                                    alert(error.message.message);
                                } else {
                                    alert(
                                        'An error occurred while creating the supplier. Please check the console for details.'
                                        );
                                }
                            })
                            .finally(() => {
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Create Supplier';
                                }
                            });
                    });
                }

                // Clear invalid classes on input
                ['supplier_name', 'supplier_phone', 'supplier_email'].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) {
                        input.addEventListener('input', function() {
                            this.classList.remove('is-invalid');
                        });
                    }
                });
            }

            // New Product Modal Functionality
            const newProductModalEl = document.getElementById('newProductModal');
            if (newProductModalEl) {
                const newProductModal = new bootstrap.Modal(newProductModalEl);
                const newProductForm = document.getElementById('newProductForm');
                document.getElementById('add-new-product').addEventListener('click', () => {
                    if (newProductForm) {
                        newProductForm.reset();
                        document.getElementById('product_status').value = 'active';
                    }
                    const errorsDiv = document.getElementById('product-errors');
                    if (errorsDiv) {
                        errorsDiv.style.display = 'none';
                    }
                    newProductModal.show();
                });

                if (newProductForm) {
                    newProductForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        const formData = new FormData(this);
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute(
                            'content');
                        if (csrfToken) {
                            formData.append('_token', csrfToken);
                        }
                        const submitBtn = this.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Creating...';
                        }

                        fetch('/api/products', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            })
                            .then(response => {
                                if (!response.ok) {
                                    return response.json().then(err => {
                                        throw err;
                                    });
                                }
                                return response.json();
                            })
                            .then(data => {
                                if (data.success) {
                                    // Add new product to options and allProducts
                                    const newOption =
                                        `<option value="${data.data.id}">${data.data.name}</option>`;
                                    productsOptions += newOption;
                                    allProducts.push({
                                        id: data.data.id,
                                        name: data.data.name
                                    });
                                    // Update all existing selects
                                    document.querySelectorAll('.item-select').forEach(select => {
                                        const currentValue = select.value;
                                        select.innerHTML =
                                            '<option value="">-- Select Product --</option>' +
                                            productsOptions;
                                        if (currentValue) {
                                            select.value = currentValue;
                                        }
                                    });
                                    newProductModal.hide();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: 'Product created successfully!',
                                        confirmButtonText: 'OK'
                                    });
                                } else {
                                    // Handle validation errors
                                    const errorsDiv = document.getElementById('product-errors');
                                    if (errorsDiv) {
                                        errorsDiv.innerHTML =
                                            '<strong>Please fix the following errors:</strong><ul class="mb-0 mt-1">' +
                                            Object.values(data.errors || {}).flat().map(err =>
                                                `<li>${err}</li>`).join('') +
                                            '</ul>';
                                        errorsDiv.style.display = 'block';
                                    }
                                    // Highlight fields
                                    Object.keys(data.errors || {}).forEach(field => {
                                        const input = document.getElementById(
                                            `product_${field.replace('_', '-')}`);
                                        if (input) {
                                            input.classList.add('is-invalid');
                                        }
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                if (error.message && typeof error.message === 'object' && error.message
                                    .message) {
                                    alert(error.message.message);
                                } else {
                                    alert(
                                        'An error occurred while creating the product. Please check the console for details.'
                                        );
                                }
                            })
                            .finally(() => {
                                if (submitBtn) {
                                    submitBtn.disabled = false;
                                    submitBtn.innerHTML = 'Create Product';
                                }
                            });
                    });
                }

                // Clear invalid classes on input
                ['product_name', 'product_code', 'product_category_id', 'product_supplier_id', 'product_brand_id',
                    'product_status', 'product_description', 'product_regular_price', 'product_wholesale_price',
                    'product_sale_price', 'product_warranty', 'product_weight', 'product_model', 'product_image'
                ].forEach(id => {
                    const input = document.getElementById(id);
                    if (input) {
                        input.addEventListener('input', function() {
                            this.classList.remove('is-invalid');
                        });
                    }
                });
            }

            // Initial Calculation
            recalcAllItems();
            updateSubmitButton();
        });
    </script>
@endsection