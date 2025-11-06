<div class="card shadow-sm">
    {{-- Card Header --}}
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0 text-white"><i class="bi bi-people-fill me-2 text-white"></i>Suppliers</h4>
        <a href="{{ route('admin.suppliers.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>Add Supplier
        </a>
    </div>

    {{-- Card Body --}}
    <div class="card-body">
        {{-- Filter/Search Form --}}
        <form method="GET" action="{{ route('admin.suppliers.index') }}" class="row g-3 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Search by name, email, or mobile"
                    onkeypress="if(event.keyCode==13){this.form.submit();}">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Filter by Status --</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                    <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                <a href="{{ route('admin.suppliers.index') }}" class="btn btn-secondary"><i
                        class="bi bi-x-circle me-1"></i>Clear</a>
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
                        <th>Balance Due</th>
                        <th>Total Paid</th>
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
                            <td>{{ number_format($supplier->balance_due ?? 0, 2) }}</td>
                            <td>{{ number_format($supplier->total_paid ?? 0, 2) }}</td>
                            <td>{{ $supplier->notes ?? '-' }}</td>
                            <td>
                                @if (strtolower($supplier->status) == 'active')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Inactive</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.suppliers.edit', $supplier->id) }}"
                                    class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>

                                {{-- View History Button --}}
                                <a href="{{ route('admin.suppliers.history', $supplier) }}" class="btn btn-sm btn-info"
                                    title="View History">
                                    <i class="bi bi-clock-history"></i>
                                </a>

                                <button type="button" class="btn btn-sm btn-danger"
                                    onclick="deleteSupplier({{ $supplier->id }})" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Delete Form --}}
                                <form id="delete-form-{{ $supplier->id }}"
                                    action="{{ route('admin.suppliers.destroy', $supplier->id) }}" method="POST"
                                    style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($suppliers->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $suppliers->withQueryString()->links() }}
        </div>
    @endif
</div>

{{-- History Modal --}}
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="historyModalLabel"><i class="bi bi-clock-history me-2"></i>Supplier History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="historyModalBody">
                <p class="text-center">Loading...</p>
            </div>
        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
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
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    function viewHistory(id, name) {
        // Set modal title
        document.getElementById('historyModalLabel').innerText = `History of ${name}`;

        // Show modal
        var historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
        historyModal.show();

        // Load history via AJAX (dummy content here)
        var modalBody = document.getElementById('historyModalBody');
        modalBody.innerHTML = '<p class="text-center py-3">Loading history...</p>';

        // Simulate history data (replace with AJAX call if needed)
        setTimeout(() => {
            modalBody.innerHTML = `
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date</th>
                            <th>Transaction Type</th>
                            <th>Amount</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>1</td><td>2025-10-01</td><td>Purchase</td><td>500.00</td><td>1500.00</td></tr>
                        <tr><td>2</td><td>2025-09-25</td><td>Payment</td><td>300.00</td><td>1200.00</td></tr>
                        <tr><td>3</td><td>2025-09-10</td><td>Purchase</td><td>700.00</td><td>1900.00</td></tr>
                    </tbody>
                </table>
            `;
        }, 500);
    }
</script>
