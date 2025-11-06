@extends('layouts.app')

@section('title', 'Customer History')

@section('content')
<div class="container my-5">
    <div class="card shadow-lg border-0 overflow-hidden" style="background: linear-gradient(135deg, #1e0041 0%, #1e0041 100%);">
        <div class="card-header bg-transparent text-white border-0 py-4">
            <h4 class="mb-0 d-flex justify-content-between align-items-center text-white">
                <span><i class="bi bi-person-circle me-2"></i>{{ $customer->name }} - History</span>
                <a href="{{ route('admin.customers.index') }}" class="btn btn-light btn-sm shadow-sm">
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
                            <h5 class="text-primary mb-3 fw-bold" style="color: #1e0041;"><i class="bi bi-building me-2"></i>Customer Info</h5>
                            <div class="list-group list-group-flush bg-white shadow-sm rounded p-3">
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Name:</strong>
                                    <span class="text-dark">{{ $customer->name }}</span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Mobile:</strong>
                                    <span class="text-dark">{{ $customer->mobile_number }}</span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Email:</strong>
                                    <span class="text-dark">{{ $customer->email ?? 'N/A' }}</span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between align-items-center">
                                    <strong class="text-muted">Status:</strong>
                                    <span class="badge bg-secondary ms-2" style="background-color: #1e0041; color: white;">
                                        {{ ucfirst($customer->status ?? 'active') }}
                                    </span>
                                </div>
                                <div class="list-group-item px-0 border-0 d-flex justify-content-between">
                                    <strong class="text-muted">Notes:</strong>
                                    <span class="text-dark">{{ $customer->notes ?? 'No notes' }}</span>
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

                    {{-- SALES HISTORY --}}
                    <h5 class="mb-3 fw-bold" style="color: #1e0041;"><i class="bi bi-cart me-2"></i>Sales</h5>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover table-striped table-sm shadow-lg rounded overflow-hidden">
                            <thead style="background-color: #1e0041; color: white;">
                                <tr>
                                    <th class="border-0">Sale ID</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Total</th>
                                    <th class="border-0">Payments</th>
                                    <th class="border-0">Items</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($sales as $sale)
                                    <tr>
                                        <td><span class="badge bg-secondary rounded-pill" style="background-color: #1e0041; color: white;">#{{ $sale->id }}</span></td>
                                        <td class="text-muted">{{ $sale->sale_date->format('Y-m-d') }}</td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                        <td>
                                            @forelse($sale->salePayments as $payment)
                                                <span class="badge rounded-pill me-1 mb-1" style="background-color: #1e0041; color: white;">{{ ucfirst($payment->payment_method) }}: Rs. {{ number_format($payment->payment_paid, 2) }}</span>
                                            @empty
                                                <span class="text-muted small">No payments</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @forelse($sale->items as $item)
                                                <span class="text-muted small">{{ $item->product->name ?? 'N/A' }} (Qty: {{ $item->quantity }})</span><br>
                                            @empty
                                                <span class="text-muted small">No items</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No sales found.</td></tr>
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
                                    <th class="border-0">Type</th>
                                    <th class="border-0">Amount</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0">Loan Date</th>
                                    <th class="border-0">Due Date</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($loans as $loan)
                                    <tr>
                                        <td><span class="badge bg-secondary rounded-pill" style="background-color: #1e0041; color: white;">#{{ $loan->reference_number }}</span></td>
                                        <td>{{ ucfirst($loan->type) }}</td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($loan->amount, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill" style="background-color: #1e0041; color: white;">
                                                {{ ucfirst($loan->status) }}
                                            </span>
                                        </td>
                                        <td class="text-muted">{{ $loan->loan_date->format('Y-m-d') }}</td>
                                        <td class="text-muted">{{ $loan->due_date->format('Y-m-d') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No credits found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- SALE RETURNS --}}
                    <h5 class="fw-bold" style="color: #1e0041;"><i class="bi bi-arrow-return-left me-2"></i>Sale Returns</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm shadow-lg rounded overflow-hidden">
                            <thead style="background-color: #1e0041; color: white;">
                                <tr>
                                    <th class="border-0">Return ID</th>
                                    <th class="border-0">Sale ID</th>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Total Amount</th>
                                    <th class="border-0">Items</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($returns as $return)
                                    <tr>
                                        <td><span class="badge bg-secondary rounded-pill" style="background-color: #1e0041; color: white;">#{{ $return->id }}</span></td>
                                        <td class="text-muted">#{{ $return->sale_id }}</td>
                                        <td class="text-muted">{{ $return->return_date->format('Y-m-d') }}</td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($return->total_amount, 2) }}</td>
                                        <td>
                                            @forelse($return->items as $item)
                                                <span class="text-muted small">{{ $item->product->name ?? 'N/A' }} (Qty: {{ $item->quantity }})</span><br>
                                            @empty
                                                <span class="text-muted small">No items</span>
                                            @endforelse
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-4">No sale returns found.</td></tr>
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
                                    <h6 class="mb-1" style="color: rgba(255,255,255,0.8);">Total Sales</h6>
                                    <h4 class="mb-0 fw-bold text-white">Rs. {{ number_format($sales->sum('total_amount'), 2) }}</h4>
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

                    {{-- PENDING SALES TABLE --}}
                    <h6 class="text-muted mb-3">Pending Sale Dues Details</h6>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped table-sm shadow-lg rounded overflow-hidden">
                            <thead style="background-color: #1e0041; color: white;">
                                <tr>
                                    <th class="border-0">Date</th>
                                    <th class="border-0">Sale ID</th>
                                    <th class="border-0">Total Amount</th>
                                    <th class="border-0">Paid Amount</th>
                                    <th class="border-0">Outstanding Due</th>
                                    <th class="border-0">Status</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                @forelse($outstandingSales as $sale)
                                    @php
                                        $paid = $sale->salePayments->sum('payment_paid');
                                        $due = $sale->total_amount - $paid;
                                    @endphp
                                    <tr>
                                        <td class="text-muted">{{ $sale->sale_date->format('Y-m-d') }}</td>
                                        <td><span class="badge bg-secondary rounded-pill" style="background-color: #1e0041; color: white;">#{{ $sale->id }}</span></td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                        <td style="color: #1e0041;">Rs. {{ number_format($paid, 2) }}</td>
                                        <td class="fw-bold" style="color: #1e0041;">Rs. {{ number_format($due, 2) }}</td>
                                        <td>
                                            <span class="badge rounded-pill" style="background-color: #1e0041; color: white;">Pending</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No outstanding sales.</td></tr>
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
                    <h5 class="modal-title" id="addPaymentModalLabel">Add Customer Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    {{-- Customer Select --}}
                    <div class="mb-3">
                        <label for="modalCustomerSelect" class="form-label">Select Customer</label>
                        <select class="form-select" id="modalCustomerSelect" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers as $cust)
                                <option value="{{ $cust->id }}" {{ $cust->id == $customer->id ? 'selected' : '' }}>{{ $cust->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sale Select --}}
                    <div class="mb-3 d-none" id="saleWrapper">
                        <label for="modalSaleSelect" class="form-label">Sale</label>
                        <select class="form-select" id="modalSaleSelect" name="sale_id">
                            <option value="">-- Select Sale --</option>
                        </select>
                        <small id="saleBalance" class="text-muted">Balance: Rs. 0.00</small>
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

