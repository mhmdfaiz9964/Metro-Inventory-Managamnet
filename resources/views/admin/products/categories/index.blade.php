@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-card-list me-2"></i>Product Categories</h4>
            <a href="{{ route('admin.product-categories.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Add Category
            </a>
        </div>

        <div class="card-body">
            {{-- Filter/Search Form --}}
            <form method="GET" action="{{ route('admin.product-categories.index') }}" class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name" onkeypress="if(event.keyCode==13){this.form.submit();}">
                </div>
                <div class="col-md-4">
                    <select name="parent_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter by Parent --</option>
                        @foreach($parents as $parent)
                            <option value="{{ $parent->id }}" {{ request('parent_id')==$parent->id?'selected':'' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('admin.product-categories.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
                </div>
            </form>

            {{-- Categories Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Parent</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>

                            {{-- Image --}}
                            <td>
                                @if($category->image)
                                    <img src="{{ $category->image }}" alt="Category Image" class="img-thumbnail" style="max-height:50px;">
                                @else
                                    <span class="text-muted">No Image</span>
                                @endif
                            </td>

                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent?->name ?? '-' }}</td>

                            {{-- Status Badge --}}
                            <td>
                                @if($category->status=='Active')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-center">
                                <a href="{{ route('admin.product-categories.edit', $category->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteCategory({{ $category->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>

                                <form id="delete-form-{{ $category->id }}" action="{{ route('admin.product-categories.destroy', $category->id) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No categories found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($categories->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $categories->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteCategory(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This category will be permanently deleted!",
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
