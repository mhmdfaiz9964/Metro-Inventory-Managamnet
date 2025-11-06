@extends('layouts.app')

@section('title', 'Supplier History')

@section('content')
<div class="container my-5">
    <div class="card shadow-lg border-0 overflow-hidden" style="background: linear-gradient(135deg, #1e0041 0%, #1e0041 100%);">
        <div class="card-header bg-transparent text-white border-0 py-4">
            <h4 class="mb-0 d-flex justify-content-between align-items-center text-white">
                <span><i class="bi bi-person-circle me-2"></i>{{ $supplier->name }} - History</span>
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-light btn-sm shadow-sm">
                    <i class="bi bi-arrow-left"></i> Back
                </a>
            </h4>
        </div>

        <div class="card-body p-0">
            <div class="p-1"></div> {{-- Space between header and tabs --}}

            {{-- TAB NAVIGATION --}}
            <ul class="nav nav-tabs nav-fill mb-0 bg-white" id="historyTabs" role="tablist" style="box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-0 rounded-0 px-4 py-3 fw-semibold border-bottom border-primary" id="details-tab" data-bs-toggle="tab" data-bs-target="#details"
                        type="button" role="tab" style="color: #1e0041 !important; border-bottom-color: #1e0041 !important;">
                        <i class="bi bi-info-circle me-1"></i> Details
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-0 rounded-0 px-4 py-3 fw-semibold border-bottom border-primary" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity"
                        type="button" role="tab" style="color: #1e0041 !important; border-bottom-color: #1e0041 !important;">
                        <i class="bi bi-activity me-1"></i> Activity
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link border-0 rounded-0 px-4 py-3 fw-semibold border-bottom border-primary" id="balance-tab" data-bs-toggle="tab" data-bs-target="#balance"
                        type="button" role="tab" style="color: #1e0041 !important; border-bottom-color: #1e0041 !important;">
                        <i class="bi bi-cash-coin me-1"></i> Outstanding Balance
                    </button>
                </li>
            </ul>

            {{-- TAB CONTENT --}}
            <div class="tab-content p-4 bg-light" id="historyTabsContent">
                
                {{-- DETAILS TAB --}}
                <div class="tab-pane fade show active" id="details" role="tabpanel">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3 fw-bold" style="color: #1e0041;"><i class="bi bi-building me-2"></i>Supplier Info</h5>
                            <div class="list-group list-group-flush bg-white shadow-sm rounded p-3">
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Name:</strong>
                                    <span class="text-dark">{{ $supplier->name }}</span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Mobile:</strong>
                                    <span class="text-dark">{{ $supplier->mobile_number }}</span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Email:</strong>
                                    <span class="text-dark">{{ $supplier->email ?? 'N/A' }}</span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center">
                                    <strong class="text-muted">Status:</strong>
                                    <span class="badge bg-secondary ms-2" style="background-color: #1e0041; color: white;">
                                        {{ ucfirst($supplier->status) }}
                                    </span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Notes:</strong>
                                    <span class="text-dark">{{ $supplier->notes ?? 'No notes' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary mb-3 fw-bold" style="color: #1e0041;"><i class="bi bi-graph-up me-2"></i>Financial Summary</h5>
                            <div class="list-group list-group-flush bg-white shadow-sm rounded p-3">
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between py-2">
                                    <span class="text-muted">Total Paid:</span>
                                    <strong class="fs-5" style="color: #1e0041;">Rs. {{ number_format($totalPaid, 2) }}</strong>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between py-2">
                                    <span class="text-muted">Balance Due:</span>
                                    <strong class="fs-5" style="color: #1e0041;">Rs. {{ number_format($balanceDue, 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ACTIVITY TAB --}}
                <div class="tab-pane fade" id="activity" role="tabpanel">

                    {{-- PURCHASE HISTORY --}}
                    <h5 class="mb-3 fw-bold" style="color: #1e0041;"><i class="bi bi-cart me-2"></i>Purchases</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover table-striped table-sm shadow-lg rounded overflow-hidden">
                            <thead style="background-color: #1e0041; color: white;">
                                <tr>
                                    <th class="border-0">Purchase ID</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Total</th>
                                    <th class="border-0">Payments</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($purchases as $purchase)
                                    <tr>
                                        <td><span class="badge bg-secondary rounded-pill" style="background-color: #1e0041; color: white;">#{{ $purchase->id }}</span></td>
                                        <td class="text-muted">{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                                        <td>
                                            @forelse($purchase->payments as $payment)
                                                <span class="badge rounded-pill me-1 mb-1" style="background-color: #1e0041; color: white;">{{ ucfirst($payment->payment_method) }}: Rs. {{ number_format($payment->amount, 2) }}</span>
                                            @empty
                                                <span class="text-muted small">No payments</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No purchases found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- LOANS --}}
                    <h5 class="mb-3 fw-bold" style="color: #1e0041;"><i class="bi bi-bank me-2"></i>Credits</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover table-striped table-sm shadow-lg rounded overflow-hidden">
                            <thead style="background-color: #1e0041; color: white;">
                                <tr>
                                    <th class="border-0">Reference</th>
                                    <th class="border-0">Amount</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Credits Date</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($loans as $loan)
                                    <tr>
                                        <td><span class="badge bg-secondary rounded-pill" style="background-color: #1e0041; color: white;">#{{ $loan->reference_number }}</span></td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($loan->amount, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill" style="background-color: #1e0041; color: white;">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ $loan->loan_date->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No loans found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- PAYMENTS --}}
                    <h5 class="fw-bold" style="color: #1e0041;"><i class="bi bi-credit-card me-2"></i>Payments</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm shadow-lg rounded overflow-hidden">
                            <thead style="background-color: #1e0041; color: white;">
                                <tr>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Method</th>
                                    <th class="border-0">Amount</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($payments as $payment)
                                    <tr>
                                        <td class="text-muted">{{ $payment->payment_date->format('Y-m-d') }}</td>
                                        <td>
                                            <span class="badge rounded-pill" style="background-color: #1e0041; color: white;">{{ ucfirst($payment->payment_method) }}</span>
                                        </td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($payment->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-4">No payments found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- BALANCE TAB --}}
                <div class="tab-pane fade" id="balance" role="tabpanel">
                    <h5 class="mb-4 fw-bold" style="color: #1e0041;"><i class="bi bi-clipboard-data me-2"></i>Outstanding Balance Summary</h5>
                    
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">
                        <i class="bi bi-plus-circle me-2"></i>Add Payment
                    </button>
                    
                    {{-- SUMMARY CARDS --}}
                    <div class="row mb-4 g-3">
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 text-center p-3" style="background-color: #1e0041; color: white; border-radius: 0.75rem;">
                                <div class="card-body">
                                    <h6 class="mb-1" style="color: rgba(255,255,255,0.8);">Total Purchases</h6>
                                    <h4 class="mb-0 fw-bold text-white">Rs. {{ number_format($purchases->sum('total_amount'), 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 text-center p-3" style="background-color: #1e0041; color: white; border-radius: 0.75rem;">
                                <div class="card-body">
                                    <h6 class="mb-1" style="color: rgba(255,255,255,0.8);">Total Paid</h6>
                                    <h4 class="mb-0 fw-bold text-white">Rs. {{ number_format($totalPaid, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card border-0 shadow-sm h-100 text-center p-3" style="background-color: #1e0041; color: white; border-radius: 0.75rem;">
                                <div class="card-body">
                                    <h6 class="mb-1" style="color: rgba(255,255,255,0.8);">Outstanding Balance</h6>
                                    <h4 class="mb-0 fw-bold text-white">Rs. {{ number_format($balanceDue, 2) }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- PENDING PURCHASES TABLE --}}
                    <h6 class="text-muted mb-3">Pending Purchase Dues Details</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm shadow-lg rounded overflow-hidden">
                            <thead style="background-color: #1e0041; color: white;">
                                <tr>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Purchase ID</th>
                                    <th class="border-0">Total Amount</th>
                                    <th class="border-0">Paid Amount</th>
                                    <th class="border-0">Outstanding Due</th>
                                    <th class="border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($outstandingPurchases as $purchase)
                                    @php
                                        $paid = $purchase->payments->sum('amount');
                                        $due = $purchase->total_amount - $paid;
                                    @endphp
                                    <tr>
                                        <td class="text-muted">{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-secondary rounded-pill" style="background-color: #1e0041; color: white;">#{{ $purchase->id }}</span></td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                                        <td style="color: #1e0041;">Rs. {{ number_format($paid, 2) }}</td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($due, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill" style="background-color: #1e0041; color: white;">Pending</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No outstanding purchases.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- Add Payment Modal --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="paymentForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Supplier Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Supplier Select --}}
                    <div class="mb-3">
                        <label for="modalSupplierSelect" class="form-label">Select Supplier</label>
                        <select class="form-select" id="modalSupplierSelect" required>
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $supplier->id == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Purchase Select --}}
                    <div class="mb-3 d-none" id="purchaseWrapper">
                        <label for="modalPurchaseSelect" class="form-label">Purchase</label>
                        <select class="form-select" id="modalPurchaseSelect" name="purchase_id">
                            <option value="">-- Select Purchase --</option>
                        </select>
                        <small id="purchaseBalance" class="text-muted">Balance: Rs. 0.00</small>
                    </div>

                    {{-- Payment Amount --}}
                    <div class="mb-3">
                        <label for="modalPaymentAmount" class="form-label">Payment Amount</label>
                        <input type="number" class="form-control" name="payment_amount" id="modalPaymentAmount" required min="1">
                    </div>

                    {{-- Payment Date --}}
                    <div class="mb-3">
                        <label for="modalPaymentDate" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" name="payment_date" id="modalPaymentDate" value="{{ date('Y-m-d') }}" required>
                    </div>

                    {{-- Payment Method --}}
                    <div class="mb-3">
                        <label for="modalPaymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method" id="modalPaymentMethod" required>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="account">Bank Account</option>
                        </select>
                    </div>

                    {{-- Cheque Fields --}}
                    <div id="chequeFields" class="d-none">
                        <div class="mb-3">
                            <label for="modalChequeNo" class="form-label">Cheque Number</label>
                            <input type="text" class="form-control" name="cheque_no" id="modalChequeNo">
                        </div>
                        <div class="mb-3">
                            <label for="modalBankSelect" class="form-label">Bank</label>
                            <select class="form-select" name="bank_id" id="modalBankSelect">
                                <option value="">-- Select Bank --</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div class="mb-3">
                        <label for="modalNotes" class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" id="modalNotes"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Add Payment</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const supplierSelect = document.getElementById('modalSupplierSelect');
    const purchaseSelect = document.getElementById('modalPurchaseSelect');
    const purchaseWrapper = document.getElementById('purchaseWrapper');
    const purchaseBalance = document.getElementById('purchaseBalance');
    const paymentForm = document.getElementById('paymentForm');
    const paymentMethod = document.getElementById('modalPaymentMethod');
    const chequeFields = document.getElementById('chequeFields');

    // Prefill supplier for current page
    const currentSupplierId = {{ $supplier->id }};
    supplierSelect.value = currentSupplierId;
    loadPurchases(currentSupplierId);

    function loadPurchases(supplierId) {
        purchaseSelect.innerHTML = '<option value="">-- Select Purchase --</option>';
        purchaseWrapper.classList.add('d-none');
        purchaseBalance.textContent = 'Balance: Rs. 0.00';
        paymentForm.action = `/admin/suppliers-payments/${supplierId}/store`;

        if (!supplierId) return;

        fetch(`/admin/suppliers-payments/${supplierId}`)
            .then(res => res.json())
            .then(data => {
                data.purchases.forEach(purchase => {
                    purchaseSelect.innerHTML += `
                        <option value="${purchase.id}" data-balance="${purchase.balance_due}">
                            Purchase #${purchase.id} - Rs. ${parseFloat(purchase.balance_due).toFixed(2)}
                        </option>`;
                });
                purchaseWrapper.classList.remove('d-none');
            });
    }

    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        loadPurchases(supplierId);
    });

    purchaseSelect.addEventListener('change', function() {
        const selectedOption = this.selectedOptions[0];
        const balance = selectedOption?.dataset.balance || 0;
        purchaseBalance.textContent = `Balance: Rs. ${parseFloat(balance).toFixed(2)}`;
    });

    paymentMethod.addEventListener('change', function() {
        chequeFields.classList.toggle('d-none', this.value !== 'cheque');
    });
});
</script>
@endsection