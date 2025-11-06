<div class="row g-3">

    {{-- Name --}}
    <div class="col-md-6">
        <label class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" name="name" value="{{ old('name', $product->name ?? '') }}"
            class="form-control @error('name') is-invalid @enderror">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Code --}}
    <div class="col-md-6">
        <label class="form-label">Code <span class="text-danger">*</span></label>
        <input type="text" name="code" value="{{ old('code', $product->code ?? '') }}"
            class="form-control @error('code') is-invalid @enderror">
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Category --}}
    <div class="col-md-6">
        <label class="form-label">Category <span class="text-danger">*</span></label>
        <select name="product_category_id" class="form-select @error('product_category_id') is-invalid @enderror">
            <option value="">-- Select --</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}"
                    {{ old('product_category_id', $product->product_category_id ?? '') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
        @error('product_category_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Product Brand --}}
    <div class="col-md-6">
        <label class="form-label">Brand</label>
        <select name="product_brand_id" class="form-select">
            <option value="">-- Select Brand --</option>
            @foreach ($productBrands as $brand)
                <option value="{{ $brand->id }}"
                    {{ old('product_brand_id', $product->product_brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Model --}}
    <div class="col-md-6">
        <label class="form-label">Model</label>
        <input type="text" name="model" value="{{ old('model', $product->model ?? '') }}" class="form-control">
    </div>

    {{-- Supplier --}}
    <div class="col-md-6">
        <label class="form-label">Supplier</label>
        <select name="supplier_id" class="form-select">
            <option value="">-- Select Supplier --</option>
            @foreach ($suppliers as $sup)
                <option value="{{ $sup->id }}"
                    {{ old('supplier_id', $product->supplier_id ?? '') == $sup->id ? 'selected' : '' }}>
                    {{ $sup->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="Active" {{ old('status', $product->status ?? '') == 'Active' ? 'selected' : '' }}>Active
            </option>
            <option value="Inactive" {{ old('status', $product->status ?? '') == 'Inactive' ? 'selected' : '' }}>
                Inactive</option>
        </select>
    </div>

    {{-- Regular Price --}}
    <div class="col-md-6">
        <label class="form-label">Regular Price</label>
        <input type="number" step="0.01" name="regular_price"
            value="{{ old('regular_price', $product->regular_price ?? '') }}" class="form-control">
    </div>

    {{-- Wholesale Price --}}
    <div class="col-md-6">
        <label class="form-label">Wholesale Price</label>
        <input type="number" step="0.01" name="wholesale_price"
            value="{{ old('wholesale_price', $product->wholesale_price ?? '') }}" class="form-control">
    </div>

    {{-- Sale Price --}}
    <div class="col-md-6">
        <label class="form-label">Sale Price</label>
        <input type="number" step="0.01" name="sale_price"
            value="{{ old('sale_price', $product->sale_price ?? '') }}" class="form-control">
    </div>

    {{-- Weight --}}
    <div class="col-md-6">
        <label class="form-label">Weight (kg)</label>
        <input type="number" step="0.01" name="weight" value="{{ old('weight', $product->weight ?? '') }}"
            class="form-control">
    </div>

    {{-- Warranty --}}
    <div class="col-md-6">
        <label class="form-label">Warranty</label>
        <select name="warranty" class="form-select">
            @foreach (['No warranty', '1 month', '3 months', '6 months', '12 months', '24 months'] as $w)
                <option value="{{ $w }}"
                    {{ old('warranty', $product->warranty ?? '') == $w ? 'selected' : '' }}>{{ $w }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Available Stock --}}
    <div class="col-md-6">
        <label class="form-label">Available Stock</label>
        <input type="number" name="available_stock"
            value="{{ old('available_stock', $product->stock->available_stock ?? 0) }}" class="form-control">
    </div>

    {{-- Stock Alert --}}
    <div class="col-md-6">
        <label class="form-label">Stock Alert</label>
        <input type="number" name="stock_alert" value="{{ old('stock_alert', $product->stock->stock_alert ?? 0) }}"
            class="form-control">
    </div>
    {{-- Manufactured Product --}}
    <div class="col-md-6">
        <label class="form-label">Manufactured Product <span class="text-danger">*</span></label>
        <select name="is_manufactured" class="form-select @error('is_manufactured') is-invalid @enderror">
            <option value="no"
                {{ old('is_manufactured', $product->is_manufactured ?? 'no') == 'no' ? 'selected' : '' }}>No</option>
            <option value="yes"
                {{ old('is_manufactured', $product->is_manufactured ?? 'no') == 'yes' ? 'selected' : '' }}>Yes</option>
        </select>
        @error('is_manufactured')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Description --}}
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control">{{ old('description', $product->description ?? '') }}</textarea>
    </div>

    {{-- Image --}}
    <div class="col-md-6">
        <label class="form-label">Product Image</label>
        <input type="file" name="image" id="image"
            class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="mt-2">
            <img id="preview-image" src="{{ isset($product->image) ? asset('storage/' . $product->image) : '#' }}"
                alt="Image Preview" class="img-fluid rounded shadow-sm"
                style="{{ isset($product->image) ? '' : 'display:none;' }} max-height:150px;">
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-content-between">
    <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">Back</a>
    <button type="submit" class="btn btn-primary">{{ $buttonText }}</button>
</div>

@section('scripts')
    <script>
        // Real-time image preview
        document.getElementById('image').addEventListener('change', function(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('preview-image');
                output.src = reader.result;
                output.style.display = 'block';
            };
            if (event.target.files[0]) {
                reader.readAsDataURL(event.target.files[0]);
            }
        });
    </script>
@endsection
