@php
    // Prepare payments array
    use App\Models\Cheques;
    use App\Models\FundTransfer;
    use App\Models\PurchasePayment;
    $payments = old(
        'payments',
        isset($purchase) && $purchase->payments
            ? $purchase->payments
                ->map(function ($payment) use ($purchase) {
                    $data = [
                        'payment_method' => $payment->payment_method,
                        'payment_amount' => $payment->payment_amount,
                        'payment_date' => $payment->payment_date,
                        'cheque_no' => null,
                        'cheque_date' => null,
                        'due_date' => $payment->due_date ?? null,
                        'transfer_ref' => null,
                    ];

                    if ($payment->payment_method === 'cheque') {
                        $cheque = Cheques::where('reason', 'Purchase Payment')
                            ->where('cheque_bank', $payment->bank_id)
                            ->where('amount', $payment->payment_amount)
                            ->whereHas('creator', function ($q) use ($purchase) {
                                $q->where('id', $purchase->created_by);
                            })
                            ->first();
                        $data['cheque_no'] = $cheque?->cheque_no ?? null;
                        $data['cheque_date'] = $cheque?->cheque_date ?? null;
                        $data['bank_id'] = $payment->bank_id;
                        $data['bank_account_id'] = null;
                    } elseif ($payment->payment_method === 'fund_transfer') {
                        $fundTransfer = FundTransfer::where('reason', 'Purchase Payment')
                            ->where('from_bank_id', $payment->bank_account_id)
                            ->where('amount', $payment->payment_amount)
                            ->whereHas('creator', function ($q) use ($purchase) {
                                $q->where('id', $purchase->created_by);
                            })
                            ->first();
                        $data['transfer_ref'] = $fundTransfer?->transfer_ref ?? null;
                        $data['bank_account_id'] = $payment->bank_account_id;
                        $data['bank_id'] = null;
                    } else {
                        $data['bank_id'] = $payment->bank_id ?? null;
                        $data['bank_account_id'] = $payment->bank_account_id ?? null;
                    }
                    return $data;
                })
                ->toArray()
            : [],
    );
    if (empty($payments)) {
        $payments = [
            [
                'payment_method' => 'cash',
                'payment_amount' => 0,
                'payment_date' => date('Y-m-d'),
                'bank_id' => null,
                'cheque_no' => '',
                'cheque_date' => date('Y-m-d'),
                'due_date' => date('Y-m-d'),
                'transfer_ref' => '',
            ],
        ];
    }

    // Prepare BOM products array
    $oldBomProducts = old(
        'bom_products',
        isset($purchase) && $purchase->bomProducts
            ? $purchase->bomProducts
                ->map(function ($item) {
                    return [
                        'bom_id' => $item->bom_id,
                        'qty' => max(1, $item->qty ?? 1),
                        'cost_price' => $item->cost_price ?? 0,
                        'discount' => $item->discount ?? 0,
                        'discount_type' => $item->discount_type ?? 'amount',
                        'total' => $item->total ?? 0,
                    ];
                })
                ->toArray()
            : [],
    );

    // Prepare overall discount
    $overallDiscount = old('overall_discount', isset($purchase) ? $purchase->overall_discount ?? 0 : 0);
    $overallDiscountType = old(
        'overall_discount_type',
        isset($purchase) ? $purchase->overall_discount_type ?? 'amount' : 'amount',
    );

    use App\Models\BomComponent;
    use App\Models\Bank;
    // Prepare BOM component options HTML for JS
    $bomsOptions = '';
    $boms = BomComponent::all();
    foreach ($boms as $bom) {
        $bomsOptions .= '<option value="' . $bom->id . '" data-cost="' . $bom->price . '">' . $bom->name . '</option>';
    }

    // Prepare brands for BOM modal
    $brands = \App\Models\Brand::all(); // Assuming Brand model exists

    $banks = Bank::all();

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

@if (isset($purchase))
    <form method="POST" action="{{ route('admin.purchases.update', $purchase) }}" enctype="multipart/form-data">
        @method('PUT')
    @else
        <form method="POST" action="{{ route('admin.purchases.store') }}" enctype="multipart/form-data">
@endif
@csrf

