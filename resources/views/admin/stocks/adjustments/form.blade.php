<div class="row g-3">

    {{-- Stock / Product --}}
    <div class="col-md-6">
        <label class="form-label">Product / Stock <span class="text-danger">*</span></label>
        <select name="stock_id" class="form-select @error('stock_id') is-invalid @enderror">
            <option value="">-- Select Stock --</option>
            @foreach($stocks as $stock)
                <option value="{{ $stock->id }}" 
                    {{ old('stock_id', $stockAdjustment->stock_id ?? '') == $stock->id ? 'selected' : '' }}>
                    {{ $stock->product->name ?? 'N/A' }} ({{ $stock->product->code ?? 'N/A' }}) - Available: {{ $stock->available_stock }}
                </option>
            @endforeach
        </select>
        @error('stock_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Adjustment Type --}}
    <div class="col-md-6">
        <label class="form-label">Adjustment Type <span class="text-danger">*</span></label>
        <select name="adjustment_type" class="form-select @error('adjustment_type') is-invalid @enderror">
            @foreach(['increase', 'decrease'] as $type)
                <option value="{{ $type }}" {{ old('adjustment_type', $stockAdjustment->adjustment_type ?? '')==$type?'selected':'' }}>
                    {{ ucfirst($type) }}
                </option>
            @endforeach
        </select>
        @error('adjustment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Quantity --}}
    <div class="col-md-6">
        <label class="form-label">Quantity <span class="text-danger">*</span></label>
        <input type="number" name="quantity" step="1" min="1" value="{{ old('quantity', $stockAdjustment->quantity ?? '') }}" class="form-control @error('quantity') is-invalid @enderror">
        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Reason Type --}}
    <div class="col-md-6">
        <label class="form-label">Reason Type <span class="text-danger">*</span></label>
        <select name="reason_type" class="form-select @error('reason_type') is-invalid @enderror">
            @foreach(['damage','stock take','correction'] as $reason)
                <option value="{{ $reason }}" {{ old('reason_type', $stockAdjustment->reason_type ?? '')==$reason?'selected':'' }}>
                    {{ ucfirst($reason) }}
                </option>
            @endforeach
        </select>
        @error('reason_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>

<div class="mt-4 d-flex justify-content-between">
    <a href="javascript:void(0)" onclick="window.history.back()"
        class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle me-1"></i> Back
     </a>
    <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
</div>
