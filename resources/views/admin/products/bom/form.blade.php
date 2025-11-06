@if ($errors->any())
<div class="alert alert-danger g-3">
    <h5><i class="bi bi-exclamation-triangle-fill"></i> Please fix the following errors:</h5>
    <ul class="mb-0">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-3">

    {{-- Category --}}
    <div class="col-md-6 mb-4">
        <label class="form-label">Product Category</label>
        <select name="category_id" id="category_id" class="form-select">
            <option value="">-- Select Category --</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    {{ isset($selectedCategoryId) && $selectedCategoryId == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Product --}}
    <div class="col-md-6 mb-4">
        <label class="form-label">Assembled Product <span class="text-danger">*</span></label>
        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
            <option value="">-- Select Product --</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}"
                    {{ old('product_id', $bomProduct?->id) == $product->id ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
        @error('product_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>

{{-- Components Section --}}
<div class="col-12 mt-4">
    <h5 class="mb-3">Components</h5>
    <div id="components-wrapper">
        @php
            $components = old('components', $boms->map(fn($b) => $b->toArray())->toArray());
        @endphp

        @if(!empty($components))
            @foreach ($components as $index => $component)
                <div class="component-row position-relative border p-3 rounded shadow-sm mb-3">
                    <input type="hidden" name="components[{{ $index }}][id]" value="{{ $component['id'] ?? '' }}">
                    <button type="button"
                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-component"
                        style="padding: 0 6px; font-size: 0.8rem;">×</button>

                    <div class="row g-2">
                        <div class="col-12 col-md-2">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="components[{{ $index }}][product_code]" class="form-control"
                                value="{{ $component['product_code'] ?? '' }}">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="components[{{ $index }}][name]" class="form-control"
                                required value="{{ $component['name'] ?? '' }}">
                        </div>

                        <div class="col-12 col-md-2">
                            <label class="form-label">Brand</label>
                            <select name="components[{{ $index }}][brand_id]" class="form-select">
                                <option value="">-- Select Brand --</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}"
                                        {{ isset($component['brand_id']) && $component['brand_id'] == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-2">
                            <label class="form-label">Model</label>
                            <input type="text" name="components[{{ $index }}][model]" class="form-control"
                                value="{{ $component['model'] ?? '' }}">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label">Required BOM Quantity <span class="text-danger">*</span></label>
                            <input type="number" step="0.01"
                                name="components[{{ $index }}][required_bom_qty]" class="form-control" required
                                value="{{ $component['required_bom_qty'] ?? '' }}">
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <p class="text-muted">No components added yet. Select a product to load existing components or add new ones.</p>
        @endif
    </div>

    <button type="button" id="add-component" class="btn btn-outline-primary btn-sm mt-3">Add Row</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('components-wrapper');
    const addBtn = document.getElementById('add-component');
    let componentIndex = wrapper.children.length;

    function createComponentRow(comp = null, index = null) {
        if (index === null) {
            index = componentIndex++;
        }
        const div = document.createElement('div');
        div.classList.add('component-row', 'position-relative', 'border', 'p-3', 'rounded', 'shadow-sm', 'mb-3');

        let brandOptions = '';
        @foreach ($brands as $brand)
            brandOptions += `<option value="{{ $brand->id }}">{{ $brand->name }}</option>`;
        @endforeach

        const idHidden = comp && comp.id ? `<input type="hidden" name="components[${index}][id]" value="${comp.id}">` : '';
        const productCodeVal = comp ? comp.product_code || '' : '';
        const nameVal = comp ? comp.name || '' : '';
        const brandIdVal = comp ? comp.brand_id || '' : '';
        const modelVal = comp ? comp.model || '' : '';
        const qtyVal = comp ? comp.required_bom_qty || '' : '';

        div.innerHTML = `
            ${idHidden}
            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 remove-component" style="padding: 0 6px; font-size: 0.8rem;">×</button>

            <div class="row g-2">
                <div class="col-12 col-md-2">
                    <label class="form-label">Product Code</label>
                    <input type="text" name="components[${index}][product_code]" class="form-control" value="${productCodeVal}">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="components[${index}][name]" class="form-control" required value="${nameVal}">
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label">Brand</label>
                    <select name="components[${index}][brand_id]" class="form-select">
                        <option value="">-- Select Brand --</option>
                        ${brandOptions}
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label">Model</label>
                    <input type="text" name="components[${index}][model]" class="form-control" value="${modelVal}">
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label">Required BOM Quantity <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="components[${index}][required_bom_qty]" class="form-control" required value="${qtyVal}">
                </div>
            </div>
        `;

        // Set brand select value if exists
        if (brandIdVal) {
            const brandSelect = div.querySelector(`select[name="components[${index}][brand_id]"]`);
            if (brandSelect) {
                brandSelect.value = brandIdVal;
            }
        }

        // Attach remove listener
        const removeBtn = div.querySelector('.remove-component');
        removeBtn.addEventListener('click', () => removeRow(removeBtn));

        return div;
    }

    function removeRow(btn) {
        if (confirm('Are you sure you want to remove this component?')) {
            btn.closest('.component-row').remove();
        }
    }

    // Remove existing rows listeners
    wrapper.querySelectorAll('.remove-component').forEach(btn => {
        btn.addEventListener('click', () => removeRow(btn));
    });

    // Add new row
    addBtn.addEventListener('click', function() {
        const newRow = createComponentRow();
        wrapper.appendChild(newRow);
    });

    // AJAX for category-product filtering
    const categorySelect = document.getElementById('category_id');
    const productSelect = document.getElementById('product_id');

    categorySelect.addEventListener('change', function() {
        const categoryId = this.value;
        productSelect.innerHTML = '<option value="">-- Select Product --</option>';

        if (categoryId) {
            fetch(`/admin/products-by-category/${categoryId}`)
                .then(response => response.json())
                .then(products => {
                    products.forEach(product => {
                        const option = document.createElement('option');
                        option.value = product.id;
                        option.textContent = product.name;
                        productSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error fetching products:', error));
        }
    });

    // Load existing BOM components on product select
    productSelect.addEventListener('change', function() {
        const productId = this.value;
        if (!productId) {
            wrapper.innerHTML = '<p class="text-muted">No components added yet. Select a product to load existing components or add new ones.</p>';
            componentIndex = 0;
            return;
        }

        fetch(`/api/bom-components?product_id=${productId}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.data.length > 0) {
                    wrapper.innerHTML = '';
                    data.data.forEach((comp, idx) => {
                        const row = createComponentRow(comp, idx);
                        wrapper.appendChild(row);
                    });
                    componentIndex = data.data.length;
                } else {
                    wrapper.innerHTML = '<p class="text-muted">No existing components for this product. Add new ones below.</p>';
                    componentIndex = 0;
                }
            })
            .catch(error => {
                console.error('Error fetching BOM components:', error);
                wrapper.innerHTML = '<p class="text-danger">Error loading components. Please try again.</p>';
            });
    });
});
</script>