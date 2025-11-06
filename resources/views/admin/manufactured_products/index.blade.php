@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            {{-- Main Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Manufactured Product Purchases</h4>
                    <a href="{{ route('admin.purchase_manufactured_products.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i> Add Purchase
                    </a>
                </div>

                <div class="card-body">
                    {{-- Search --}}
                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-md-4">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by supplier or manufactured name">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary">Search</button>
                            <a href="{{ route('admin.purchase_manufactured_products.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>

                    {{-- Purchases Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Manufactured Product</th>
                                    <th>Supplier</th>
                                    <th>Date</th>
                                    <th>Total Price</th>
                                    <th>Discount</th>
                                    <th>Discount Type</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining</th>
                                    <th>Payments</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $purchase->manufactured_product ?? 'N/A' }}</td>
                                        <td>{{ $purchase->supplier?->name ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($purchase->date)->format('Y-m-d') }}</td>
                                        <td>{{ number_format($purchase->total_price, 2) }}</td>
                                        <td>{{ number_format($purchase->discount ?? 0, 2) }}</td>
                                        <td>{{ ucfirst($purchase->discount_type) }}</td>
                                        <td>{{ number_format($purchase->payments->sum('amount'), 2) }}</td>
                                        <td>{{ number_format($purchase->total_price - $purchase->payments->sum('amount'), 2) }}</td>
                                        <td>
                                            @if($purchase->payments->count() > 0)
                                                <ul class="list-unstyled mb-0 small">
                                                    @foreach($purchase->payments as $payment)
                                                        <li>
                                                            {{ ucfirst($payment->payment_method) }} - {{ number_format($payment->amount, 2) }} ({{ \Carbon\Carbon::parse($payment->date)->format('Y-m-d') }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">No payments</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.purchase_manufactured_products.edit', $purchase->id) }}" class="btn btn-sm btn-primary mb-1">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <a href="{{ route('admin.purchase_manufactured_products.show', $purchase->id) }}" class="btn btn-sm btn-info mb-1 text-white">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger mb-1" onclick="deletePurchase({{ $purchase->id }})">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                            <form id="delete-form-{{ $purchase->id }}" action="{{ route('admin.purchase_manufactured_products.destroy', $purchase->id) }}" method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">No manufactured product purchases found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="mt-3">
                        {{ $purchases->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function deletePurchase(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "This purchase will be permanently deleted!",
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
@endsection
