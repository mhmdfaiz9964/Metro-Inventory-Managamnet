<div class="row g-3">
    {{-- Component --}}
    <div class="col-md-6">
        <label class="form-label">BOM Component <span class="text-danger">*</span></label>
        <select name="bom_stock_id" class="form-select @error('bom_stock_id') is-invalid @enderror">
            <option value="">-- Select Component --</option>
            @foreach($stocks as $stock)
                <option value="{{ $stock->id }}" 
                    {{ old('bom_stock_id', $adjustment->bom_stock_id ?? '') == $stock->id ? 'selected' : '' }}>
                    {{ $stock->bomComponent->name }} ({{ $stock->bomComponent->product_code }}) 
                    - Current: {{ $stock->available_stock }}
                </option>
            @endforeach
        </select>
        @error('bom_stock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Adjustment Type --}}
    <div class="col-md-6">
        <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
        <select name="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror">
            <option value="increase" {{ old('adjustment_type', $adjustment->adjustment_type ?? '')=='increase'?'selected':'' }}>Increase</option>
            <option value="decrease" {{ old('adjustment_type', $adjustment->adjustment_type ?? '')=='decrease'?'selected':'' }}>Decrease</option>
        </select>
        @error('adjustment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Reason --}}
    <div class="col-md-6">
        <label class="form-label">Reason Type <span class="text-danger">*</span></label>
        <select name="reason_type" class="form-select @error('reason_type') is-invalid @enderror">
            <option value="damage" {{ old('reason_type', $adjustment->reason_type ?? '')=='damage'?'selected':'' }}>Damage</option>
            <option value="stock take" {{ old('reason_type', $adjustment->reason_type ?? '')=='stock take'?'selected':'' }}>Stock Take</option>
            <option value="correction" {{ old('reason_type', $adjustment->reason_type ?? '')=='correction'?'selected':'' }}>Correction</option>
        </select>
        @error('reason_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Quantity --}}
    <div class="col-md-6">
        <label class="form-label">Quantity <span class="text-danger">*</span></label>
        <input type="number" name="quantity" min="1" 
            value="{{ old('quantity', $adjustment->quantity ?? '') }}" 
            class="form-control @error('quantity') is-invalid @enderror">
        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>

<div class="mt-4 d-flex justify-content-between">
    <a href="{{ route('admin.bom-stock-adjustments.index') }}" class="btn btn-outline-secondary">Back</a>
    <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
</div>
