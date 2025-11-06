        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="bi bi-cart4 me-2"></i>Sales</h4>
                <a href="{{ route('admin.sales.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>Add Sale
                </a>
            </div>

            <div class="card-body">
                {{-- Filter/Search --}}
                <form method="GET" action="{{ route('admin.sales.index') }}" class="row g-3 mb-3">
                    <div class="col-md-3">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search by product">
                    </div>
                    <div class="col-md-3">
                        <select name="salesperson_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Sales Rap --</option>
                            @foreach ($salespersons as $sp)
                                <option value="{{ $sp->id }}"
                                    {{ request('salesperson_id') == $sp->id ? 'selected' : '' }}>
                                    {{ $sp->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                        <a href="{{ route('admin.sales.index') }}" class="btn btn-secondary"><i
                                class="bi bi-x-circle me-1"></i>Clear</a>
                    </div>
                </form>

                {{-- Sales Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Sales Rap</th>
                                <th>Total Amount</th>
                                <th>Due Amount</th>
                                <th>Payment Method</th>
                                <th>Customer</th>
                                <th>Sale Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sales as $sale)
                                <tr>
                                    <td>{{ $sale->id }}</td>
                                    <td>{{ $sale->salesperson->name ?? '-' }}</td>
                                    <td>Rs. {{ number_format($sale->total_amount, 2) }}</td>
                                    <td>
                                        @php
                                            $paid = $sale->salePayments->sum('payment_paid');
                                            $due = $sale->total_amount - $paid;
                                        @endphp
                                        Rs. {{ number_format($due, 2) }}
                                    </td>
                                    <td>
                                        @if ($sale->payments->first()?->payment_method)
                                            <span class="badge bg-primary text-uppercase">
                                                {{ $sale->payments->first()->payment_method }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $sale->payments->first()?->customer->name ?? '-' }}
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}</td>
                                    <td class="text-center">
                                        {{-- View --}}
                                        <a href="{{ route('admin.sales.show', $sale->id) }}" class="btn btn-sm btn-info"
                                            title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.sales.edit', $sale->id) }}" class="btn btn-sm btn-primary"
                                            title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        {{-- Delete --}}
                                        <button class="btn btn-sm btn-danger" onclick="deleteSale({{ $sale->id }})"
                                            title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                        <form id="delete-form-{{ $sale->id }}"
                                            action="{{ route('admin.sales.destroy', $sale->id) }}" method="POST"
                                            style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No sales found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($sales->hasPages())
                    <div class="card-footer d-flex justify-content-center">
                        {{ $sales->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        function deleteSale(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This sale will be permanently deleted!",
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