<div class="row g-3">
    {{-- Reason --}}
    <div class="col-md-6">
        <label class="form-label">Reason <span class="text-danger">*</span></label>
        <input type="text" name="reason" value="{{ old('reason', $cheque->reason ?? '') }}"
            class="form-control @error('reason') is-invalid @enderror">
        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Cheque No --}}
    <div class="col-md-6">
        <label class="form-label">Cheque No <span class="text-danger">*</span></label>
        <input type="text" name="cheque_no" value="{{ old('cheque_no', $cheque->cheque_no ?? '') }}"
            class="form-control @error('cheque_no') is-invalid @enderror">
        @error('cheque_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Type --}}
    <div class="col-md-6">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" class="form-select @error('type') is-invalid @enderror">
            @foreach(['supplier_payment','outsource','company_expenses','others'] as $type)
                <option value="{{ $type }}" {{ old('type', $cheque->type ?? '') == $type ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_',' ', $type)) }}
                </option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Cheque Date --}}
    <div class="col-md-6">
        <label class="form-label">Cheque Date <span class="text-danger">*</span></label>
        <input type="date" name="cheque_date"
            value="{{ old('cheque_date', isset($cheque->cheque_date) ? \Carbon\Carbon::parse($cheque->cheque_date)->format('Y-m-d') : '') }}"
            class="form-control @error('cheque_date') is-invalid @enderror">
        @error('cheque_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Cheque Bank --}}
    <div class="col-md-6">
        <label class="form-label">Bank <span class="text-danger">*</span></label>
        <select name="cheque_bank" class="form-select @error('cheque_bank') is-invalid @enderror">
            <option value="">-- Select --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}" {{ old('cheque_bank', $cheque->cheque_bank ?? '') == $bank->id ? 'selected' : '' }}>
                    {{ $bank->bank_name }}
                </option>
            @endforeach
        </select>
        @error('cheque_bank') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Amount --}}
    <div class="col-md-6">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="amount" value="{{ old('amount', $cheque->amount ?? '') }}"
            class="form-control @error('amount') is-invalid @enderror">
        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Paid To --}}
    <div class="col-md-6">
        <label class="form-label">Paid To</label>
        <input type="text" name="paid_to" value="{{ old('paid_to', $cheque->paid_to ?? '') }}"
            class="form-control @error('paid_to') is-invalid @enderror" placeholder="Enter recipient name">
        @error('paid_to') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Created By --}}
    <div class="col-md-6">
        <label class="form-label">Created By <span class="text-danger">*</span></label>
        <select name="created_by" class="form-select @error('created_by') is-invalid @enderror">
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('created_by', $cheque->created_by ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('created_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Approved By --}}
    <div class="col-md-6">
        <label class="form-label">Approved By</label>
        <select name="approved_by" class="form-select @error('approved_by') is-invalid @enderror">
            <option value="">-- Not Approved Yet --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('approved_by', $cheque->approved_by ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('approved_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            @foreach(['processing','pending','approved','rejected'] as $status)
                <option value="{{ $status }}" {{ old('status', $cheque->status ?? '') == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Note --}}
    <div class="col-md-12">
        <label class="form-label">Note</label>
        <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3">{{ old('note', $cheque->note ?? '') }}</textarea>
        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
