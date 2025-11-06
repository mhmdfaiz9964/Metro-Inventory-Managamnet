@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        {{-- Card Header --}}
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-people-fill me-2"></i>Suppliers</h4>
            <a href="{{ route('admin.suppliers.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Add Supplier
            </a>
        </div>

        {{-- Card Body --}}
        <div class="card-body">
            {{-- Filter/Search Form --}}
            <form method="GET" action="{{ route('admin.suppliers.index') }}" class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by name, email, or mobile" onkeypress="if(event.keyCode==13){this.form.submit();}">
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter by Status --</option>
                        <option value="Active" {{ request('status')=='Active'?'selected':'' }}>Active</option>
                        <option value="Inactive" {{ request('status')=='Inactive'?'selected':'' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
                </div>
            </form>

            {{-- Suppliers Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Mobile Number</th>
                            <th>Email</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->id }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->mobile_number }}</td>
                            <td>{{ $supplier->email ?? '-' }}</td>
                            <td>{{ $supplier->notes ?? '-' }}</td>
                            <td>
                                @if($supplier->status=='active')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.suppliers.edit', $supplier->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                 <a href="{{ route('admin.suppliers.history', $supplier) }}" class="btn btn-sm btn-info"
                                    title="View History">
                                    <i class="bi bi-clock-history"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteSupplier({{ $supplier->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Delete Form --}}
                                <form id="delete-form-{{ $supplier->id }}" action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No suppliers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        @if($suppliers->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $suppliers->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteSupplier(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This supplier will be permanently deleted!",
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
