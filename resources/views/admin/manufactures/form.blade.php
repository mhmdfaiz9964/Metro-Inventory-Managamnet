<div class="row g-3">

    {{-- Product --}}
    <div class="col-md-6">
        <label class="form-label">Product <span class="text-danger">*</span></label>
        <select name="product_id" id="product_id" class="form-select @error('product_id') is-invalid @enderror" required>
            <option value="">-- Select Product --</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" 
                    {{ old('product_id', $manufacture->product_id ?? '') == $product->id ? 'selected' : '' }}>
                    {{ $product->name }}
                </option>
            @endforeach
        </select>
        @error('product_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Assigned User --}}
    <div class="col-md-6">
        <label class="form-label">Assigned User <span class="text-danger">*</span></label>
        <select name="assigned_user_id" class="form-select @error('assigned_user_id') is-invalid @enderror" required>
            <option value="">-- Select User --</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" 
                    {{ old('assigned_user_id', $manufacture->assigned_user_id ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}
                </option>
            @endforeach
        </select>
        @error('assigned_user_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Dates --}}
    <div class="col-md-6">
        <label class="form-label">Start Date <span class="text-danger">*</span></label>
        <input type="date" name="start_date" value="{{ old('start_date', $manufacture->start_date ?? '') }}" 
               class="form-control @error('start_date') is-invalid @enderror" required>
        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
        <label class="form-label">End Date</label>
        <input type="date" name="end_date" value="{{ old('end_date', $manufacture->end_date ?? '') }}" 
               class="form-control @error('end_date') is-invalid @enderror">
        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Status --}}
    <div class="col-md-6">
        <label class="form-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach($statuses as $status)
                <option value="{{ $status }}" 
                    {{ old('status', $manufacture->status ?? '') == $status ? 'selected' : '' }}>
                    {{ ucfirst($status) }}
                </option>
            @endforeach
        </select>
        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Quantity to Produce --}}
    <div class="col-md-6">
        <label class="form-label">Quantity to Produce <span class="text-danger">*</span></label>
        <input type="number" name="quantity_to_produce" id="quantity_to_produce" 
               value="{{ old('quantity_to_produce', $manufacture->quantity_to_produce ?? 1) }}" 
               class="form-control @error('quantity_to_produce') is-invalid @enderror" required min="1">
        @error('quantity_to_produce') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- Components --}}
    <div class="col-12">
        <label class="form-label">Components</label>
        <div id="components-list" class="list-group list-group-flush">
            {{-- Dynamically loaded via AJAX --}}
            @if(isset($manufacture) && $manufacture->items)
                @foreach($manufacture->items as $index => $item)
                    <div class="list-group-item component-block px-3 py-2 border-bottom" 
                         data-bom-qty="{{ $item->bomComponent->required_bom_qty }}"
                         data-available="{{ optional($item->bomComponent->stock)->available_stock ?? 0 }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $item->bomComponent->name }}</strong> 
                                <small class="text-muted">(Price: {{ $item->bomComponent->price }})</small>
                            </div>
                            <input type="hidden" name="components[{{ $index }}][id]" value="{{ $item->bom_component_id }}">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <span class="input-group-text">Qty</span>
                                <input type="number" step="0.01" name="components[{{ $index }}][required_qty]" 
                                       value="{{ old("components.$index.required_qty", $item->required_qty) }}" 
                                       class="form-control required-qty-input @error("components.$index.required_qty") is-invalid @enderror" required>
                            </div>
                        </div>
                        @error("components.$index.required_qty")
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                @endforeach
            @endif
        </div>
    </div>