{{-- Mark Paid Modal --}}
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="markPaidForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="markPaidModalLabel">Mark Cheque as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cheque_id" id="modal_cheque_id">

                    {{-- Payment Method --}}
                    <div class="mb-3" id="paymentMethodWrapper">
                        <label for="modalPaymentMethod" class="form-label">Payment Method</label>
                        <select class="form-select" name="payment_method" id="modalPaymentMethod" required>
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="loan">Loan</option>
                            <option value="fund_transfer">Fund Transfer</option>
                        </select>
                    </div>

                    {{-- Paid Bank Account --}}
                    <div class="mb-3 d-none" id="paidBankWrapper">
                        <label for="paidBankAccount" class="form-label">Paid Bank Account</label>
                        <select name="paid_bank_account_id" id="paidBankAccount" class="form-select">
                            <option value="">-- Select Bank Account --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_paid_date" class="form-label">Paid Date</label>
                        <input type="date" name="paid_date" id="modal_paid_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const customerSelect = document.getElementById('modalCustomerSelect');
    const saleSelect = document.getElementById('modalSaleSelect');
    const saleWrapper = document.getElementById('saleWrapper');
    const saleBalance = document.getElementById('saleBalance');
    const paymentForm = document.getElementById('paymentForm');
    const paymentMethod = document.getElementById('modalPaymentMethod');
    const chequeFields = document.getElementById('chequeFields');

    // Prefill customer for current page
    const currentCustomerId = {{ $customer->id }};
    customerSelect.value = currentCustomerId;
    loadSales(currentCustomerId);

    function loadSales(customerId) {
        saleSelect.innerHTML = '<option value="">-- Select Sale --</option>';
        saleWrapper.classList.add('d-none');
        saleBalance.textContent = 'Balance: Rs. 0.00';
        paymentForm.action = `/admin/customer-payments/${customerId}/store`;

        if (!customerId) return;

        fetch(`/admin/customer-payments/${customerId}`)
            .then(res => res.json())
            .then(data => {
                data.sales.forEach(sale => {
                    saleSelect.innerHTML += `
                        <option value="${sale.id}" data-balance="${sale.balance_due}">
                            Sale #${sale.id} - Rs. ${parseFloat(sale.balance_due).toFixed(2)}
                        </option>`;
                });
                saleWrapper.classList.remove('d-none');
            });
    }

    customerSelect.addEventListener('change', function() {
        const customerId = this.value;
        loadSales(customerId);
    });

    saleSelect.addEventListener('change', function() {
        const selectedOption = this.selectedOptions[0];
        const balance = selectedOption?.dataset.balance || 0;
        saleBalance.textContent = `Balance: Rs. ${parseFloat(balance).toFixed(2)}`;
    });

    paymentMethod.addEventListener('change', function() {
        chequeFields.classList.toggle('d-none', this.value !== 'cheque');
    });

    // Delete buttons
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const chequeId = this.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + chequeId).submit();
                }
            });
        });
    });

    // Mark Paid modal open
    document.querySelectorAll('.btn-mark-paid').forEach(btn => {
        btn.addEventListener('click', function() {
            const chequeId = this.dataset.id;
            document.getElementById('modal_cheque_id').value = chequeId;
            document.getElementById('modal_paid_date').valueAsDate = new Date();
        });
    });

    // Payment method toggle bank dropdown
    const markPaymentMethod = document.getElementById('markPaidModalPaymentMethod');
    const markPaidBankWrapper = document.getElementById('paidBankWrapper');
    const markPaidBankSelect = document.getElementById('paidBankAccount');

    if (markPaymentMethod) {
        markPaymentMethod.addEventListener('change', function() {
            if(this.value === 'fund_transfer') {
                markPaidBankWrapper.classList.remove('d-none');
                markPaidBankSelect.setAttribute('required', true);
            } else {
                markPaidBankWrapper.classList.add('d-none');
                markPaidBankSelect.removeAttribute('required');
            }
        });
        markPaymentMethod.dispatchEvent(new Event('change'));
    }

    // Submit Mark Paid
    document.getElementById('markPaidForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const chequeId = document.getElementById('modal_cheque_id').value;
        const paidDate = document.getElementById('modal_paid_date').value;
        const paidBank = document.getElementById('paidBankAccount').value;
        const paymentMethodValue = document.getElementById('modalPaymentMethod').value;
        const token = document.querySelector('input[name="_token"]').value;

        fetch(`/admin/receiving-cheques/mark-received/${chequeId}`, {
            method: 'POST',
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify({ paid_date: paidDate, paid_bank_account_id: paidBank, payment_method: paymentMethodValue })
        })
        .then(res => res.json())
        .then(res => { if(res.success) location.reload(); });
    });
});
</script>
@endsection