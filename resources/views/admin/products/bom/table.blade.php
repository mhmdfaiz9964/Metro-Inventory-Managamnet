<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>BOM Components</h4>
        <a href="{{ route('admin.bom.create') }}" class="btn btn-success">Add BOM</a>
    </div>
    <div class="card-body">
        {{-- Search --}}
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                       placeholder="Search by component or product">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary">Search</button>
                <a href="{{ route('admin.bom.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive" style="overflow-x: auto; max-width: 100%;">
            <table class="table table-bordered table-striped table-hover" style="min-width: 1200px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Assembled Product</th>
                        <th>Component</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Notes</th>
                        <th style="width: 150px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($boms as $bom)
                    <tr>
                        <td>{{ $bom->id }}</td>
                        <td>{{ $bom->product->name }}</td>
                        <td>{{ $bom->name }}</td>
                        <td>{{ $bom->price }}</td>
                        <td>{{ $bom->quantity_required }}</td>
                        <td>{{ $bom->notes }}</td>
                        <td>
                            <a href="{{ route('admin.bom.edit', $bom->id) }}" class="btn btn-sm btn-primary">Edit</a>
                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteBOM({{ $bom->id }})">Delete</button>

                            <form id="delete-form-{{ $bom->id }}" action="{{ route('admin.bom.destroy', $bom->id) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No BOM components found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteBOM(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This BOM component will be permanently deleted!",
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