</div>
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const productSelect = document.getElementById('product_id');
    const quantityInput = document.getElementById('quantity_to_produce');
    const componentsList = document.getElementById('components-list');
    let hasShownAlert = false; // Flag to prevent multiple alerts on load

    function checkQtyAlert(block, required, available, componentName) {
        if (required > available) {
            block.querySelector('.required-qty-input').classList.add('is-invalid');
            if (!hasShownAlert) {
                hasShownAlert = true;
                Swal.fire({
                    icon: 'warning',
                    title: 'Insufficient Stock Alert',
                    text: `At least one component has insufficient stock. ${componentName}: Required ${required.toFixed(2)}, Available ${available.toFixed(2)}. Please adjust quantities.`
                });
            }
            return true;
        } else {
            block.querySelector('.required-qty-input').classList.remove('is-invalid');
            return false;
        }
    }

    function updateRequiredQtys() {
        hasShownAlert = false; // Reset flag for new calculations
        const quantity = parseFloat(quantityInput.value) || 1;
        document.querySelectorAll('.component-block').forEach(block => {
            const bomQty = parseFloat(block.dataset.bomQty) || 0;
            const available = parseFloat(block.dataset.available) || 0;
            const input = block.querySelector('.required-qty-input');
            const componentName = block.querySelector('strong').textContent.trim();
            if (input) {
                const required = bomQty * quantity;
                input.value = required.toFixed(2);
                checkQtyAlert(block, required, available, componentName);
            }
        });
    }

    function loadAvailableStocks() {
        const productId = productSelect.value;
        if (!productId) return;

        fetch(`/admin/manufactures/product/${productId}/components`)
            .then(res => res.json())
            .then(components => {
                const componentMap = {};
                components.forEach(c => {
                    componentMap[c.id] = c.available_stock;
                });

                document.querySelectorAll('.component-block').forEach(block => {
                    const hiddenId = block.querySelector('input[type="hidden"]').value;
                    const available = componentMap[hiddenId] || 0;
                    block.dataset.available = available;

                    const input = block.querySelector('.required-qty-input');
                    const required = parseFloat(input.value) || 0;
                    const componentName = block.querySelector('strong').textContent.trim();
                    checkQtyAlert(block, required, available, componentName);
                });
            })
            .catch(error => console.error('Error loading stocks:', error));
    }

    // Form submit handler
    if (form) {
        form.addEventListener('submit', function(e) {
            let hasInsufficient = false;
            let errorDetails = [];

            document.querySelectorAll('.component-block').forEach(block => {
                const required = parseFloat(block.querySelector('.required-qty-input').value) || 0;
                const available = parseFloat(block.dataset.available) || 0;
                const componentName = block.querySelector('strong').textContent.trim();

                if (required > available) {
                    hasInsufficient = true;
                    errorDetails.push(`${componentName}: Required ${required.toFixed(2)}, Available ${available.toFixed(2)}`);
                }
            });

            if (hasInsufficient) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Cannot Create Manufacture',
                    html: `At least one component has insufficient stock:<br><ul>${errorDetails.map(detail => `<li>${detail}</li>`).join('')}</ul><br>Please restock or adjust quantities before proceeding.`,
                    confirmButtonText: 'OK'
                });
            }
        });
    }

    quantityInput?.addEventListener('input', updateRequiredQtys);

    productSelect?.addEventListener('change', function() {
        const productId = this.value;
        if (!productId) {
            componentsList.innerHTML = '';
            return;
        }

        fetch(`/admin/manufactures/product/${productId}/components`)
            .then(res => res.json())
            .then(components => {
                componentsList.innerHTML = '';
                components.forEach((c, index) => {
                    const item = document.createElement('div');
                    item.className = "list-group-item component-block px-3 py-2 border-bottom";
                    item.dataset.bomQty = c.required_bom_qty;
                    item.dataset.available = c.available_stock;
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${c.name}</strong> 
                                <small class="text-muted">(Price: ${c.price})</small>
                            </div>
                            <input type="hidden" name="components[${index}][id]" value="${c.id}">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <span class="input-group-text">Qty</span>
                                <input type="number" step="0.01" name="components[${index}][required_qty]" class="form-control required-qty-input" required>
                            </div>
                        </div>
                    `;
                    componentsList.appendChild(item);
                });
                updateRequiredQtys();
            })
            .catch(error => console.error('Error loading components:', error));
    });

    // Listener for manual qty changes
    document.addEventListener('input', function(e) {
        if (e.target.matches('.required-qty-input')) {
            const block = e.target.closest('.component-block');
            const available = parseFloat(block.dataset.available) || 0;
            const required = parseFloat(e.target.value) || 0;
            const componentName = block.querySelector('strong').textContent.trim();
            checkQtyAlert(block, required, available, componentName);
        }
    });

    // Initial update for edit mode
    if (componentsList.children.length > 0) {
        loadAvailableStocks();
        updateRequiredQtys();
    }
});
</script>
@endsection