    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h4 class="mb-0 text-white"><i class="bi bi-box-seam me-2 text-white"></i>Manufactures</h4>
            <a href="{{ route('admin.manufactures.create') }}" class="btn btn-light btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Manufacture
            </a>
        </div>

        <div class="card-body">
            {{-- Search --}}
            <form method="GET" class="row g-2 mb-4">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="Search by product or user">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary">Search</button>
                    <a href="{{ route('admin.manufactures.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            @if($manufactures->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Assigned User</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Components</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($manufactures as $index => $manufacture)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $manufacture->product->name ?? 'N/A' }}</td>
                            <td>{{ $manufacture->assignedUser?->name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge 
                                    @if($manufacture->status == 'processing') bg-info
                                    @elseif($manufacture->status == 'manufacturing') bg-primary
                                    @elseif($manufacture->status == 'testing') bg-warning
                                    @elseif($manufacture->status == 'completed') bg-success
                                    @elseif($manufacture->status == 'added to sale') bg-secondary
                                    @else bg-dark @endif
                                text-white">{{ ucfirst($manufacture->status) }}</span>
                            </td>
                            <td>{{ $manufacture->start_date ?? 'N/A' }}</td>
                            <td>{{ $manufacture->end_date ?? 'N/A' }}</td>
                            <td>
                                @if($manufacture->manufactureItems?->count() > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach($manufacture->manufactureItems as $item)
                                            <li>{{ $item->bomComponent?->name ?? 'N/A' }} (Qty: {{ $item->required_qty ?? 0 }})</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted">No components</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.manufactures.edit', $manufacture->id) }}" class="btn btn-sm btn-primary mb-1">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>

                                <button type="button" class="btn btn-sm btn-danger mb-1" onclick="deleteManufacture({{ $manufacture->id }})">
                                    <i class="bi bi-trash"></i> Delete
                                </button>

                                <form id="delete-form-{{ $manufacture->id }}" action="{{ route('admin.manufactures.destroy', $manufacture->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $manufactures->links() }} {{-- pagination --}}
            @else
                <div class="alert alert-info text-center">No manufactures found.</div>
            @endif
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteManufacture(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This manufacture will be permanently deleted!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    })
}
</script>