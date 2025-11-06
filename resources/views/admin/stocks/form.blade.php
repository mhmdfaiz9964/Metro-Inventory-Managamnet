<div class="row g-3">

    {{-- Product --}}
    <div class="col-md-6">
        <label class="form-label">Product <span class="text-danger">*</span></label>
        <select name="product_id" class="form-select @error('product_id') is-invalid @enderror">
            <option value="">-- Select Product --</option>
            @foreach($products as $prod)
                <option value="{{ $prod->id }}" {{ old('product_id', $stock->product_id ?? '')==$prod->id?'selected':'' }}>
                    {{ $prod->name }} ({{ $prod->code }})
                </option>
            @endforeach
        </select>
        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Available Stock --}}
    <div class="col-md-6">
        <label class="form-label">Available Stock <span class="text-danger">*</span></label>
        <input type="number" name="available_stock" step="1" value="{{ old('available_stock', $stock->available_stock ?? '') }}" class="form-control @error('available_stock') is-invalid @enderror">
        @error('available_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Stock Alert --}}
    <div class="col-md-6">
        <label class="form-label">Stock Alert <span class="text-danger">*</span></label>
        <input type="number" name="stock_alert" step="1" value="{{ old('stock_alert', $stock->stock_alert ?? '') }}" class="form-control @error('stock_alert') is-invalid @enderror">
        @error('stock_alert') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Notes --}}
    <div class="col-12">
        <label class="form-label">Notes</label>
        <textarea name="notes" class="form-control">{{ old('notes', $stock->notes ?? '') }}</textarea>
    </div>
</div>

<div class="mt-4 d-flex justify-content-between">
    <a href="{{ route('admin.stocks.index') }}" class="btn btn-outline-secondary">Back</a>
    <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
</div>
