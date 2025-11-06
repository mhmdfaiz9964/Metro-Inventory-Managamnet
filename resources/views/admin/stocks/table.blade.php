<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="mb-0"><i class="bi bi-boxes me-2"></i>Stock Management</h4>
        <a href="{{ route('admin.stocks.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>Add Stock
        </a>
    </div>

    <div class="card-body">
        {{-- Filter/Search --}}
        <form method="GET" action="{{ route('admin.stocks.index') }}" class="row g-3 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by product name or code">
            </div>
            <div class="col-md-4">
                <select name="product_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Filter by Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id')==$product->id?'selected':'' }}>
                            {{ $product->name }} ({{ $product->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                <a href="{{ route('admin.stocks.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
            </div>
        </form>

        {{-- Stocks Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Regular Price</th>
                        <th>Wholesale Price</th>
                        <th>Sale Price</th>
                        <th>Available Stock</th>
                        <th>Stock Alert</th>
                        <th>Notes</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                    <tr>
                        <td>{{ $stock->id }}</td>
                        <td>{{ $stock->product->name ?? '-' }} ({{ $stock->product->code ?? '-' }})</td>
                        <td>{{ $stock->product->regular_price ?? '-' }}</td>
                        <td>{{ $stock->product->wholesale_price ?? '-' }}</td>
                        <td>{{ $stock->product->sale_price ?? '-' }}</td>
                        <td>
                            @if($stock->available_stock <= $stock->stock_alert)
                                <span class="badge bg-danger">{{ $stock->available_stock }} (Low!)</span>
                            @else
                                <span class="badge bg-success">{{ $stock->available_stock }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="alert alert-warning mt-1 p-1 d-inline-block">{{ $stock->stock_alert }}</span>
                        </td>
                        <td>{{ $stock->notes ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.stocks.edit', $stock->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted">No stock records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($stocks->hasPages())
    <div class="card-footer d-flex justify-content-center">
        {{ $stocks->withQueryString()->links() }}
    </div>
    @endif
</div>