<div class="container-fluid py-4">
    <div class="row g-3">
        {{-- Supplier Selection --}}
        <div class="col-12 col-md-6">
            <label class="form-label fw-semibold">Supplier <span class="text-danger">*</span></label>
            <div class="input-group">
                <select name="supplier_id" id="supplier-select"
                    class="form-select {{ $errors->has('supplier_id') ? 'is-invalid' : '' }}" required>
                    <option value="">-- Select a Supplier --</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}"
                            {{ old('supplier_id', isset($purchase) ? $purchase->supplier_id : '') == $supplier->id ? 'selected' : '' }}>
                            {{ $supplier->name }} ({{ $supplier->mobile_number ?? '' }})
                        </option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-outline-success" id="add-new-supplier" title="Add New Supplier">
                    <i class="bi bi-plus-circle"></i>
                </button>
            </div>
            <div class="form-text">Select an existing supplier or add a new one.</div>
        </div>

        {{-- Purchase Date --}}
        <div class="col-12 col-md-6">
            <label for="purchase_date" class="form-label fw-semibold">Purchase Date <span
                    class="text-danger">*</span></label>
            <input type="date" name="purchase_date" id="purchase_date"
                value="{{ old('purchase_date', isset($purchase) ? $purchase->purchase_date : date('Y-m-d')) }}"
                class="form-control {{ $errors->has('purchase_date') ? 'is-invalid' : '' }}" required>
            <div class="form-text">Date of the purchase transaction.</div>
        </div>

        {{-- BOM Components Section --}}
        <div class="col-12 {{ $errors->has('bom_products') ? 'is-invalid' : '' }}">
            <label class="form-label fw-semibold mb-2">BOM Components <span class="text-danger">*</span></label>

            {{-- BOM Component Search Bar --}}
            <div class="mb-3 position-relative">
                <label for="bom-search" class="form-label">Quick Search & Add BOM Component</label>
                <input type="text" id="bom-search" class="form-control"
                    placeholder="Search BOM components by name...">
                <div id="bom-search-results"
                    class="position-absolute z-3 w-100 bg-white border rounded shadow top-100 start-0"
                    style="display: none; max-height: 200px; overflow-y: auto;"></div>
            </div>

            <div class="d-flex justify-content-end align-items-center mb-2">
                <button type="button" id="add-bom-component" class="btn btn-success btn-sm me-2 shadow-sm"
                    title="Add a new BOM component to the purchase" style="font-weight: bold;">
                    <i class="bi bi-plus-circle"></i> Add Component
                </button>
                <button type="button" id="add-new-bom-component" class="btn btn-primary btn-sm shadow-sm"
                    title="Add New BOM Component" style="font-weight: bold;">
                    <i class="bi bi-plus-circle"></i> New Component
                </button>
            </div>

            <div class="card shadow-sm border-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="bom-components-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 30%;">Component</th>
                                <th scope="col" style="width: 10%;">Qty</th>
                                <th scope="col" style="width: 15%;">Unit Cost</th>
                                <th scope="col" style="width: 20%;">Discount</th>
                                <th scope="col" style="width: 15%;">Disc Amt</th>
                                <th scope="col" style="width: 10%;">Line Total</th>
                                <th scope="col" style="width: 10%;">&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (count($oldBomProducts) > 0)
                                @foreach ($oldBomProducts as $index => $b)
                                    <tr class="align-middle">
                                        <td>
                                            <select name="bom_products[{{ $index }}][bom_id]"
                                                class="form-select bom-select {{ $errors->has("bom_products.$index.bom_id") ? 'is-invalid' : '' }}"
                                                required>
                                                <option value="">-- Select Component --</option>
                                                @foreach ($boms as $bom)
                                                    <option value="{{ $bom->id }}"
                                                        data-cost="{{ $bom->price }}"
                                                        {{ $b['bom_id'] == $bom->id ? 'selected' : '' }}>
                                                        {{ $bom->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="bom_products[{{ $index }}][qty]"
                                                class="form-control qty-input text-center {{ $errors->has("bom_products.$index.qty") ? 'is-invalid' : '' }}"
                                                value="{{ $b['qty'] }}" min="1" max="999"
                                                placeholder="1" title="Enter quantity">
                                        </td>
                                        <td class="text-end">
                                            <input type="number" name="bom_products[{{ $index }}][cost_price]"
                                                class="form-control cost-input text-end {{ $errors->has("bom_products.$index.cost_price") ? 'is-invalid' : '' }}"
                                                value="{{ $b['cost_price'] }}" step="0.01" placeholder="0.00"
                                                title="Cost auto-fills on component selection">
                                        </td>
                                        <td>
                                            <div class="input-group" style="gap: 2px;">
                                                <!-- Smaller input -->
                                                <input type="number" name="bom_products[0][discount]"
                                                    class="form-control form-control-sm discount-input {{ $errors->has('bom_products.0.discount') ? 'is-invalid' : '' }}"
                                                    value="0" step="0.01" min="0" placeholder="0.00"
                                                    title="Enter discount amount or percentage" style="width: 80px;">

                                                <!-- Slightly wider select -->
                                                <select name="bom_products[0][discount_type]"
                                                    class="form-select form-select-sm discount-type-input rounded-start-0 {{ $errors->has('bom_products.0.discount_type') ? 'is-invalid' : '' }}"
                                                    style="width: 40px;">
                                                    <option value="percentage">%</option>
                                                    <option value="amount" selected>Amt</option>
                                                </select>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="disc-amt fw-semibold text-muted">0.00</span>
                                        </td>
                                        <td class="text-end">
                                            <input type="number" name="bom_products[{{ $index }}][total]"
                                                class="form-control total-input text-end fw-semibold"
                                                value="{{ $b['total'] }}" step="0.01" readonly
                                                placeholder="0.00" title="Auto-calculated total">
                                        </td>
                                        <td class="text-center">
                                            <button type="button"
                                                class="btn btn-outline-danger btn-sm p-1 remove-bom"
                                                title="Remove this BOM component line">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="align-middle">
                                    <td>
                                        <select name="bom_products[0][bom_id]"
                                            class="form-select bom-select {{ $errors->has('bom_products.0.bom_id') ? 'is-invalid' : '' }}"
                                            required>
                                            <option value="">-- Select Component --</option>
                                            @foreach ($boms as $bom)
                                                <option value="{{ $bom->id }}" data-cost="{{ $bom->price }}">
                                                    {{ $bom->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="bom_products[0][qty]"
                                            class="form-control qty-input text-center {{ $errors->has('bom_products.0.qty') ? 'is-invalid' : '' }}"
                                            value="1" min="1" max="999" placeholder="1"
                                            title="Enter quantity">
                                    </td>
                                    <td class="text-end">
                                        <input type="number" name="bom_products[0][cost_price]"
                                            class="form-control cost-input text-end {{ $errors->has('bom_products.0.cost_price') ? 'is-invalid' : '' }}"
                                            value="0" step="0.01" placeholder="0.00"
                                            title="Cost auto-fills on component selection">
                                    </td>
                                    <td>
                                        <div class="input-group" style="gap: 2px;">
                                            <!-- Smaller input -->
                                            <input type="number" name="bom_products[0][discount]"
                                                class="form-control form-control-sm discount-input {{ $errors->has('bom_products.0.discount') ? 'is-invalid' : '' }}"
                                                value="0" step="0.01" min="0" placeholder="0.00"
                                                title="Enter discount amount or percentage" style="width: 80px;">

                                            <!-- Slightly wider select -->
                                            <select name="bom_products[0][discount_type]"
                                                class="form-select form-select-sm discount-type-input rounded-start-0 {{ $errors->has('bom_products.0.discount_type') ? 'is-invalid' : '' }}"
                                                style="width: 40px;">
                                                <option value="percentage">%</option>
                                                <option value="amount" selected>Amt</option>
                                            </select>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="disc-amt fw-semibold text-muted">0.00</span>
                                    </td>
                                    <td class="text-end">
                                        <input type="number" name="bom_products[0][total]"
                                            class="form-control total-input text-end fw-semibold" value="0"
                                            step="0.01" readonly placeholder="0.00" title="Auto-calculated total">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm p-1 remove-bom"
                                            title="Remove this BOM component line">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="table-light">
                            <tr class="fw-semibold">
                                <th colspan="5" class="text-end">Subtotal:</th>
                                <th class="text-end"><input type="number" id="subtotal"
                                        class="form-control fw-bold bg-transparent border-0 text-end" value="0"
                                        step="0.01" readonly></th>
                                <th></th>
                            </tr>
                            <tr class="fw-semibold">
                                <th colspan="5" class="text-end text-danger">BOM Discounts:</th>
                                <th class="text-end"><input type="number" id="total_discount"
                                        class="form-control fw-bold text-danger bg-transparent border-0 text-end"
                                        value="0" step="0.01" readonly></th>
                                <th></th>
                            </tr>
                            <tr class="fw-semibold text-dark" style="background-color: #d7dbdf !important;">
                                <th colspan="3" class="text-end pe-0">
                                    <div class="input-group">
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
                                        class="form-control fw-bold bg-transparent border-0 text-end" value="0"
                                        step="0.01" readonly></th>
                                <th></th>
                                <th></th>
                            </tr>
                            <tr class="bg-primary text-white">
                                <th colspan="6" class="text-end">Final Grand Total:</th>
                                <th class="text-end"><input type="number" id="grand_total"
                                        class="form-control fw-bold text-white bg-transparent border-0 text-end fs-5"
                                        value="0" step="0.01" readonly></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="form-text small text-muted mt-1">Apply overall discount on the subtotal after BOM discounts.
                Changes auto-update the final total.</div>
        </div>

        {{-- Status --}}
        <div class="col-12 col-md-6">
            <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
            <select name="status" id="status"
                class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}" required>
                <option value="pending"
                    {{ old('status', isset($purchase) ? $purchase->status : 'pending') == 'pending' ? 'selected' : '' }}>
                    Pending</option>
                <option value="received"
                    {{ old('status', isset($purchase) ? $purchase->status : 'pending') == 'received' ? 'selected' : '' }}>
                    Received</option>
            </select>
            <div class="form-text">Status of the purchase receipt.</div>
        </div>

        {{-- Notes --}}
        <div class="col-12">
            <label for="notes" class="form-label fw-semibold">Notes</label>
            <textarea name="notes" id="notes" class="form-control {{ $errors->has('notes') ? 'is-invalid' : '' }}"
                rows="3">{{ old('notes', isset($purchase) ? $purchase->notes : '') }}</textarea>
            <div class="form-text">Additional notes about the purchase.</div>
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
                                <option value="cheque" {{ $pay['payment_method'] == 'cheque' ? 'selected' : '' }}>
                                    Cheque
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
                            <input type="number" name="payments[{{ $i }}][payment_amount]"
                                value="{{ $pay['payment_amount'] ?? 0 }}"
                                class="form-control form-control payment-amount-input text-end {{ $errors->has("payments.$i.payment_amount") ? 'is-invalid' : '' }}"
                                placeholder="0.00" step="0.01" min="0" required
                                title="Enter payment amount">
                        </div>
                        <div class="col-12 col-md-2">
                            <label class="form-label small fw-semibold">Payment Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" name="payments[{{ $i }}][payment_date]"
                                value="{{ $pay['payment_date'] ?? date('Y-m-d') }}"
                                class="form-control {{ $errors->has("payments.$i.payment_date") ? 'is-invalid' : '' }}"
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
                                        {{ ($pay['bank_id'] ?? '') == $bank->id ? 'selected' : '' }}>
                                        {{ $bank->name }}
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
                                value="{{ $pay['transfer_ref'] ?? '' }}"
                                class="form-control mt-1 {{ $errors->has("payments.$i.transfer_ref") ? 'is-invalid' : '' }}"
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
                <button type="submit" id="submit-purchase" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i>
                    {{ isset($purchase) ? 'Update Purchase' : 'Create Purchase' }}
                </button>
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
                            <label for="supplier_name" class="form-label">Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="supplier_name" name="name" required
                                maxlength="255">
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="mb-3">
                            <label for="supplier_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="supplier_phone" name="mobile_number">
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

    {{-- New BOM Component Modal --}}
    <div class="modal fade" id="newBomComponentModal" tabindex="-1" aria-labelledby="newBomComponentModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newBomComponentModalLabel">Add New BOM Component</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="newBomComponentForm">
                    @csrf
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="product_id" class="form-label">Product <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" id="product_id" name="product_id" required>
                                    <option value="">-- Select Product --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label for="bom_name" class="form-label">Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="bom_name" name="name" required
                                    maxlength="255">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="bom_required_qty" class="form-label">Required BOM Qty <span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="bom_required_qty"
                                    name="required_bom_qty" required min="0.01" value="1" step="0.01">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="bom_product_code" class="form-label">Product Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="bom_product_code" name="product_code"
                                    required maxlength="255">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="bom_brand_id" class="form-label">Brand</label>
                                <select class="form-select" id="bom_brand_id" name="brand_id">
                                    <option value="">-- Select Brand (Optional) --</option>
                                    @foreach ($brands as $brand)
                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="bom_model" class="form-label">Model</label>
                                <input type="text" class="form-control" id="bom_model" name="model"
                                    maxlength="255">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="col-12">
                                <label for="bom_notes" class="form-label">Notes</label>
                                <textarea class="form-control" id="bom_notes" name="notes" rows="2"></textarea>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div id="bom-errors" class="alert alert-danger" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create BOM Component</button>
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
                let bomIndex = {{ count($oldBomProducts) }};
                let paymentIndex = {{ count($payments) }};

                // All BOM components data for search
                const allBoms = {!! json_encode(
                    $boms->map(function ($bom) {
                        return [
                            'id' => $bom->id,
                            'name' => $bom->name,
                            'cost' => $bom->price,
                        ];
                    }),
                ) !!};

                // BOM options HTML
                let bomsOptions = `{!! addslashes($bomsOptions) !!}`;

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
                        const num = parseFloat(e.target.value);
                        if (!isNaN(num)) {
                            let formatted;
                            if (e.target.classList.contains('qty-input')) {
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

                // Function to update payment fields visibility and required attributes
                function updatePaymentFields(paymentRow) {
                    const methodSelect = paymentRow.querySelector('.payment-method-select');
                    const method = methodSelect.value;
                    const amountInput = paymentRow.querySelector('.payment-amount-input');
                    const chequeFields = paymentRow.querySelector('.cheque-fields');
                    const fundFields = paymentRow.querySelector('.fund-transfer-fields');
                    const loanFields = paymentRow.querySelector('.loan-fields');

                    // Handle loan: set amount to 0 and readonly
                    // if (method === 'loan') {
                    //     amountInput.value = 0;
                    //     amountInput.readOnly = false;
                    //     amountInput.classList.add('bg-light');
                    // } else {
                    //     amountInput.readOnly = false;
                    //     amountInput.classList.remove('bg-light');
                    // }

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
                    document.querySelectorAll('.payment-amount-input').forEach(input => {
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
                }

                // Check if has credit payment
                function hasCreditPayment() {
                    return Array.from(document.querySelectorAll('.payment-method-select')).some(select => select
                        .value === 'loan');
                }

                // Form validation function
                function isFormValid() {
                    // Check basic required fields
                    if (!document.getElementById('supplier-select').value) return false;
                    if (!document.getElementById('purchase_date').value) return false;
                    if (!document.getElementById('status').value) return false;

                    // Check BOM rows
                    const bomRows = document.querySelectorAll('#bom-components-table tbody tr');
                    if (bomRows.length === 0) return false;
                    for (let row of bomRows) {
                        const bomId = row.querySelector('.bom-select').value;
                        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                        const total = parseFloat(row.querySelector('.total-input').value) || 0;
                        if (!bomId || qty < 1 || total <= 0) return false;
                    }

                    // Check payments
                    const paymentRows = document.querySelectorAll('.payment-row');
                    for (let prow of paymentRows) {
                        const methodSelect = prow.querySelector('.payment-method-select');
                        const method = methodSelect.value;
                        if (!method) return false;

                        const amountInput = prow.querySelector('.payment-amount-input');
                        const amount = parseFloat(amountInput.value);
                        if (isNaN(amount) || amount < 0) return false;

                        const paymentDate = prow.querySelector('input[name*="[payment_date]"]').value;
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
                            const bank = fundFields.querySelector('select[name*="[bank_account_id]"]').value;
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
                /*
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
                */

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

                // BOM Component Search Functionality
                const bomSearchInput = document.getElementById('bom-search');
                const bomSearchResults = document.getElementById('bom-search-results');

                bomSearchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();
                    bomSearchResults.innerHTML = '';
                    bomSearchResults.style.display = 'none';

                    if (query.length < 2) return;

                    const matches = allBoms.filter(bom => bom.name.toLowerCase().includes(query));
                    if (matches.length === 0) return;

                    matches.forEach(bom => {
                        const div = document.createElement('div');
                        div.className = 'p-2 border-bottom cursor-pointer hover-bg-light';
                        div.innerHTML = `
                <div class="fw-semibold">${bom.name}</div>
                <small class="text-muted">Cost: ${bom.cost}</small>
            `;
                        div.addEventListener('click', () => addBomFromSearch(bom));
                        bomSearchResults.appendChild(div);
                    });

                    bomSearchResults.style.display = 'block';
                });

                // Handle Enter key to add first matching BOM
                bomSearchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && this.value.length >= 2) {
                        e.preventDefault();
                        const query = this.value.toLowerCase().trim();
                        const matches = allBoms.filter(bom => bom.name.toLowerCase().includes(query));
                        if (matches.length > 0) {
                            addBomFromSearch(matches[0]);
                        }
                    }
                });

                // Hide results on outside click
                document.addEventListener('click', (e) => {
                    if (!bomSearchInput.contains(e.target) && !bomSearchResults.contains(e.target)) {
                        bomSearchResults.style.display = 'none';
                    }
                });

                function addBomFromSearch(bom) {
                    if (document.querySelectorAll('#bom-components-table tbody tr').length >= 50) {
                        alert('Maximum 50 BOM components allowed per purchase.');
                        return;
                    }
                    addEmptyBomRow(bom.id, bom.cost);
                    bomSearchInput.value = '';
                    bomSearchResults.style.display = 'none';
                }

                // Recalculate a single row total
                function recalcBomRow(row) {
                    const qtyInput = row.querySelector('.qty-input');
                    const qty = parseFloat(qtyInput.value) || 1;
                    if (qty < 1) {
                        qtyInput.value = '1';
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

                    // Format total (integer if whole, else 2 decimals)
                    row.querySelector('.total-input').value = Number.isInteger(lineTotal) ? lineTotal.toString() :
                        lineTotal.toFixed(2);

                    // Format disc amount (integer if whole, else 2 decimals)
                    row.querySelector('.disc-amt').textContent = Number.isInteger(discAmount) ? discAmount.toString() :
                        discAmount.toFixed(2);
                }

                // Recalculate all BOM totals including overall discount
                function recalcAllBoms() {
                    let subtotal = 0,
                        totalBomDiscount = 0;
                    document.querySelectorAll('#bom-components-table tbody tr').forEach(row => {
                        recalcBomRow(row); // Ensure each row is recalculated with min qty check
                        const qty = parseFloat(row.querySelector('.qty-input').value) || 1;
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
                        totalBomDiscount += discAmount;
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
                    const finalGrand = Math.max(0, subtotal - totalBomDiscount - overallDiscountAmount);

                    // Format and set values (integer if whole, else 2 decimals)
                    document.getElementById('subtotal').value = Number.isInteger(subtotal) ? subtotal.toString() :
                        subtotal.toFixed(2);
                    document.getElementById('total_discount').value = Number.isInteger(totalBomDiscount) ?
                        totalBomDiscount.toString() : totalBomDiscount.toFixed(2);
                    document.getElementById('overall_discount_amount').value = Number.isInteger(overallDiscountAmount) ?
                        overallDiscountAmount.toString() : overallDiscountAmount.toFixed(2);
                    document.getElementById('grand_total').value = Number.isInteger(finalGrand) ? finalGrand
                        .toString() : finalGrand.toFixed(2);

                    recalcTotalPaid();
                    // updateSubmitButton();
                }

                // Trigger cost update on BOM select change
                function updateBomCost(select) {
                    const row = select.closest('tr');
                    const selectedOption = select.selectedOptions[0];
                    if (selectedOption && selectedOption.value) {
                        const cost = parseFloat(selectedOption.getAttribute('data-cost')) || 0;
                        // Format cost (integer if whole, else 2 decimals)
                        row.querySelector('.cost-input').value = Number.isInteger(cost) ? cost.toString() : cost
                            .toFixed(2);
                    } else {
                        row.querySelector('.cost-input').value = '0.00';
                    }
                    recalcBomRow(row);
                    recalcAllBoms();
                }

                // Add empty BOM row
                function addEmptyBomRow(bomId = null, cost = 0) {
                    if (document.querySelectorAll('#bom-components-table tbody tr').length >= 50) {
                        alert('Maximum 50 BOM components allowed per purchase.');
                        return;
                    }
                    const tbody = document.querySelector('#bom-components-table tbody');
                    const tr = document.createElement('tr');
                    tr.classList.add('align-middle');
                    const optionsHtml = '<option value="">-- Select Component --</option>' + bomsOptions;
                    tr.innerHTML = `
            <td>
                <select name="bom_products[${bomIndex}][bom_id]" class="form-select bom-select" required>
                    ${optionsHtml}
                </select>
            </td>
            <td><input type="number" name="bom_products[${bomIndex}][qty]" class="form-control qty-input text-center" value="1" min="1" max="999" placeholder="1" title="Enter quantity"></td>
            <td class="text-end"><input type="number" name="bom_products[${bomIndex}][cost_price]" class="form-control cost-input text-end" value="${bomId ? (Number.isInteger(cost) ? cost.toString() : cost.toFixed(2)) : '0.00'}" step="0.01" placeholder="0.00" title="Cost auto-fills"></td>
                                    <td>
                                        <div class="input-group" style="gap: 2px;">
                                            <!-- Smaller input -->
                                            <input type="number" name="bom_products[0][discount]"
                                                class="form-control form-control-sm discount-input {{ $errors->has('bom_products.0.discount') ? 'is-invalid' : '' }}"
                                                value="0" step="0.01" min="0" placeholder="0.00"
                                                title="Enter discount amount or percentage" style="width: 80px;">

                                            <!-- Slightly wider select -->
                                            <select name="bom_products[0][discount_type]"
                                                class="form-select form-select-sm discount-type-input rounded-start-0 {{ $errors->has('bom_products.0.discount_type') ? 'is-invalid' : '' }}"
                                                style="width: 40px;">
                                                <option value="percentage">%</option>
                                                <option value="amount" selected>Amt</option>
                                            </select>
                                        </div>
                                    </td>
            <td class="text-end"><span class="disc-amt fw-semibold text-muted">0.00</span></td>
            <td class="text-end"><input type="number" name="bom_products[${bomIndex}][total]" class="form-control total-input text-end fw-semibold" value="0" step="0.01" readonly placeholder="0.00" title="Line total"></td>
            <td class="text-center">
                <button type="button" class="btn btn-outline-danger btn-sm p-1 remove-bom" title="Remove line">
                    <i class="bi bi-trash"></i>
                </button>
            </td>`;
                    tbody.appendChild(tr);
                    bomIndex++;
                    if (bomId) {
                        const newSelect = tr.querySelector('.bom-select');
                        newSelect.value = bomId;
                        updateBomCost(newSelect);
                    } else {
                        recalcAllBoms();
                    }
                    tr.querySelector('.qty-input').focus();
                }

                // Add BOM Row (manual)
                document.getElementById('add-bom-component').addEventListener('click', () => {
                    addEmptyBomRow();
                });

                // Remove BOM or Payment
                document.addEventListener('click', e => {
                    if (e.target.closest('.remove-bom')) {
                        const row = e.target.closest('tr');
                        const rows = document.querySelectorAll('#bom-components-table tbody tr');
                        if (rows.length > 1 || confirm('This will remove the only BOM component. Continue?')) {
                            row.remove();
                            recalcAllBoms();
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
                    if (e.target.classList.contains('bom-select')) {
                        updateBomCost(e.target);
                    }
                    if (e.target.classList.contains('payment-method-select')) {
                        updatePaymentFields(e.target.closest('.payment-row'));
                    }
                    if (e.target.classList.contains('discount-type-input')) {
                        const row = e.target.closest('tr');
                        recalcBomRow(row);
                        recalcAllBoms();
                    }
                    if (e.target.id === 'overall_discount_type') {
                        recalcAllBoms();
                    }
                    if (e.target.id === 'supplier-select' || e.target.id === 'status' || e.target.id ===
                        'purchase_date') {
                        // updateSubmitButton();
                    }
                });

                document.addEventListener('input', e => {
                    const row = e.target.closest('tr');
                    if (row && (e.target.classList.contains('qty-input') || e.target.classList.contains(
                            'cost-input'))) {
                        recalcBomRow(row);
                        recalcAllBoms();
                    }
                    if (e.target.classList.contains('discount-input')) {
                        if (parseFloat(e.target.value) < 0) e.target.value = 0;
                        const row = e.target.closest('tr');
                        if (row) {
                            recalcBomRow(row);
                            recalcAllBoms();
                        }
                    }
                    if (e.target.id === 'overall_discount_value') {
                        if (parseFloat(e.target.value) < 0) e.target.value = 0;
                        recalcAllBoms();
                    }
                    if (e.target.classList.contains('payment-amount-input')) {
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
                <select name="payments[${paymentIndex}][payment_method]" class="form-select form-select-sm payment-method-select" required>
                    <option value="cash">Cash</option>
                    <option value="cheque">Cheque</option>
                    <option value="loan">Credit</option>
                    <option value="fund_transfer">Fund Transfer</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold">Amount <span class="text-danger">*</span></label>
                <input type="number" name="payments[${paymentIndex}][payment_amount]" class="form-control form-control payment-amount-input text-end" placeholder="0.00" step="0.01" min="0" required title="Amount paid">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold">Payment Date <span class="text-danger">*</span></label>
                <input type="date" name="payments[${paymentIndex}][payment_date]" class="form-control" value="{{ date('Y-m-d') }}" required title="Payment date">
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
                    div.querySelector('.payment-amount-input').focus();
                    // updateSubmitButton();
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
                                    // Read as text first to handle non-JSON responses (e.g., HTML errors)
                                    return response.text().then(text => {
                                        if (!response.ok) {
                                            try {
                                                const errData = JSON.parse(text);
                                                throw errData; // Throw parsed error object
                                            } catch (parseErr) {
                                                // If not JSON (e.g., HTML like <!DOCTYPE), log and throw generic
                                                console.error(
                                                    'Non-JSON error response (likely HTML):',
                                                    text.substring(0, 500) + '...');
                                                throw new Error(
                                                    `Server error (${response.status}): ${response.statusText}. Check server logs or route configuration.`
                                                );
                                            }
                                        }
                                        // Parse success response
                                        try {
                                            return JSON.parse(text);
                                        } catch (parseErr) {
                                            console.error('Non-JSON success response:', text
                                                .substring(0, 500) + '...');
                                            throw new Error(
                                                'Unexpected response format from server.');
                                        }
                                    });
                                })
                                .then(data => {
                                    if (data.success) {
                                        // Add new supplier to dropdown
                                        const select = document.getElementById('supplier-select');
                                        if (select) {
                                            const newOption = new Option(data.data.name, data.data.id, true,
                                                true);
                                            select.appendChild(newOption);
                                        }
                                        newSupplierModal.hide();
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: data.message || 'Supplier created successfully!',
                                            confirmButtonText: 'OK'
                                        });
                                    } else {
                                        // Handle validation errors or other failures
                                        const errorsDiv = document.getElementById('supplier-errors');
                                        if (errorsDiv) {
                                            let errorHtml =
                                                '<strong>Please fix the following errors:</strong><ul class="mb-0 mt-1">';
                                            if (data.errors && typeof data.errors === 'object') {
                                                errorHtml += Object.values(data.errors).flat().map(err =>
                                                    `<li>${err}</li>`).join('');
                                            } else if (data.message) {
                                                errorHtml += `<li>${data.message}</li>`;
                                            } else {
                                                errorHtml += '<li>An unknown error occurred.</li>';
                                            }
                                            errorHtml += '</ul>';
                                            errorsDiv.innerHTML = errorHtml;
                                            errorsDiv.style.display = 'block';
                                        }
                                        // Highlight fields
                                        if (data.errors && typeof data.errors === 'object') {
                                            Object.keys(data.errors).forEach(field => {
                                                const input = document.getElementById(
                                                    `supplier_${field.replace('_', '-')}`);
                                                if (input) {
                                                    input.classList.add('is-invalid');
                                                }
                                            });
                                        }
                                    }
                                })
                                .catch(error => {
                                    console.error('Fetch error details:', error);
                                    // Improved error handling: Use Swal for better UX, show specific message if available
                                    let errorMsg =
                                        'An error occurred while creating the supplier. Please check the console for details.';
                                    if (error.message) {
                                        errorMsg = error.message;
                                    } else if (error.errors && typeof error.errors === 'object') {
                                        errorMsg = Object.values(error.errors).flat().join(', ');
                                    }
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: errorMsg,
                                        confirmButtonText: 'OK'
                                    });
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

                // New BOM Component Modal Functionality
                const newBomComponentModalEl = document.getElementById('newBomComponentModal');
                if (newBomComponentModalEl) {
                    const newBomComponentModal = new bootstrap.Modal(newBomComponentModalEl);
                    const newBomComponentForm = document.getElementById('newBomComponentForm');
                    document.getElementById('add-new-bom-component').addEventListener('click', () => {
                        if (newBomComponentForm) {
                            newBomComponentForm.reset();
                        }
                        const errorsDiv = document.getElementById('bom-errors');
                        if (errorsDiv) {
                            errorsDiv.style.display = 'none';
                        }
                        newBomComponentModal.show();
                    });

                    if (newBomComponentForm) {
                        newBomComponentForm.addEventListener('submit', function(e) {
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

                            fetch('/api/bom-components', { // Assuming route is /admin/bom-components for POST
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
                                    if (data.status === 'success') {
                                        // Add new BOM to allBoms array
                                        allBoms.push({
                                            id: data.data.id,
                                            name: data.data.name,
                                            cost: data.data.price || 0
                                        });
                                        // Update bomsOptions dynamically
                                        const newOption =
                                            `<option value="${data.data.id}" data-cost="${data.data.price || 0}">${data.data.name}</option>`;
                                        // Append to existing options
                                        bomsOptions = bomsOptions + newOption;
                                        // Update all existing selects
                                        document.querySelectorAll('.bom-select').forEach(select => {
                                            const currentValue = select.value;
                                            select.innerHTML =
                                                '<option value="">-- Select Component --</option>' +
                                                bomsOptions;
                                            if (currentValue) {
                                                select.value = currentValue;
                                            }
                                        });
                                        newBomComponentModal.hide();
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: 'BOM Component created successfully!',
                                            confirmButtonText: 'OK'
                                        });
                                        // updateSubmitButton();
                                    } else {
                                        // Handle validation errors
                                        const errorsDiv = document.getElementById('bom-errors');
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
                                                `bom_${field.replace('_', '-')}`);
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
                                            'An error occurred while creating the BOM component. Please check the console for details.'
                                        );
                                    }
                                })
                                .finally(() => {
                                    if (submitBtn) {
                                        submitBtn.disabled = false;
                                        submitBtn.innerHTML = 'Create BOM Component';
                                    }
                                });
                        });
                    }

                    // Clear invalid classes on input
                    ['bom_name', 'bom_required_qty', 'bom_product_code', 'bom_brand_id', 'bom_model',
                        'bom_notes'
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
                recalcAllBoms();
                // updateSubmitButton();
            });
        </script>
    @endsection
