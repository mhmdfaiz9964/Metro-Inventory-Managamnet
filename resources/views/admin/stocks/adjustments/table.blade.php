    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-card-list me-2"></i>Stock Adjustments</h4>
            <a href="{{ route('admin.stock-adjustments.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Add Adjustment
            </a>
        </div>

        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.stock-adjustments.index') }}" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by product name/code">
                </div>

                <div class="col-md-3">
                    <select name="adjustment_type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Adjustment Type --</option>
                        <option value="increase" {{ request('adjustment_type')=='increase'?'selected':'' }}>Increase</option>
                        <option value="decrease" {{ request('adjustment_type')=='decrease'?'selected':'' }}>Decrease</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="reason_type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Reason Type --</option>
                        <option value="damage" {{ request('reason_type')=='damage'?'selected':'' }}>Damage</option>
                        <option value="stock take" {{ request('reason_type')=='stock take'?'selected':'' }}>Stock Take</option>
                        <option value="correction" {{ request('reason_type')=='correction'?'selected':'' }}>Correction</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('admin.stock-adjustments.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Adjustment Type</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Adjusted By</th>
                            <th>Created At</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->id }}</td>
                            <td>{{ $adj->stock->product->name ?? '-' }} ({{ $adj->stock->product->code ?? '-' }})</td>
                            <td>
                                @if($adj->adjustment_type == 'increase')
                                    <span class="badge bg-success">Increase</span>
                                @else
                                    <span class="badge bg-danger">Decrease</span>
                                @endif
                            </td>
                            <td>{{ $adj->quantity }}</td>
                            <td>{{ ucfirst($adj->reason_type) }}</td>
                            <td>{{ $adj->adjustedByUser ? $adj->adjustedByUser->first_name . ' ' . $adj->adjustedByUser->last_name : '-' }}</td>

                            <td>{{ $adj->created_at->format('d M Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.stock-adjustments.edit', $adj->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No stock adjustments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($adjustments->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $adjustments->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>