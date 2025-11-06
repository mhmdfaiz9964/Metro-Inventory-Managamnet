@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            {{-- Main Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Purchases</h4>
                    <a href="{{ route('admin.purchases.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-circle me-1"></i> Add Purchase
                    </a>
                </div>

                <div class="card-body">
                    {{-- Search --}}
                    <form method="GET" class="row g-2 mb-4">
                        <div class="col-md-5">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by supplier">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-primary">Search</button>
                            <a href="{{ route('admin.purchases.index') }}" class="btn btn-secondary">Clear</a>
                        </div>
                    </form>

                    {{-- Purchases Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Supplier</th>
                                    <th>Purchase Date</th>
                                    <th>Payment Method</th>
                                    <th>Bank Account</th>
                                    <th>Payment Status</th>
                                    <th>Status</th>
                                    <th>Paid Amount</th>
                                    <th>BOM Components</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $purchase->supplier?->name ?? 'N/A' }}</td>
                                        <td>{{ $purchase->purchase_date ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($purchase->payment_method) ?? 'N/A' }}</td>
                                        <td>{{ $purchase->bankAccount?->bank_name ?? '-' }} {{ $purchase->bankAccount?->account_number ?? '' }}</td>
                                        <td>{{ ucfirst($purchase->payment_status) ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge 
                                                @if($purchase->status == 'pending') bg-warning
                                                @elseif($purchase->status == 'received') bg-success
                                                @else bg-dark @endif
                                                text-white">{{ ucfirst($purchase->status) }}</span>
                                        </td>
                                        <td>{{ number_format($purchase->paid_amount, 2) ?? 0 }}</td>
                                        <td>
                                            @if($purchase->bomProducts && $purchase->bomProducts->count() > 0)
                                                <ul class="list-unstyled mb-0">
                                                    @foreach($purchase->bomProducts as $item)
                                                        <li>
                                                            {{ $item->bomComponent?->name ?? 'N/A' }} 
                                                            (Qty: {{ $item->qty }}, Cost: {{ number_format($item->cost_price, 2) }})
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">No BOM</span>
                                            @endif
                                        </td>
                                        <td class="text-nowrap">
                                            <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn btn-sm btn-primary mb-1">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger mb-1" onclick="deletePurchase({{ $purchase->id }})">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                            <form id="delete-form-{{ $purchase->id }}" action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" style="display:none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No purchases found.</td>
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
