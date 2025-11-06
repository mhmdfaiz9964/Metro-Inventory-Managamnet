@php
    // Prepare payments array
    $payments = old(
        'payments',
        isset($sale) && $sale->salePayments
            ? $sale->salePayments
                ->map(function ($payment) {
                    return [
                        'payment_method' => $payment->payment_method,
                        'payment_paid' => $payment->payment_paid,
                        'paid_date' => $payment->paid_date,
                        'bank_id' => $payment->bank_id,
                        'cheque_no' => $payment->cheque_no,
                        'cheque_date' => $payment->cheque_date,
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
                'cheque_no' => '',
                'cheque_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d'),
            ],
        ];
    }

    // Prepare products array
    $oldProducts = old(
        'products',
        isset($sale) && $sale->items
            ? $sale->items
                ->map(function ($item) {
                    return [
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'sale_price' => $item->sale_price,
                        'discount' => $item->discount ?? 0,
                        'discount_type' => $item->discount_type ?? 'percentage',
                        'total' => $item->total ?? 0,
                    ];
                })
                ->toArray()
            : [],
    );

    // Prepare overall discount
    $overallDiscount = old('overall_discount', isset($sale) ? $sale->overall_discount ?? 0 : 0);
    $overallDiscountType = old(
        'overall_discount_type',
        isset($sale) ? $sale->overall_discount_type ?? 'percentage' : 'percentage',
    );

    // Prepare product options HTML for JS
    $productOptions = '';
    foreach ($products as $product) {
        $imageUrl = $product->image ? asset('storage/' . $product->image) : '';
        $productOptions .=
            '<option value="' .
            $product->id .
            '" data-price="' .
            ($product->sale_price ?? $product->regular_price) .
            '" data-available="' .
            ($product->stock->available_stock ?? 0) .
            '" data-image="' .
            $imageUrl .
            '">' .
            $product->name .
            '</option>';
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

@if (isset($sale) && isset($sale->id))
    <form action="{{ route('admin.sales.update', $sale->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
    @else
        <form method="POST" action="{{ route('admin.sales.store') }}" enctype="multipart/form-data">
            @csrf
@endif

<div class="container-fluid py-4">
    <div class="row g-3">
        {{-- Salesperson Selection --}}
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Salesperson</label>
            <select name="salesperson_id" id="salesperson-select"
                class="form-select {{ $errors->has('salesperson_id') ? 'is-invalid' : '' }}">
                <option value="">-- Select a Salesperson --</option>
                @foreach ($salespersons as $salesperson)
                    <option value="{{ $salesperson->id }}"
                        {{ old('salesperson_id', isset($sale) ? $sale->salesperson_id : '') == $salesperson->id ? 'selected' : '' }}>
                        {{ $salesperson->name }} ({{ $salesperson->email ?? '' }})
                    </option>
                @endforeach
            </select>
            <div class="form-text">Select the sales representative for this sale.</div>
        </div>

        {{-- Customer Selection --}}
        <div class="col-12 col-md-4">
            <label class="form-label fw-semibold">Customer <span class="text-danger">*</span></label>
            <div class="input-group">
                <select name="customer_id" id="customer-select"
                    class="form-select {{ $errors->has('customer_id') ? 'is-invalid' : '' }}" required>
                    <option value="">-- Select a Customer --</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" data-balance-due="{{ $customer->balance_due ?? 0 }}"
                            data-credit-limit="{{ $customer->credit_limit ?? 0 }}"
                            {{ old('customer_id', isset($sale) ? $sale->customer_id : '') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->mobile_number ?? '' }})
                        </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-success" id="add-new-customer" title="Add New Customer">
                    <i class="bi bi-plus-circle"></i>
                </button>
            </div>
            <div class="form-text">Select an existing customer or add a new one.</div>
        </div>

        {{-- Sale Date --}}
        <div class="col-12 col-md-4">
            <label for="sale_date" class="form-label fw-semibold">Sale Date <span class="text-danger">*</span></label>
            <input type="date" name="sale_date" id="sale_date"
                value="{{ old('sale_date', isset($sale) ? $sale->sale_date : date('Y-m-d')) }}"
                class="form-control {{ $errors->has('sale_date') ? 'is-invalid' : '' }}" required>
            <div class="form-text">Date of the sale transaction.</div>
        </div>

        {{-- Products Section --}}
        <div class="col-12 {{ $errors->has('products') ? 'is-invalid' : '' }}">
            <label class="form-label fw-semibold mb-2">Products <span class="text-danger">*</span></label>

            {{-- Product Search Bar --}}
            <div class="mb-3 position-relative">
                <label for="product-search" class="form-label">Quick Search & Add Product</label>
                <input type="text" id="product-search" class="form-control" placeholder="Search products by name...">
                <div id="product-search-results"
                    class="position-absolute z-3 w-100 bg-white border rounded shadow top-100 start-0"
                    style="display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-2">
                <button type="button" id="add-product" class="btn btn-success btn-sm shadow-sm"
                    title="Add a new product to the sale">
                    <i class="bi bi-plus-circle"></i> Add Product
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="products-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 30%;">Products</th>
                                <th scope="col" style="width: 10%;">Qty</th>
                                <th scope="col" style="width: 15%;">Unit Cost</th>
                                <th scope="col" style="width: 20%;">Discount</th>
                                <th scope="col" style="width: 15%;">Disc Amt</th>
                                <th scope="col" style="width: 10%;">Line Total</th>
                                <th scope="col" style="width: 10%;">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($oldProducts) > 0)
                                @foreach ($oldProducts as $index => $p)
                                    <tr class="align-middle">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img class="product-image rounded me-2" src=""
                                                    style="width: 30px; height: 30px; object-fit: cover; display: none;"
                                                    alt="">
                                                <select name="products[{{ $index }}][product_id]"
                                                    class="form-select product-select flex-grow-1 {{ $errors->has("products.$index.product_id") ? 'is-invalid' : '' }}"
                                                    required>
                                                    <option value="">-- Select Product --</option>
                                                    @foreach ($products as $product)
                                                        @php $imageUrl = $product->image ? asset('storage/' . $product->image) : ''; @endphp
                                                        <option value="{{ $product->id }}"
                                                            data-price="{{ $product->sale_price ?? $product->regular_price }}"
                                                            data-available="{{ $product->stock->available_stock ?? 0 }}"
                                                            data-image="{{ $imageUrl }}"
                                                            {{ $p['product_id'] == $product->id ? 'selected' : '' }}>
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="number" name="products[{{ $index }}][quantity]"
                                                class="form-control quantity-input text-center {{ $errors->has("products.$index.quantity") ? 'is-invalid' : '' }}"
                                                value="{{ $p['quantity'] }}" min="1" max="999"
                                                placeholder="1" title="Enter quantity">
                                        </td>
                                        <td class="text-end">
                                            <input type="number" name="products[{{ $index }}][sale_price]"
                                                class="form-control price-input text-end {{ $errors->has("products.$index.sale_price") ? 'is-invalid' : '' }}"
                                                value="{{ $p['sale_price'] }}" step="0.01" readonly
                                                placeholder="0.00" title="Price auto-fills on product selection">
                                        </td>
                                        <td>
                                            <div class="input-group" style="gap: 2px;">
                                                <!-- Discount input -->
                                                <input type="number" name="products[{{ $index }}][discount]"
                                                    class="form-control discount-input {{ $errors->has("products.$index.discount") ? 'is-invalid' : '' }}"
                                                    value="{{ $p['discount'] }}" step="0.01" min="0"
                                                    placeholder="0.00" title="Enter discount amount or percentage"
                                                    style="width: 80px; height: 28px;">

                                                <!-- Discount type select -->
                                                <select name="products[{{ $index }}][discount_type]"
                                                    class="form-select discount-type-input rounded-start-0 {{ $errors->has("products.$index.discount_type") ? 'is-invalid' : '' }}"
                                                    style="width: 40px; height: 28px;">
                                                    <option value="percentage"
                                                        {{ ($p['discount_type'] ?? 'percentage') == 'percentage' ? 'selected' : '' }}>
                                                        %</option>
                                                    <option value="amount"
                                                        {{ ($p['discount_type'] ?? 'percentage') == 'amount' ? 'selected' : '' }}>
                                                        Amt</option>
                                                </select>
                                            </div>
                                        </td>

                                        <td class="text-end">
                                            <span
                                                class="disc-amt fw-semibold text-muted">{{ $p['discount'] ?? '0.00' }}</span>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" name="products[{{ $index }}][total]"
                                                class="form-control total-input text-end fw-semibold"
                                                value="{{ $p['total'] }}" step="0.01" readonly
                                                placeholder="0.00" title="Auto-calculated total">
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm p-1 remove-product"
                                                title="Remove this product line">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="align-middle">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img class="product-image rounded me-2" src=""
                                                style="width: 30px; height: 30px; object-fit: cover; display: none;"
                                                alt="">
                                            <select name="products[0][product_id]"
                                                class="form-select product-select flex-grow-1 {{ $errors->has('products.0.product_id') ? 'is-invalid' : '' }}"
                                                required>
                                                <option value="">-- Select Product --</option>
                                                @foreach ($products as $product)
                                                    @php $imageUrl = $product->image ? asset('storage/' . $product->image) : ''; @endphp
                                                    <option value="{{ $product->id }}"
                                                        data-price="{{ $product->sale_price ?? $product->regular_price }}"
                                                        data-available="{{ $product->stock->available_stock ?? 0 }}"
                                                        data-image="{{ $imageUrl }}">
                                                        {{ $product->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="number" name="products[0][quantity]"
                                            class="form-control quantity-input text-center {{ $errors->has('products.0.quantity') ? 'is-invalid' : '' }}"
                                            value="" min="1" max="999" placeholder="1"
                                            title="Enter quantity">
                                    </td>
                                    <td class="text-end">
                                        <input type="number" name="products[0][sale_price]"
                                            class="form-control price-input text-end {{ $errors->has('products.0.sale_price') ? 'is-invalid' : '' }}"
                                            value="" step="0.01" readonly placeholder="0.00"
                                            title="Price auto-fills on product selection">
                                    </td>
                                    <td>
                                        <div class="input-group" style="gap: 2px;">
                                            <!-- Discount input -->
                                            <input type="number" name="products[0][discount]"
                                                class="form-control form-control-sm discount-input {{ $errors->has('products.0.discount') ? 'is-invalid' : '' }}"
                                                value="" step="0.01" min="0" placeholder="0.00"
                                                title="Enter discount amount or percentage" style="width: 80px;">

                                            <!-- Discount type select -->
                                            <select name="products[0][discount_type]"
                                                class="form-select discount-type-input rounded-start-0 {{ $errors->has('products.0.discount_type') ? 'is-invalid' : '' }}"
                                                style="width: 40px;">
                                                <option value="percentage">%</option>
                                                <option value="amount">Amt</option>
                                            </select>
                                        </div>
                                    </td>

                                    <td class="text-end">
                                        <span class="disc-amt fw-semibold text-muted">0.00</span>
                                    </td>
                                    <td class="text-end">
                                        <input type="number" name="products[0][total]"
                                            class="form-control total-input text-end fw-semibold" value=""
                                            step="0.01" readonly placeholder="0.00" title="Auto-calculated total">
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-outline-danger btn-sm p-1 remove-product"
                                            title="Remove this product line">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <th colspan="4" class="text-end">Subtotal:</th>
                                <th class="text-end" colspan="1"><input type="number" id="subtotal"
                                        class="form-control fw-bold bg-transparent border-0 text-end" value=""
                                        step="0.01" readonly placeholder="0.00"></th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr class="fw-semibold">
                                <th colspan="4" class="text-end text-danger">Product Discounts:</th>
                                <th class="text-end"><input type="number" id="total_discount"
                                        class="form-control fw-bold text-danger bg-transparent border-0 text-end"
                                        value="" step="0.01" readonly placeholder="0.00"></th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr class="fw-semibold text-dark" style="background-color: #d7dbdf !important;">
                                <th colspan="3" class="text-end pe-0">
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="overall_discount" id="overall_discount_value"
                                            class="form-control fw-bold text-end {{ $errors->has('overall_discount') ? 'is-invalid' : '' }}"
                                            value="{{ $overallDiscount }}" step="0.01" min="0"
                                            placeholder="0.00" title="Overall discount value">
                                        <select name="overall_discount_type" id="overall_discount_type"
                                            class="form-select form-select-sm {{ $errors->has('overall_discount_type') ? 'is-invalid' : '' }}">
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
                                <th colspan="5" class="text-end">Final Grand Total:</th>
                                <th class="text-end"><input type="number" id="grand_total"
                                        class="form-control fw-bold text-white bg-transparent border-0 text-end fs-5"
                                        value="" step="0.01" readonly placeholder="0.00"></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="form-text small text-muted mt-1">Apply overall discount on the subtotal after product
                discounts. Changes auto-update the final total.</div>
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
                                class="form-select form-select-sm payment-method-select {{ $errors->has("payments.$i.payment_method") ? 'is-invalid' : '' }}"
                                required>
                                <option value="cash" {{ $pay['payment_method'] == 'cash' ? 'selected' : '' }}>Cash
                                </option>
                                <option value="cheque" {{ $pay['payment_method'] == 'cheque' ? 'selected' : '' }}>Cheque
                                </option>
                                <option value="loan" {{ $pay['payment_method'] == 'loan' ? 'selected' : '' }}>Credit
                                </option>
                                <option value="fund_transfer"
                                    {{ $pay['payment_method'] == 'fund_transfer' ? 'selected' : '' }}>Fund Transfer
                                </option>
                            </select>
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold">Amount <span
                                    class="text-danger">*</span></label>
                            <input type="number" name="payments[{{ $i }}][payment_paid]"
                                value="{{ $pay['payment_paid'] ?? '' }}"
                                class="form-control form-control payment-paid-input text-end {{ $errors->has("payments.$i.payment_paid") ? 'is-invalid' : '' }}"
                                placeholder="0.00" step="0.01" min="0" required
                                title="Enter payment amount">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold">Payment Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="payments[{{ $i }}][paid_date]"
                                value="{{ $pay['paid_date'] ?? date('Y-m-d') }}"
                                class="form-control {{ $errors->has("payments.$i.paid_date") ? 'is-invalid' : '' }}"
                                required title="Payment date">
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
                            <select name="payments[{{ $i }}][bank_id]"
                                class="form-select {{ $errors->has("payments.$i.bank_id") ? 'is-invalid' : '' }}"
                                title="Select bank account">
                                <option value="">-- Select Bank Account --</option>
                                @foreach ($bankAccounts as $bankAccount)
                                    <option value="{{ $bankAccount->id }}"
                                        {{ ($pay['bank_id'] ?? '') == $bankAccount->id ? 'selected' : '' }}>
                                        {{ $bankAccount->bank_name }} ({{ $bankAccount->account_number }})</option>
                                @endforeach
                            </select>
                            <input type="text" name="payments[{{ $i }}][cheque_no]"
                                value="{{ $pay['cheque_no'] ?? '' }}" class="form-control mt-1"
                                placeholder="Transfer Ref" title="Enter transfer reference">
                        </div>
                        <div class="col-12 col-md-4 loan-fields"
                            style="display: {{ $pay['payment_method'] == 'loan' ? 'block' : 'none' }};">
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
        </div>
    </div>

    {{-- Submit Button --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="mt-4 d-flex justify-content-between">
                <a href="javascript:void(0)" onclick="window.history.back()"
                    class="btn btn-outline-secondary">Back</a>
                <button type="submit" id="submit-sale" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    {{ isset($sale) ? 'Update Sale' : 'Create Sale' }}
                </button>
            </div>
        </div>
    </div>

    </form>

    {{-- New Customer Modal --}}
    <div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCustomerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newCustomerForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="customer_name" class="form-label">Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customer_name" name="name" required
                                maxlength="255">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="customer_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="customer_email" name="email">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="customer_mobile" class="form-label">Mobile Number</label>
                            <input type="text" class="form-control" id="customer_mobile" name="mobile_number"
                                maxlength="20">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div id="customer-errors" class="alert alert-danger" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                let productIndex = {{ count($oldProducts) }};
                let paymentIndex = {{ count($payments) }};

                // All products data for search
                const allProducts = {!! json_encode(
                    $products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => $product->sale_price ?? $product->regular_price,
                            'stock' => $product->stock->available_stock ?? 0,
                            'image' => $product->image ? asset('storage/' . $product->image) : '',
                        ];
                    }),
                ) !!};

                // Product options HTML
                let productOptions = `{!! addslashes($productOptions) !!}`;

                let currentCustomerBalance = 0;
                let currentCreditLimit = 0;

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
                        if (e.target.classList.contains('quantity-input')) {
                            e.target.value = '1';
                        } else {
                            e.target.value = '0.00';
                        }
                    } else if (e.target.type === 'number' && !e.target.readOnly) {
                        const num = parseFloat(e.target.value);
                        if (!isNaN(num)) {
                            let formatted;
                            if (e.target.classList.contains('quantity-input')) {
                                formatted = Math.round(num).toString(); // Integer for quantity (no decimals)
                            } else {
                                if (Number.isInteger(num)) {
                                    formatted = num.toString(); // Whole number without decimal (e.g., 10)
                                } else {
                                    formatted = num.toFixed(2); // Two decimals for non-integers (e.g., 10.50)
                                }
                            }
                            // Remove leading zeros
                            formatted = formatted.replace(/^0+/, '');
                            if (formatted === '') formatted = '0';
                            if (formatted === '.') formatted = '0.00';
                            e.target.value = formatted;
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
                    const loanFields = paymentRow.querySelector('.loan-fields');

                    // Handle loan: set amount to 0 and readonly
                    if (method === 'loan') {
                        amountInput.value = 0;
                        amountInput.readOnly = true;
                        amountInput.classList.add('bg-light');
                    } else {
                        amountInput.readOnly = false;
                        amountInput.classList.remove('bg-light');
                    }

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
                        const bankFund = fundFields.querySelector('select[name*="[bank_id]"]');
                        if (bankFund) bankFund.required = method === 'fund_transfer';
                        const transferRef = fundFields.querySelector('input[name*="[cheque_no]"]');
                        if (transferRef) transferRef.required = false; // Optional
                    }

                    // Loan fields
                    if (loanFields) {
                        loanFields.style.display = method === 'loan' ? 'block' : 'none';
                        const dueDate = loanFields.querySelector('input[name*="[due_date]"]');
                        if (dueDate) dueDate.required = method === 'loan';
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
                    // Format display (integer if whole, else 2 decimals)
                    document.getElementById('total-paid').textContent = Number.isInteger(totalPaid) ? totalPaid
                        .toString() : totalPaid.toFixed(2);
                    const grandTotal = parseFloat(document.getElementById('grand_total').value) || 0;
                    const balanceDue = Math.max(0, grandTotal - totalPaid);
                    // Format display (integer if whole, else 2 decimals)
                    document.getElementById('balance-due').textContent = Number.isInteger(balanceDue) ? balanceDue
                        .toString() : balanceDue.toFixed(2);
                    checkCreditLimit();
                }

                // Check credit limit
                function checkCreditLimit() {
                    const grandTotal = parseFloat(document.getElementById('grand_total').value) || 0;
                    const totalPaid = calculateTotalPaid();
                    const unpaid = grandTotal - totalPaid;
                    const projectedDue = currentCustomerBalance + unpaid;
                    if (currentCreditLimit > 0 && projectedDue > currentCreditLimit) {
                        // Optionally show warning, but allow proceed
                        console.warn('Credit limit exceeded');
                    }
                }

                // Check if has credit payment
                function hasCreditPayment() {
                    return Array.from(document.querySelectorAll('.payment-method-select')).some(select => select
                        .value === 'loan');
                }

                // Form validation function
                function isFormValid() {
                    // Check basic required fields
                    if (!document.getElementById('customer-select').value) return false;
                    if (!document.getElementById('sale_date').value) return false;

                    // Check product rows
                    const productRows = document.querySelectorAll('#products-table tbody tr');
                    if (productRows.length === 0) return false;
                    for (let row of productRows) {
                        const productId = row.querySelector('.product-select').value;
                        const qty = parseFloat(row.querySelector('.quantity-input').value) || 0;
                        const total = parseFloat(row.querySelector('.total-input').value) || 0;
                        if (!productId || qty < 1 || total <= 0) return false;
                    }

                    // Check payments
                    const paymentRows = document.querySelectorAll('.payment-row');
                    for (let prow of paymentRows) {
                        const methodSelect = prow.querySelector('.payment-method-select');
                        const method = methodSelect.value;
                        if (!method) return false;

                        const amountInput = prow.querySelector('.payment-paid-input');
                        const amount = parseFloat(amountInput.value);
                        if (isNaN(amount) || amount < 0) return false;

                        const paymentDate = prow.querySelector('input[name*="[paid_date]"]').value;
                        if (!paymentDate) return false;

                        if (method === 'loan' && amount !== 0) return false; // For loan, amount must be 0

                        const chequeFields = prow.querySelector('.cheque-fields');
                        if (method === 'cheque' && chequeFields) {
                            const bank = chequeFields.querySelector('select[name*="[bank_id]"]').value;
                            const chequeNo = chequeFields.querySelector('input[name*="[cheque_no]"]').value;
                            const chequeDate = chequeFields.querySelector('input[name*="[cheque_date]"]').value;
                            if (!bank || !chequeNo || !chequeDate) return false;
                        }

                        const fundFields = prow.querySelector('.fund-transfer-fields');
                        if (method === 'fund_transfer' && fundFields) {
                            const bank = fundFields.querySelector('select[name*="[bank_id]"]').value;
                            if (!bank) return false;
                        }

                        const loanFields = prow.querySelector('.loan-fields');
                        if (method === 'loan' && loanFields) {
                            const dueDate = loanFields.querySelector('input[name*="[due_date]"]').value;
                            if (!dueDate) return false;
                        }
                    }

                    return true;
                }

                // Update submit button state
                // function updateSubmitButton() {
                //     const btn = document.getElementById('submit-sale');
                //     const isValid = isFormValid();
                //     if (!isValid) {
                //         btn.disabled = true;
                //         btn.classList.add('disabled');
                //     } else {
                //         btn.disabled = false;
                //         btn.classList.remove('disabled');
                //     }
                // }

                // Submit handler for full payment check
                document.getElementById('submit-sale').addEventListener('click', function(e) {
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
                        let imageHtml = '';
                        if (product.image) {
                            imageHtml =
                                `<img src="${product.image}" width="50" height="50" class="rounded me-2" alt="${product.name}" style="object-fit: cover;" onerror="this.style.display='none'">`;
                        }
                        div.innerHTML = `
                ${imageHtml}
                <div class="flex-grow-1">
                    <div class="fw-semibold">${product.name}</div>
                    <small class="text-muted">Price: ${product.price} | Stock: ${product.stock}</small>
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
                    if (document.querySelectorAll('#products-table tbody tr').length >= 50) {
                        alert('Maximum 50 products allowed per sale.');
                        return;
                    }
                    // Check if product already exists in a row; if yes, duplicate the row with same product
                    const existingRows = document.querySelectorAll('#products-table tbody tr .product-select');
                    let duplicateRow = null;
                    existingRows.forEach(select => {
                        if (select.value == product.id) {
                            duplicateRow = select.closest('tr').cloneNode(true);
                            // Update index for duplicate
                            const newIndex = productIndex;
                            duplicateRow.querySelectorAll('input, select').forEach(el => {
                                const name = el.name.replace(/\[(\d+)\]/, `[${newIndex}]`);
                                el.name = name;
                            });
                            productIndex++;
                            document.querySelector('#products-table tbody').appendChild(duplicateRow);
                            recalcAll();
                            return;
                        }
                    });
                    if (!duplicateRow) {
                        // Add new row if no duplicate found
                        addEmptyProductRow(product.id, product.price, product.stock);
                    }
                    searchInput.value = '';
                    searchResults.style.display = 'none';
                }

                // Recalculate a single row total
                function recalcRow(row) {
                    const qtyInput = row.querySelector('.quantity-input');
                    const qty = parseFloat(qtyInput.value) || 1;
                    if (qty < 1) {
                        qtyInput.value = '1';
                    }
                    const price = parseFloat(row.querySelector('.price-input').value) || 0;
                    const discount = parseFloat(row.querySelector('.discount-input')?.value) || 0;
                    const type = row.querySelector('.discount-type-input')?.value || 'percentage';
                    let lineSubtotal = qty * price;
                    let discAmount = 0;
                    if (type === 'percentage') {
                        discAmount = lineSubtotal * (discount / 100);
                    } else {
                        discAmount = discount;
                    }
                    discAmount = Math.min(discAmount, lineSubtotal);
                    const lineTotal = Math.max(0, lineSubtotal - discAmount);

                    // Format total (integer if whole, else 2 decimals)
                    row.querySelector('.total-input').value = Number.isInteger(lineTotal) ? lineTotal.toString() :
                        lineTotal.toFixed(2);

                    // Format disc amount (integer if whole, else 2 decimals)
                    row.querySelector('.disc-amt').textContent = Number.isInteger(discAmount) ? discAmount.toString() :
                        discAmount.toFixed(2);
                }

                // Recalculate all totals including overall discount
                function recalcAll() {
                    let subtotal = 0,
                        totalProductDiscount = 0;
                    document.querySelectorAll('#products-table tbody tr').forEach(row => {
                        recalcRow(row);
                        const qty = parseFloat(row.querySelector('.quantity-input').value) || 1;
                        const price = parseFloat(row.querySelector('.price-input').value) || 0;
                        const discount = parseFloat(row.querySelector('.discount-input')?.value) || 0;
                        const type = row.querySelector('.discount-type-input')?.value || 'percentage';
                        const lineSubtotal = qty * price;
                        let discAmount = 0;
                        if (type === 'percentage') {
                            discAmount = lineSubtotal * (discount / 100);
                        } else {
                            discAmount = discount;
                        }
                        discAmount = Math.min(discAmount, lineSubtotal);
                        subtotal += lineSubtotal;
                        totalProductDiscount += discAmount;
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

                    // Final grand total
                    const finalGrand = Math.max(0, subtotal - totalProductDiscount - overallDiscountAmount);

                    // Format and set values (integer if whole, else 2 decimals)
                    document.getElementById('subtotal').value = Number.isInteger(subtotal) ? subtotal.toString() :
                        subtotal.toFixed(2);
                    document.getElementById('total_discount').value = Number.isInteger(totalProductDiscount) ?
                        totalProductDiscount.toString() : totalProductDiscount.toFixed(2);
                    document.getElementById('overall_discount_amount').value = Number.isInteger(overallDiscountAmount) ?
                        overallDiscountAmount.toString() : overallDiscountAmount.toFixed(2);
                    document.getElementById('grand_total').value = Number.isInteger(finalGrand) ? finalGrand
                    .toString() : finalGrand.toFixed(2);

                    recalcTotalPaid();
                    updateSubmitButton();
                }

                // Trigger price update on product select change
                function updateProductPrice(select) {
                    const row = select.closest('tr');
                    const selectedOption = select.selectedOptions[0];
                    if (selectedOption && selectedOption.value) {
                        const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                        // Format price (integer if whole, else 2 decimals)
                        row.querySelector('.price-input').value = Number.isInteger(price) ? price.toString() : price
                            .toFixed(2);
                        const available = parseInt(selectedOption.getAttribute('data-available')) || 0;
                        const qtyInput = row.querySelector('.quantity-input');
                        qtyInput.max = available;
                        const currentQty = parseFloat(qtyInput.value);
                        if (currentQty > available && available >= 0) {
                            qtyInput.value = available;
                            Swal.fire({
                                icon: 'warning',
                                title: 'Stock Alert',
                                text: `Only ${available} available. Quantity adjusted.`
                            });
                            recalcRow(row);
                        }
                        if (available < 5) {
                            qtyInput.title = `Low stock: Only ${available} available`;
                        }

                        // Handle product image
                        const image = selectedOption.getAttribute('data-image');
                        const img = row.querySelector('.product-image');
                        if (image) {
                            if (!img) {
                                const newImg = document.createElement('img');
                                newImg.className = 'product-image rounded me-2';
                                newImg.style.width = '30px';
                                newImg.style.height = '30px';
                                newImg.style.objectFit = 'cover';
                                newImg.alt = selectedOption.text;
                                newImg.onerror = function() {
                                    this.style.display = 'none';
                                };
                                const productContainer = select.parentNode;
                                productContainer.insertBefore(newImg, select);
                            } else {
                                img.src = image;
                                img.alt = selectedOption.text;
                                img.style.display = 'block';
                            }
                        } else if (img) {
                            img.style.display = 'none';
                        }
                    } else {
                        row.querySelector('.price-input').value = '0.00';
                        const img = row.querySelector('.product-image');
                        if (img) img.style.display = 'none';
                    }
                    recalcRow(row);
                    recalcAll();
                }

                // Add empty product row
                function addEmptyProductRow(productId = null, price = 0, stock = 0) {
                    if (document.querySelectorAll('#products-table tbody tr').length >= 50) {
                        alert('Maximum 50 products allowed per sale.');
                        return;
                    }
                    const tbody = document.querySelector('#products-table tbody');
                    const tr = document.createElement('tr');
                    tr.classList.add('align-middle');
                    const optionsHtml = '<option value="">-- Select Product --</option>' + productOptions;
                    tr.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    <img class="product-image rounded me-2" src="" style="width: 30px; height: 30px; object-fit: cover; display: none;" alt="">
                    <select name="products[${productIndex}][product_id]" class="form-select product-select flex-grow-1" required>
                        ${optionsHtml}
                    </select>
                </div>
            </td>
            <td><input type="number" name="products[${productIndex}][quantity]" class="form-control quantity-input text-center" value="" min="1" max="999" placeholder="1" title="Enter quantity"></td>
            <td class="text-end"><input type="number" name="products[${productIndex}][sale_price]" class="form-control price-input text-end" value="${productId ? (Number.isInteger(price) ? price.toString() : price.toFixed(2)) : ''}" step="0.01" readonly placeholder="0.00" title="Price auto-fills"></td>
<td>
    <div class="input-group" style="gap: 2px;">
        <!-- Discount input -->
        <input type="number" 
               name="products[${productIndex}][discount]" 
               class="form-control discount-input" 
               value="" 
               step="0.01" 
               min="0" 
               placeholder="0.00" 
               title="Discount"
               style="width: 80px;">

        <!-- Discount type select -->
        <select name="products[${productIndex}][discount_type]" 
                class="form-select discount-type-input rounded-start-0" 
                style="width: 40px;">
            <option value="percentage">%</option>
            <option value="amount">Amt</option>
        </select>
    </div>
</td>

            <td class="text-end"><span class="disc-amt fw-semibold text-muted">0.00</span></td>
            <td class="text-end"><input type="number" name="products[${productIndex}][total]" class="form-control total-input text-end fw-semibold" value="" step="0.01" readonly placeholder="0.00" title="Line total"></td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm p-1 remove-product" title="Remove line">
                    <i class="bi bi-trash"></i>
                </button>
            </td>`;
                    tbody.appendChild(tr);
                    productIndex++;
                    if (productId) {
                        const newSelect = tr.querySelector('.product-select');
                        newSelect.value = productId;
                        updateProductPrice(newSelect);
                    } else {
                        recalcAll();
                    }
                    tr.querySelector('.quantity-input').focus();
                }

                // Add Product Row (manual)
                document.getElementById('add-product').addEventListener('click', () => {
                    addEmptyProductRow();
                });

                // Remove Product or Payment
                document.addEventListener('click', e => {
                    if (e.target.closest('.remove-product')) {
                        const row = e.target.closest('tr');
                        const rows = document.querySelectorAll('#products-table tbody tr');
                        if (rows.length > 1 || confirm('This will remove the only product. Continue?')) {
                            row.remove();
                            recalcAll();
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
                    if (e.target.classList.contains('product-select')) {
                        updateProductPrice(e.target);
                    }
                    if (e.target.classList.contains('payment-method-select')) {
                        updatePaymentFields(e.target.closest('.payment-row'));
                    }
                    if (e.target.classList.contains('discount-type-input')) {
                        const row = e.target.closest('tr');
                        recalcRow(row);
                        recalcAll();
                    }
                    if (e.target.id === 'overall_discount_type') {
                        recalcAll();
                    }
                    if (e.target.id === 'customer-select') {
                        const selected = e.target.selectedOptions[0];
                        currentCustomerBalance = parseFloat(selected.getAttribute('data-balance-due')) || 0;
                        currentCreditLimit = parseFloat(selected.getAttribute('data-credit-limit')) || 0;
                        checkCreditLimit();
                    }
                });

                document.addEventListener('input', e => {
                    const row = e.target.closest('tr');
                    if (row && (e.target.classList.contains('quantity-input') || e.target.classList.contains(
                            'price-input'))) {
                        recalcRow(row);
                        recalcAll();
                    }
                    if (e.target.classList.contains('discount-input')) {
                        if (parseFloat(e.target.value) < 0) e.target.value = 0;
                        const row = e.target.closest('tr');
                        if (row) {
                            recalcRow(row);
                            recalcAll();
                        }
                    }
                    if (e.target.id === 'overall_discount_value') {
                        if (parseFloat(e.target.value) < 0) e.target.value = 0;
                        recalcAll();
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

                // Load initial customer data
                const customerSelect = document.getElementById('customer-select');
                if (customerSelect && customerSelect.value) {
                    const selected = customerSelect.selectedOptions[0];
                    currentCustomerBalance = parseFloat(selected.getAttribute('data-balance-due')) || 0;
                    currentCreditLimit = parseFloat(selected.getAttribute('data-credit-limit')) || 0;
                }

                // Initialize images for existing products
                document.querySelectorAll('.product-select').forEach(select => {
                    if (select.value) {
                        updateProductPrice(select);
                    }
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
                <select name="payments[${paymentIndex}][payment_method]" class="form-select form-select-sm payment-method-select" required>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="loan">Credit</option>
                    <option value="fund_transfer">Fund Transfer</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
                <input type="number" name="payments[${paymentIndex}][payment_paid]" class="form-control form-control payment-paid-input text-end" placeholder="0.00" step="0.01" min="0" required title="Amount paid">
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
                <select name="payments[${paymentIndex}][bank_id]" class="form-select" title="Select bank account">
                    <option value="">-- Select Bank Account --</option>
                    @foreach ($bankAccounts as $bankAccount)
                    <option value="{{ $bankAccount->id }}">{{ $bankAccount->bank_name }} ({{ $bankAccount->account_number }})</option>
                    @endforeach
                </select>
                <input type="text" name="payments[${paymentIndex}][cheque_no]" class="form-control mt-1" placeholder="Transfer Ref" title="Transfer reference">
            </div>
            <div class="col-12 col-md-4 loan-fields" style="display:none;">
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

                // New Customer Modal Functionality
                const newCustomerModalEl = document.getElementById('newCustomerModal');
                if (newCustomerModalEl) {
                    const newCustomerModal = new bootstrap.Modal(newCustomerModalEl);
                    const newCustomerForm = document.getElementById('newCustomerForm');
                    document.getElementById('add-new-customer').addEventListener('click', () => {
                        if (newCustomerForm) {
                            newCustomerForm.reset();
                        }
                        const errorsDiv = document.getElementById('customer-errors');
                        if (errorsDiv) {
                            errorsDiv.style.display = 'none';
                        }
                        newCustomerModal.show();
                    });

                    if (newCustomerForm) {
                        newCustomerForm.addEventListener('submit', function(e) {
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

                            fetch('{{ route('admin.api-customers.store') }}', {
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
                                        // Add new customer to dropdown
                                        const select = document.getElementById('customer-select');
                                        if (select) {
                                            const newOption = new Option(data.data.name + ' (' + (data.data
                                                    .mobile_number || '') + ')', data.data.id, true,
                                                true);
                                            newOption.setAttribute('data-balance-due', data.data
                                                .balance_due || 0);
                                            newOption.setAttribute('data-credit-limit', data.data
                                                .credit_limit || 0);
                                            select.appendChild(newOption);
                                        }
                                        newCustomerModal.hide();
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: 'Customer created successfully!',
                                            confirmButtonText: 'OK'
                                        });
                                        updateSubmitButton();
                                    } else {
                                        // Handle validation errors
                                        const errorsDiv = document.getElementById('customer-errors');
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
                                                `customer_${field.replace('_', '-')}`);
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
                                            'An error occurred while creating the customer. Please check the console for details.');
                                    }
                                })
                                .finally(() => {
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = 'Create Customer';
                                    }
                                });
                        });
                    }

                    // Clear invalid classes on input
                    ['customer_name', 'customer_email', 'customer_mobile'].forEach(id => {
                        const input = document.getElementById(id);
                        if (input) {
                            input.addEventListener('input', function() {
                                this.classList.remove('is-invalid');
                            });
                        }
                    });
                }

                // Initial Calculation
                recalcAll();
                updateSubmitButton();
            });
        </script>
    @endsection
