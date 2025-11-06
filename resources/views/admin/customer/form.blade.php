@csrf
<div class="row g-3">
    <!-- Name -->
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" 
               name="name" 
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $customer->name ?? '') }}" 
               placeholder="Enter customer name">
        @error('name') 
            <div class="invalid-feedback">{{ $message }}</div> 
        @enderror
    </div>

    <!-- Mobile Number -->
    <div class="col-md-6">
        <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
        <input type="text" 
               name="mobile_number" 
               class="form-control @error('mobile_number') is-invalid @enderror"
               value="{{ old('mobile_number', $customer->mobile_number ?? '') }}" 
               placeholder="Enter mobile number">
        @error('mobile_number') 
            <div class="invalid-feedback">{{ $message }}</div> 
        @enderror
    </div>

    <!-- Mobile Number 2 -->
    <div class="col-md-6">
        <label class="form-label">Mobile Number 2</label>
        <input type="text" 
               name="mobile_number_2" 
               class="form-control @error('mobile_number_2') is-invalid @enderror"
               value="{{ old('mobile_number_2', $customer->mobile_number_2 ?? '') }}" 
               placeholder="Enter second mobile number (optional)">
        @error('mobile_number_2') 
            <div class="invalid-feedback">{{ $message }}</div> 
        @enderror
    </div>

    <!-- Email -->
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" 
               name="email" 
               class="form-control @error('email') is-invalid @enderror"
               value="{{ old('email', $customer->email ?? '') }}" 
               placeholder="Enter email (optional)">
        @error('email') 
            <div class="invalid-feedback">{{ $message }}</div> 
        @enderror
    </div>

    <!-- Credit Limit -->
    <div class="col-md-6">
        <label class="form-label">Credit Limit</label>
        <div class="input-group">
            <span class="input-group-text">LKR</span>
            <input type="number" 
                   id="credit_limit"
                   name="credit_limit" 
                   step="0.01" 
                   min="0"
                   class="form-control @error('credit_limit') is-invalid @enderror"
                   value="{{ old('credit_limit', $customer->credit_limit ?? 0) }}" 
                   placeholder="Enter credit limit amount">
        </div>
        @error('credit_limit') 
            <div class="invalid-feedback d-block">{{ $message }}</div> 
        @enderror
    </div>

    <!-- Note -->
    <div class="col-md-12">
        <label class="form-label">Note</label>
        <textarea name="note" 
                  class="form-control @error('note') is-invalid @enderror" 
                  placeholder="Add note (optional)">{{ old('note', $customer->note ?? '') }}</textarea>
        @error('note') 
            <div class="invalid-feedback">{{ $message }}</div> 
        @enderror
    </div>
</div>

<!-- Add script below -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const creditInput = document.getElementById('credit_limit');

    // Clear "0" when focused
    creditInput.addEventListener('focus', function () {
        if (this.value === '0' || this.value === '0.00') {
            this.value = '';
        }
    });

    // Restore "0" if empty on blur
    creditInput.addEventListener('blur', function () {
        if (this.value.trim() === '') {
            this.value = '0';
        } else {
            // Add leading zero if single-digit
            const num = parseFloat(this.value);
            if (!isNaN(num) && num < 10 && num >= 0) {
                this.value = num.toFixed(0).padStart(2, '0');
            }
        }
    });
});
</script>
@endpush
