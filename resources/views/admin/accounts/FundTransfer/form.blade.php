<div class="row g-3">
    {{-- Reason --}}
    <div class="col-md-6">
        <label class="form-label">Reason <span class="text-danger">*</span></label>
        <input type="text" name="reason" value="{{ old('reason', $fundTransfer->reason ?? '') }}" class="form-control @error('reason') is-invalid @enderror">
        @error('reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- From Bank --}}
    <div class="col-md-6">
        <label class="form-label">From Bank <span class="text-danger">*</span></label>
        <select name="bank_id" class="form-select @error('bank_id') is-invalid @enderror">
            <option value="">-- Select --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}" {{ old('bank_id', $fundTransfer->bank_id ?? '') == $bank->id ? 'selected' : '' }}>
                    {{ $bank->bank_name }}
                </option>
            @endforeach
        </select>
        @error('bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- To Bank --}}
    <div class="col-md-6" id="toBankWrapper" style="display: none;">
        <label class="form-label">To Bank <span class="text-danger" id="toBankRequired" style="display:none;">*</span></label>
        <select name="to_bank_id" id="to_bank_id" class="form-select @error('to_bank_id') is-invalid @enderror">
            <option value="">-- Select --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}" {{ old('to_bank_id', $fundTransfer->to_bank_id ?? '') == $bank->id ? 'selected' : '' }}>
                    {{ $bank->bank_name }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Required only if type = Bank to Bank</small>
        @error('to_bank_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Type --}}
    <div class="col-md-6">
        <label class="form-label">Type <span class="text-danger">*</span></label>
        <select name="type" id="transfer_type" class="form-select @error('type') is-invalid @enderror">
            @foreach(['bank_to_bank','outsource_payment','employee_payment','sales_payment'] as $type)
                <option value="{{ $type }}" {{ old('type', $fundTransfer->type ?? '') == $type ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_',' ', $type)) }}
                </option>
            @endforeach
        </select>
        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Transfer Date --}}
    <div class="col-md-6">
        <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
        <input type="date" name="transfer_date" value="{{ old('transfer_date', isset($fundTransfer->transfer_date) ? $fundTransfer->transfer_date->format('Y-m-d') : '') }}" class="form-control @error('transfer_date') is-invalid @enderror">
        @error('transfer_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Amount --}}
    <div class="col-md-6">
        <label class="form-label">Amount <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="amount" value="{{ old('amount', $fundTransfer->amount ?? '') }}" class="form-control @error('amount') is-invalid @enderror">
        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Transferred By --}}
    <div class="col-md-6">
        <label class="form-label">Transferred By <span class="text-danger">*</span></label>
        <select name="transferred_by" class="form-select @error('transferred_by') is-invalid @enderror">
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('transferred_by', $fundTransfer->transferred_by ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('transferred_by') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Approved By --}}
    <div class="col-md-6">
        <label class="form-label">Approved By</label>
        <select name="approved_by" class="form-select @error('approved_by') is-invalid @enderror">
            <option value="">-- Not Approved Yet --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" {{ old('approved_by', $fundTransfer->approved_by ?? '') == $user->id ? 'selected' : '' }}>
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
            @foreach(['pending','processing','pending_for_approval','completed'] as $status)
                <option value="{{ $status }}" {{ old('status', $fundTransfer->status ?? '') == $status ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_',' ', $status)) }}
                </option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Note --}}
    <div class="col-md-12">
        <label class="form-label">Note</label>
        <textarea name="note" class="form-control @error('note') is-invalid @enderror" rows="3">{{ old('note', $fundTransfer->note ?? '') }}</textarea>
        @error('note') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

{{-- JS for real-time toggle --}}
@section('scripts')
<script>
    function toggleToBank() {
        const type = document.getElementById('transfer_type').value;
        const wrapper = document.getElementById('toBankWrapper');
        const requiredStar = document.getElementById('toBankRequired');
        const toBankSelect = document.getElementById('to_bank_id');

        if (type === 'bank_to_bank') {
            wrapper.style.display = 'block';
            requiredStar.style.display = 'inline';
        } else {
            wrapper.style.display = 'none';
            requiredStar.style.display = 'none';
            toBankSelect.value = ""; // reset if hidden
        }
    }

    document.addEventListener("DOMContentLoaded", toggleToBank);
    document.getElementById('transfer_type').addEventListener("change", toggleToBank);
</script>
@endsection
