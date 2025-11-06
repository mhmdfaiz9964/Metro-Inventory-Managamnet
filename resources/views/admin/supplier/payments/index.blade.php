@extends('layouts.app')

@section('title', 'Supplier Payments')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Supplier Payments</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>
        </div>

        <div class="card-body">
            {{-- Payments Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="paymentsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Supplier</th>
                            <th>Purchase</th>
                            <th>Payment Amount</th>
                            <th>Payment Method</th>
                            <th>Bank</th>
                            <th>Payment Date</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $i = 1; @endphp
                        @foreach($payments as $payment)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $payment->supplier->name ?? '-' }}</td>
                                <td>#{{ $payment->purchase->id ?? '-' }}</td>
                                <td>Rs. {{ number_format($payment->payment_amount, 2) }}</td>
                                <td>{{ ucfirst($payment->payment_method) }}</td>
                                <td>{{ $payment->bank->name ?? '-' }}</td>
                                <td>{{ $payment->payment_date->format('Y-m-d') }}</td>
                                <td>{{ $payment->notes }}</td>
                            </tr>
                        @endforeach
                        @if($i === 1)
                            <tr>
                                <td colspan="8" class="text-center">No payments found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
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
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
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

    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        purchaseSelect.innerHTML = '<option value="">-- Select Purchase --</option>';
        purchaseWrapper.classList.add('d-none');
        purchaseBalance.textContent = 'Balance: Rs. 0.00';

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
                paymentForm.action = `/admin/suppliers-payments/${supplierId}/store`;
            });
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
