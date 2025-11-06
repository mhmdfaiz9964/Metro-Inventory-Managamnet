@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>BOM Stocks</h4>
        </div>

        {{-- Search --}}
        <form method="GET" class="row g-2 p-3">
            <div class="col-md-5">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by component or product">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary">Search</button>
                <a href="{{ route('admin.bom-stocks.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive p-3">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Component</th>
                        <th>Available Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $stock)
                        <tr>
                            <td>{{ $stock->id }}</td>
                            <td>{{ $stock->bomComponent->product->name ?? 'N/A' }}</td>
                            <td>{{ $stock->bomComponent->name ?? 'N/A' }}</td>
                            <td>{{ $stock->available_stock }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No BOM stocks found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
