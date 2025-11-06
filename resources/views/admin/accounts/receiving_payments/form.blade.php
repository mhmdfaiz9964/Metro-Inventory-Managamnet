<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Reason</label>
        <input type="text" name="reason" value="{{ old('reason', $receivingPayment->reason ?? '') }}" class="form-control @error('reason') is-invalid @enderror">
        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Paid Date</label>
        <input type="date" name="paid_date" value="{{ old('paid_date', $receivingPayment->paid_date ?? '') }}" class="form-control @error('paid_date') is-invalid @enderror">
        @error('paid_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Paid By</label>
        <input type="text" name="paid_by" value="{{ old('paid_by', $receivingPayment->paid_by ?? '') }}" class="form-control @error('paid_by') is-invalid @enderror">
        @error('paid_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="paid" {{ old('status', $receivingPayment->status ?? '')=='paid'?'selected':'' }}>Paid</option>
            <option value="pending" {{ old('status', $receivingPayment->status ?? '')=='pending'?'selected':'' }}>Pending</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Bank</label>
        <select name="bank_id" class="form-select @error('bank_id') is-invalid @enderror">
            <option value="">-- Select Bank --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}" {{ old('bank_id', $receivingPayment->bank_id ?? '')==$bank->id?'selected':'' }}>
                    {{ $bank->bank_name }} ({{ $bank->account_number }})
                </option>
            @endforeach
        </select>
        @error('bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label">Amount</label>
        <input type="number" step="0.01" name="amount" value="{{ old('amount', $receivingPayment->amount ?? '') }}" class="form-control @error('amount') is-invalid @enderror">
        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4">
    <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-secondary">Back</a>
</div>
