<div class="row g-3">
    {{-- Bank --}}
    <div class="col-md-6">
        <label class="form-label">Bank <span class="text-danger">*</span></label>
        <select name="bank_name" id="bank_name" class="form-select @error('bank_name') is-invalid @enderror">
            <option value="">-- Select Bank --</option>
            @foreach($banks as $bank)
                <option value="{{ $bank }}"
                    {{ old('bank_name', $bankAccount->bank_name ?? '') == $bank ? 'selected' : '' }}>
                    {{ $bank }}
                </option>
            @endforeach
        </select>
        @error('bank_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Branch --}}
    <div class="col-md-6">
        <label class="form-label">Branch <span class="text-danger">*</span></label>
        <select name="branch_name" id="branch_name" class="form-select @error('branch_name') is-invalid @enderror">
            <option value="">-- Select Branch --</option>
            @if(old('bank_name', $bankAccount->bank_name ?? ''))
                @php
                    $bank = \App\Models\Bank::where('name', old('bank_name', $bankAccount->bank_name ?? ''))->first();
                @endphp
                @if($bank)
                    @foreach(\App\Models\Branch::where('bank_id', $bank->id)->pluck('name') as $branch)
                        <option value="{{ $branch }}"
                            {{ old('branch_name', $bankAccount->branch_name ?? '') == $branch ? 'selected' : '' }}>
                            {{ $branch }}
                        </option>
                    @endforeach
                @endif
            @endif
        </select>
        @error('branch_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Owner Name --}}
    <div class="col-md-6">
        <label class="form-label">Owner Name <span class="text-danger">*</span></label>
        <input type="text" name="owner_name"
               value="{{ old('owner_name', $bankAccount->owner_name ?? '') }}"
               class="form-control @error('owner_name') is-invalid @enderror"
               placeholder="Enter owner name">
        @error('owner_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Account Number --}}
    <div class="col-md-6">
        <label class="form-label">Account Number <span class="text-danger">*</span></label>
        <input type="text" name="account_number"
               value="{{ old('account_number', $bankAccount->account_number ?? '') }}"
               class="form-control @error('account_number') is-invalid @enderror"
               placeholder="Enter account number">
        @error('account_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Bank Balance --}}
    <div class="col-md-6">
        <label class="form-label">Bank Balance <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="bank_balance"
               value="{{ old('bank_balance', $bankAccount->bank_balance ?? '') }}"
               class="form-control @error('bank_balance') is-invalid @enderror"
               placeholder="Enter bank balance">
        @error('bank_balance') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="active" {{ old('status', $bankAccount->status ?? '')=='active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $bankAccount->status ?? '')=='inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.getElementById('bank_name').addEventListener('change', function() {
        var bankName = this.value;
        var branchSelect = document.getElementById('branch_name');
        branchSelect.innerHTML = '<option>Loading...</option>';

        if(bankName) {
            axios.get('{{ route("admin.bank-accounts.branches") }}', { params: { bank_name: bankName } })
                .then(function(response) {
                    var branches = response.data;
                    var options = '<option value="">-- Select Branch --</option>';
                    branches.forEach(function(branch) {
                        options += '<option value="'+branch+'">'+branch+'</option>';
                    });
                    branchSelect.innerHTML = options;
                })
                .catch(function(error) {
                    branchSelect.innerHTML = '<option value="">-- Error Loading --</option>';
                });
        } else {
            branchSelect.innerHTML = '<option value="">-- Select Branch --</option>';
        }
    });
</script>
@endsection
