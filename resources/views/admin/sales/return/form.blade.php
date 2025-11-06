@php
    $saleReturn = $saleReturn ?? null;

    $oldItems = old('items') ?: ($saleReturn ? $saleReturn->items->map(function($item){
        return [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity
        ];
    })->toArray() : []);
@endphp
{{-- Global Errors Display --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Validation Errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @if($method ?? null)
        @method($method)
    @endif

    {{-- Sale Select --}}
    <div class="mb-3">
        <label class="form-label">Sale</label>
        <select name="sale_id" id="saleSelect" class="form-select" required>
            <option value="">Select Sale</option>
            @foreach($sales as $sale)
                <option value="{{ $sale->id }}"
                    {{ old('sale_id', $saleReturn->sale_id ?? '') == $sale->id ? 'selected' : '' }}>
                    #{{ $sale->id }} | {{ optional($sale->customer)->name ?? '-' }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Return Date --}}
    <div class="mb-3">
        <label class="form-label">Return Date</label>
        <input type="date" name="return_date" class="form-control"
               value="{{ old('return_date', $saleReturn?->return_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
    </div>


    {{-- Reason --}}
    <div class="mb-3">
        <label class="form-label">Reason</label>
        <textarea name="reason" class="form-control">{{ old('reason', $saleReturn->reason ?? '') }}</textarea>
    </div>

    <hr>
    <h5>Items</h5>
    <div id="itemsContainer"></div>

    <div class="mb-3">
        <button type="button" class="btn btn-secondary" id="addItem">Add Item</button>
    </div>

    <div class="mt-4 d-flex justify-content-between">
        <a href="javascript:void(0)" onclick="window.history.back()" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left-circle me-1"></i>Back
        </a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle me-1"></i>Save Return
        </button>
    </div>
</form>

{{-- Sales Items Data --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    let salesItems = @json($sales->mapWithKeys(function($sale){
        return [$sale->id => $sale->items->map(function($item){
            return [
                'id' => $item->product->id ?? null,
                'name' => $item->product->name ?? '',
                'quantity' => $item->quantity
            ];
        })->toArray()];
    }));

    let itemIndex = {{ count($oldItems) }};

    function addItemRow(productId = '', productName = '', quantity = 1) {
        let html = `<div class="row mb-2 item-row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
                <input type="text" class="form-control" value="${productName}" readonly>
                <input type="hidden" name="items[${itemIndex}][product_id]" value="${productId}">
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" value="${quantity}" min="0" required>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" class="btn btn-danger remove-item">Remove</button>
            </div>
        </div>`;
        $('#itemsContainer').append(html);
        itemIndex++;
    }

    function populateItems(saleId) {
        $('#itemsContainer').empty();
        itemIndex = 0;
        if(salesItems[saleId]) {
            salesItems[saleId].forEach(item => {
                addItemRow(item.id, item.name, item.quantity);
            });
        }
    }

    $(document).ready(function(){
        let selectedSale = $('#saleSelect').val();
        if(selectedSale) {
            populateItems(selectedSale);
        }

        $('#saleSelect').on('change', function(){
            let saleId = $(this).val();
            populateItems(saleId);
        });

        $('#addItem').click(function(){
            addItemRow();
        });

        $(document).on('click', '.remove-item', function(){
            $(this).closest('.item-row').remove();
        });
    });
</script>