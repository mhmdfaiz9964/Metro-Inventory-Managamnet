@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            {{-- Main Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Warehouses</h4>
                    <button class="btn btn-success" onclick="openCreateModal()">
                        <i class="bi bi-plus-circle me-1"></i> Add Warehouse
                    </button>
                </div>

                <div class="card-body">
                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.warehouses.index') }}" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search warehouses..." onkeypress="if(event.keyCode==13){this.form.submit();}">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <a href="{{ route('admin.warehouses.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                        </div>
                    </form>

                    {{-- Warehouses Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Address</th>
                                    <th>Status</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($warehouses as $wh)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $wh->name }}</td>
                                    <td>{{ $wh->code ?? '-' }}</td>
                                    <td>{{ ucfirst($wh->type) }}</td>
                                    <td>{{ $wh->address ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $wh->status ? 'success' : 'secondary' }}">
                                            {{ $wh->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-primary me-1"
                                            onclick="openEditModal({{ $wh->id }}, '{{ $wh->name }}', '{{ $wh->code }}', '{{ $wh->type }}', '{{ $wh->address }}', {{ $wh->status ? 1 : 0 }})"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteWarehouse({{ $wh->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        {{-- Hidden Delete Form --}}
                                        <form id="delete-form-{{ $wh->id }}" action="{{ route('admin.warehouses.destroy', $wh->id) }}" method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center">No warehouses found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if($warehouses->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $warehouses->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal --}}
<div class="modal fade" id="warehouseModal" tabindex="-1" aria-labelledby="warehouseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="warehouseForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="warehouseModalLabel">Add Warehouse</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Warehouse Name</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" name="code" id="code" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="type" class="form-label">Type</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="warehouse">Warehouse</option>
                            <option value="store">Store</option>
                            <option value="factory">Factory</option>
                            <option value="service_center">Service Center</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea name="address" id="address" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="status" name="status" value="1" checked>
                        <label class="form-check-label" for="status">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const modal = new bootstrap.Modal(document.getElementById('warehouseModal'));

    // Open Create Modal
    function openCreateModal() {
        document.getElementById('warehouseForm').reset();
        document.getElementById('warehouseModalLabel').innerText = 'Add Warehouse';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('warehouseForm').action = "{{ route('admin.warehouses.store') }}";
        document.getElementById('saveBtn').innerText = 'Create';
        modal.show();
    }

    // Open Edit Modal
    function openEditModal(id, name, code, type, address, status) {
        document.getElementById('warehouseModalLabel').innerText = 'Edit Warehouse';
        document.getElementById('warehouseForm').action = `/admin/warehouses/${id}`;
        document.getElementById('formMethod').value = 'PUT';
        document.getElementById('saveBtn').innerText = 'Update';

        document.getElementById('name').value = name;
        document.getElementById('code').value = code;
        document.getElementById('type').value = type;
        document.getElementById('address').value = address;
        document.getElementById('status').checked = status === 1;

        modal.show();
    }

    // SweetAlert delete confirmation
    function deleteWarehouse(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This will permanently delete the warehouse.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection
