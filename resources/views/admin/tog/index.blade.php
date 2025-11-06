@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Transfers of Goods</h4>
            <a href="{{ route('admin.transfers.create') }}" class="btn btn-success">Add Transfer</a>
        </div>

        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            {{-- Search --}}
            <form method="GET" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search transfers...">
                    <button class="btn btn-primary" type="submit">Search</button>
                    <a href="{{ route('admin.transfers.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>From Warehouse</th>
                            <th>To Warehouse</th>
                            <th>Date</th>
                            <th>Products</th>
                            <th>Notes</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transfers as $transfer)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $transfer->fromWarehouse->name }}</td>
                            <td>{{ $transfer->toWarehouse->name }}</td>
                            <td>{{ $transfer->transfer_date }}</td>
                            <td>
                                @foreach($transfer->items as $item)
                                    {{ $item->product->name }} ({{ $item->quantity }})<br>
                                @endforeach
                            </td>
                            <td>{{ $transfer->notes }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.transfers.edit', $transfer->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteTransfer({{ $transfer->id }})">Delete</button>

                                <form id="delete-form-{{ $transfer->id }}" action="{{ route('admin.transfers.destroy', $transfer->id) }}" method="POST" style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No transfers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transfers->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deleteTransfer(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This action will delete the transfer permanently!",
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
