@extends('layouts.app')

@section('title', 'Purchase Returns')

@section('content')
    <div class="container-fluid my-4 px-4">
        <div class="card shadow-lg border-0 rounded-3">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h4 class="mb-0 text-white">Purchase Returns</h4>
                <a href="{{ route('admin.purchase-returns.create') }}" class="btn btn-light">
                    <i class="bi bi-plus-circle me-1"></i>New Return
                </a>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('admin.purchase-returns.index') }}" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search returns..." onkeypress="if(event.keyCode==13){this.form.submit();}">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <a href="{{ route('admin.purchase-returns.index') }}"
                            class="btn btn-outline-secondary ms-2">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Purchase ID</th>
                                <th>Supplier</th>
                                <th>Return Date</th>
                                <th>Reason</th>
                                <th>Total Amount</th>
                                <th>Items</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($purchaseReturns as $return)
                                <tr>
                                    <td>{{ $return->id }}</td>
                                    <td>#{{ $return->purchase->id ?? '-' }}</td>
                                    <td>{{ $return->supplier->name ?? '-' }}</td>
                                    <td>{{ $return->return_date->format('d M Y') }}</td>
                                    <td>{{ $return->reason }}</td>
                                    <td>{{ number_format($return->total_amount, 2) }}</td>
                                    <td>
                                        @foreach ($return->items as $item)
                                            {{ $item->bomComponent->name ?? '-' }} ({{ $item->quantity }})<br>
                                        @endforeach
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.purchase-returns.edit', $return->id) }}"
                                            class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteReturn({{ $return->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $return->id }}"
                                            action="{{ route('admin.purchase-returns.destroy', $return->id) }}"
                                            method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No returns found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>


                @if ($purchaseReturns->hasPages())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $purchaseReturns->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteReturn(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    </script>
@endsection
