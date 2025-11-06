@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row">

        {{-- Left Side: Create Form --}}
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i> Add Product Brand</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.product-brand.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Brand Name</label>
                            <input type="text" name="name" class="form-control" placeholder="Enter brand name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="3" placeholder="Optional note"></textarea>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right Side: Table --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-card-list me-2"></i> Product Brands List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Note</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productBrands as $productBrand)
                                <tr>
                                    <td>{{ $productBrand->id }}</td>
                                    <td>{{ $productBrand->name }}</td>
                                    <td>{{ $productBrand->note ?? '-' }}</td>
                                    <td class="text-center">
                                        {{-- Edit Modal Trigger --}}
                                        <button type="button" class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editProductBrandModal{{ $productBrand->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        {{-- Delete --}}
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteProductBrand({{ $productBrand->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $productBrand->id }}" 
                                              action="{{ route('admin.product-brand.destroy', $productBrand->id) }}" 
                                              method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editProductBrandModal{{ $productBrand->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('admin.product-brand.update', $productBrand->id) }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Product Brand</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Name</label>
                                                        <input type="text" name="name" value="{{ $productBrand->name }}" class="form-control" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Note</label>
                                                        <textarea name="note" class="form-control">{{ $productBrand->note }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No product brands found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if($productBrands->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $productBrands->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteProductBrand(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This product brand will be permanently deleted!",
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
