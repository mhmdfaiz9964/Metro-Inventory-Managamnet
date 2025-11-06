@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-box-seam me-2"></i>Products</h4>
            <a href="{{ route('admin.products.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Add Product
            </a>
        </div>

        <div class="card-body">
            {{-- Filter/Search Form --}}
            <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3 mb-3">
                {{-- Search --}}
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control" placeholder="Search by name or code">
                </div>

                {{-- Status Filter --}}
                <div class="col-md-2">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Status --</option>
                        <option value="Active" {{ request('status')=='Active'?'selected':'' }}>Active</option>
                        <option value="Inactive" {{ request('status')=='Inactive'?'selected':'' }}>Inactive</option>
                    </select>
                </div>

                {{-- Category Filter --}}
                <div class="col-md-3">
                    <select name="category_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Category --</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id')==$cat->id?'selected':'' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Supplier Filter --}}
                <div class="col-md-2">
                    <select name="supplier_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Supplier --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id')==$sup->id?'selected':'' }}>
                                {{ $sup->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>Clear
                    </a>
                </div>
            </form>

            {{-- Products Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            <th>Status</th>
                            <th>Price</th>
                            <th>Warranty</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>

                            {{-- Image --}}
                            <td>
                                @if($product->image)
                                    <img src="{{ asset('storage/'.$product->image) }}" alt="Product Image" 
                                         class="img-thumbnail" style="max-height:50px;">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>

                            {{-- Name + Code --}}
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->code }}</td>

                            {{-- Category --}}
                            <td>{{ $product->category->name ?? '-' }}</td>

                            {{-- Supplier --}}
                            <td>{{ $product->supplier->name ?? '-' }}</td>

                            {{-- Status --}}
                            <td>
                                @if($product->status=='Active')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                @endif
                            </td>

                            {{-- Price --}}
                            <td>
                                @if($product->sale_price)
                                    <span class="text-success fw-bold">Rs. {{ number_format($product->sale_price, 2) }}</span>
                                    <small class="text-muted d-block"><del>Rs. {{ number_format($product->regular_price, 2) }}</del></small>
                                @else
                                    Rs. {{ number_format($product->regular_price ?? 0, 2) }}
                                @endif
                            </td>

                            {{-- Warranty --}}
                            <td>{{ $product->warranty }}</td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteProduct({{ $product->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>

                                <form id="delete-form-{{ $product->id }}" 
                                      action="{{ route('admin.products.destroy', $product->id) }}" 
                                      method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No products found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $products->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteProduct(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This product will be permanently deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result)=>{
            if(result.isConfirmed){
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection
