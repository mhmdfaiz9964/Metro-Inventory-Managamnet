@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-plus-circle me-2 text-white"></i>Create Transfer</h4>
        </div>
        <div class="card-body">

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <form action="{{ route('admin.transfers.store') }}" method="POST">
                @csrf
                <div class="row g-3">

                    <div class="col-md-6">
                        <label class="form-label">From Warehouse <span class="text-danger">*</span></label>
                        <select name="from_warehouse_id" class="form-select @error('from_warehouse_id') is-invalid @enderror" required>
                            <option value="">Select Warehouse</option>
                            @foreach(\App\Models\Warehouse::all() as $w)
                                <option value="{{ $w->id }}" {{ old('from_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                        @error('from_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">To Warehouse <span class="text-danger">*</span></label>
                        <select name="to_warehouse_id" class="form-select @error('to_warehouse_id') is-invalid @enderror" required>
                            <option value="">Select Warehouse</option>
                            @foreach(\App\Models\Warehouse::all() as $w)
                                <option value="{{ $w->id }}" {{ old('to_warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                            @endforeach
                        </select>
                        @error('to_warehouse_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                        <input type="date" name="transfer_date" value="{{ old('transfer_date') }}" class="form-control @error('transfer_date') is-invalid @enderror" required>
                        @error('transfer_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" value="{{ old('notes') }}" class="form-control @error('notes') is-invalid @enderror">
                        @error('notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                <hr>
                <h5>Products</h5>
                <div id="products-wrapper">
                    @php $oldProducts = old('products', [['product_id'=>'','quantity'=>'']]); @endphp
                    @foreach($oldProducts as $index => $prod)
                        <div class="row g-3 mb-2 product-item">
                            <div class="col-md-6">
                                <select name="products[{{ $index }}][product_id]" class="form-select" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" {{ $prod['product_id'] == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="number" name="products[{{ $index }}][quantity]" value="{{ $prod['quantity'] }}" class="form-control" placeholder="Quantity" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-danger remove-product">Remove</button>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-product" class="btn btn-secondary mb-3">Add Product</button>

                <div class="mt-4 d-flex justify-content-between">
                    <a href="{{ route('admin.transfers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left-circle me-1"></i> Back</a>
                    <button type="submit" class="btn btn-primary">Create Transfer</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
let productIndex = {{ count($oldProducts) }};

document.getElementById('add-product').addEventListener('click', function() {
    let wrapper = document.getElementById('products-wrapper');
    let div = document.createElement('div');
    div.classList.add('row','g-3','mb-2','product-item');
    div.innerHTML = `
        <div class="col-md-6">
            <select name="products[${productIndex}][product_id]" class="form-select" required>
                <option value="">Select Product</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" name="products[${productIndex}][quantity]" class="form-control" placeholder="Quantity" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-product">Remove</button>
        </div>
    `;
    wrapper.appendChild(div);
    productIndex++;
});

document.addEventListener('click', function(e) {
    if(e.target && e.target.classList.contains('remove-product')) {
        e.target.closest('.product-item').remove();
    }
});
</script>
@endsection
