    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Purchase Invoices</h4>
        </div>

        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-2">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" placeholder="To Date">
                </div>
                <div class="col-md-3">
                    <select name="created_by" class="form-select">
                        <option value="">-- Filter by Created By --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by Invoice ID">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    <a href="{{ route('admin.invoices.purchases') }}" class="btn btn-secondary w-100">Clear</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invoice ID</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Created By</th>
                            <th>Total Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $index => $purchase)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $purchase->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</td>
                            <td>{{ $purchase->supplier?->name ?? '-' }}</td>
                            <td>{{ $purchase->creator?->name ?? '-' }}</td>
                            <td>Rs. {{ number_format($purchase->paid_amount, 2) }}</td>
                            <td>
                                <a href="{{ route('admin.invoices.printPurchase', $purchase->id) }}" target="_blank" class="btn btn-primary btn-sm">
                                    Print
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No purchases found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($purchases->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $purchases->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>