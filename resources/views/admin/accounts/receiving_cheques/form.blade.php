{{-- Global Errors Display --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validation Errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="row g-3">
    <div class="col-md-6">
        <label for="cheque_no" class="form-label">Cheque Number</label>
        <input type="text" name="cheque_no" id="cheque_no" class="form-control"
            value="{{ old('cheque_no', $cheque->cheque_no ?? '') }}" required>
    </div>

    <div class="col-md-6 mb-3">
        <label for="bank_id" class="form-label">Bank</label>
        <select name="bank_id" id="bank_id" class="form-control select2" required>
            <option value="">-- Select Bank --</option>
            @foreach ($banks as $bank)
                <option value="{{ $bank->id }}"
                    {{ old('bank_id', $cheque->bank_id ?? '') == $bank->id ? 'selected' : '' }}>
                    {{ $bank->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="paid_by" class="form-label">Customer Name</label>
        <input type="text" name="paid_by" id="paid_by" class="form-control"
            value="{{ old('paid_by', $cheque->paid_by ?? '') }}">
    </div>

    <div class="col-md-6">
        <label for="cheque_type" class="form-label">Cheque Type</label>
        <select name="cheque_type" id="cheque_type" class="form-select" required>
            <option value="">-- Select Type --</option>
            <option value="Crossed Cheque" {{ old('cheque_type', $cheque->cheque_type ?? '') == 'Crossed Cheque' ? 'selected' : '' }}>Crossed Cheque</option>
            <option value="Cash Cheque" {{ old('cheque_type', $cheque->cheque_type ?? '') == 'Cash Cheque' ? 'selected' : '' }}>Cash Cheque</option>
        </select>
    </div>

    <div class="col-md-6">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select" required>
            <option value="pending" {{ old('status', $cheque->status ?? '') == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="paid" {{ old('status', $cheque->status ?? '') == 'paid' ? 'selected' : '' }}>Paid</option>
        </select>
    </div>

    <div class="col-md-6">
        <label for="paid_date" class="form-label">Paid Date</label>
        <input type="date" name="paid_date" id="paid_date" class="form-control"
            value="{{ old('paid_date', $cheque->paid_date ?? '') }}">
    </div>

    <div class="col-md-6">
        <label for="cheque_date" class="form-label">Cheque Date</label>
        <input type="date" name="cheque_date" id="cheque_date" class="form-control"
            value="{{ old('cheque_date', $cheque->cheque_date ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="amount" class="form-label">Amount</label>
        <input type="number" step="0.01" name="amount" id="amount" class="form-control"
            value="{{ old('amount', $cheque->amount ?? '') }}" required>
    </div>

    <div class="col-md-6">
        <label for="reason" class="form-label">Reason</label>
        <input type="text" name="reason" id="reason" class="form-control"
            value="{{ old('reason', $cheque->reason ?? '') }}" required>
    </div>
</div>
