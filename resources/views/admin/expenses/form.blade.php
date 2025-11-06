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
@csrf
<div class="row g-3">
    {{-- Reason --}}
    <div class="col-md-12">
        <label class="form-label">Reason <span class="text-danger">*</span></label>
        <textarea name="reason" class="form-control @error('reason') is-invalid @enderror"
                  placeholder="Enter reason">{{ old('reason', $expense->reason ?? '') }}</textarea>
        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Date --}}
    <div class="col-md-6">
        <label class="form-label">Date <span class="text-danger">*</span></label>
        <input type="date" name="date" value="{{ old('date', $expense->date ?? '') }}"
               class="form-control @error('date') is-invalid @enderror">
        @error('date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Payment Method --}}
    <div class="col-md-6">
        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
        <select name="payment_method" id="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
            <option value="Cash" {{ old('payment_method', $expense->payment_method ?? '') == 'Cash' ? 'selected' : '' }}>Cash</option>
            <option value="Fund Transfer" {{ old('payment_method', $expense->payment_method ?? '') == 'Fund Transfer' ? 'selected' : '' }}>Fund Transfer</option>
            <option value="Cheque" {{ old('payment_method', $expense->payment_method ?? '') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
        </select>
        @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Bank Account --}}
    <div class="col-md-6 bank-field" style="display:none;">
        <label class="form-label">Bank Account <span class="text-danger">*</span></label>
        <select name="bank_account_id" class="form-select @error('bank_account_id') is-invalid @enderror">
            <option value="">-- Select Bank Account --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}"
                    {{ old('bank_account_id', $expense->bank_account_id ?? '') == $bank->id ? 'selected' : '' }}>
                    {{ $bank->bank_name }} - {{ $bank->account_number }}
                </option>
            @endforeach
        </select>
        @error('bank_account_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Amount --}}
    <div class="col-md-6">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="amount"
               value="{{ old('amount', $expense->amount ?? '') }}"
               class="form-control @error('amount') is-invalid @enderror" placeholder="Enter amount">
        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Paid By --}}
    <div class="col-md-6">
        <label class="form-label">Paid By <span class="text-danger">*</span></label>
        <input type="text" name="paid_by" value="{{ old('paid_by', $expense->paid_by ?? '') }}"
               class="form-control @error('paid_by') is-invalid @enderror" placeholder="Paid by">
        @error('paid_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Cheque Fields --}}
    <div class="col-md-6 cheque-fields" style="display:none;">
        <label class="form-label">Cheque Number <span class="text-danger">*</span></label>
        <input type="text" name="cheque_no" value="{{ old('cheque_no', $expense->cheque_no ?? '') }}"
               class="form-control @error('cheque_no') is-invalid @enderror" placeholder="Enter cheque number">
        @error('cheque_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6 cheque-fields" style="display:none;">
        <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
        <input type="date" name="cheque_date" value="{{ old('cheque_date', $expense->cheque_date ?? '') }}"
               class="form-control @error('cheque_date') is-invalid @enderror">
        @error('cheque_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const paymentMethod = document.getElementById('payment_method');
    const bankField = document.querySelector('.bank-field');
    const chequeFields = document.querySelectorAll('.cheque-fields');

    function toggleFields() {
        if (paymentMethod.value === 'Cash') {
            bankField.style.display = 'none';
            chequeFields.forEach(f => f.style.display = 'none');
        } else if (paymentMethod.value === 'Fund Transfer') {
            bankField.style.display = 'block';
            chequeFields.forEach(f => f.style.display = 'none');
        } else if (paymentMethod.value === 'Cheque') {
            bankField.style.display = 'block';
            chequeFields.forEach(f => f.style.display = 'block');
        }
    }

    paymentMethod.addEventListener('change', toggleFields);
    toggleFields(); // initial load
});
</script>
