@php
    $purchaseReturn = $purchaseReturn ?? null;
    $oldItems = old('items') ?: ($purchaseReturn ? $purchaseReturn->items->map(function($item){
        return [
            'bom_component_id' => $item->bom_component_id,
            'quantity' => $item->quantity
        ];
    })->toArray() : []);
@endphp
<form action="{{ $action }}" method="POST">
    @csrf
    @if($method ?? null)
        @method($method)
    @endif

    {{-- Purchase Select --}}
    <div class="mb-3">
        <label class="form-label">Purchase</label>
        <select name="purchase_id" id="purchaseSelect" class="form-select" required>
            <option value="">Select Purchase</option>
            @foreach($purchases as $purchase)
                <option value="{{ $purchase->id }}"
                    {{ old('purchase_id', $purchaseReturn->purchase_id ?? '') == $purchase->id ? 'selected' : '' }}>
                    #{{ $purchase->id }} | {{ optional($purchase->supplier)->name ?? '-' }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Return Date --}}
    <div class="mb-3">
        <label class="form-label">Return Date</label>
        <input type="date" name="return_date" class="form-control"
               value="{{ old('return_date', isset($purchaseReturn->return_date) ? $purchaseReturn->return_date->format('Y-m-d') : date('Y-m-d')) }}"
               required>
    </div>

    {{-- Reason --}}
    <div class="mb-3">
        <label class="form-label">Reason</label>
        <textarea name="reason" class="form-control">{{ old('reason', $purchaseReturn->reason ?? '') }}</textarea>
    </div>

    <hr>
    <h5>Items</h5>
    <div id="itemsContainer">
        @if(count($oldItems) > 0)
            @foreach($oldItems as $index => $item)
                <div class="row mb-2 item-row align-items-center">
                    <div class="col-md-6 mb-2 mb-md-0">
                        <select name="items[{{ $index }}][bom_component_id]" class="form-select bom-select" required>
                            <option value="">Select BOM Component</option>
                            @foreach($allBoms as $bom)
                                <option value="{{ $bom->id }}" {{ $item['bom_component_id'] == $bom->id ? 'selected' : '' }}>{{ $bom->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <input type="number" name="items[{{ $index }}][quantity]" class="form-control"
                               placeholder="Quantity" min="0" value="{{ $item['quantity'] }}" required>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="mb-3">
        <button type="button" class="btn btn-secondary" id="addItem">Add Item</button>
    </div>

    <div class="mt-4 d-flex justify-content-between">
        <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i>Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i>{{ $buttonText ?? 'Save Return' }}
        </button>
    </div>
</form>

{{-- Purchases Items Data --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    let purchasesItems = @json($purchases->mapWithKeys(function($purchase){
        return [$purchase->id => $purchase->items->map(function($item){
            return [
                'id' => $item->bomComponent->id ?? null,
                'name' => $item->bomComponent->name ?? '',
                'quantity' => $item->qty
            ];
        })];
    }));

    let allBoms = @json($allBoms->map(function($bom){
        return [
            'id' => $bom->id,
            'name' => $bom->name
        ];
    }));

    let bomOptions = '<option value="">Select BOM Component</option>';
    allBoms.forEach(function(bom) {
        bomOptions += `<option value="${bom.id}">${bom.name}</option>`;
    });

    let itemIndex = {{ count($oldItems) }};

    function addItemRow(bomId = '', quantity = 0) {
        let html = `<div class="row mb-2 item-row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
                <select name="items[${itemIndex}][bom_component_id]" class="form-select bom-select" required>
                    ${bomOptions}
                </select>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="${quantity}" min="0" required>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-item">Remove</button>
            </div>
        </div>`;
        $('#itemsContainer').append(html);
        let select = $(`select[name="items[${itemIndex}][bom_component_id]"]`);
        if (bomId) {
            select.val(bomId);
        }
        itemIndex++;
    }

    function populateItems(purchaseId) {
        $('#itemsContainer').empty();
        itemIndex = 0;
        if (purchasesItems[purchaseId]) {
            purchasesItems[purchaseId].forEach(function(item) {
                addItemRow(item.id, item.quantity);
            });
        }
    }

    $(document).ready(function(){
        let selectedPurchase = $('#purchaseSelect').val();
        let oldItemsCount = {{ count($oldItems) }};
        if (selectedPurchase && oldItemsCount === 0) {
            populateItems(selectedPurchase);
        }

        $('#purchaseSelect').on('change', function(){
            let purchaseId = $(this).val();
            populateItems(purchaseId);
        });

        $('#addItem').click(function(){
            addItemRow();
        });

        $(document).on('click', '.remove-item', function(){
            $(this).closest('.item-row').remove();
        });
    });
</script>
