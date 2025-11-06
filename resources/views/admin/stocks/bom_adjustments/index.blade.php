@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-boxes me-2"></i>BOM Stock Adjustments</h4>
            <a href="{{ route('admin.bom-stock-adjustments.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i>Add Adjustment
            </a>
        </div>

        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.bom-stock-adjustments.index') }}" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by component">
                </div>
                <div class="col-md-3">
                    <select name="component_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter by Component --</option>
                        @foreach($components as $component)
                            <option value="{{ $component->id }}" {{ request('component_id')==$component->id?'selected':'' }}>
                                {{ $component->name }} ({{ $component->product_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="adjustment_type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Type --</option>
                        <option value="increase" {{ request('adjustment_type')=='increase'?'selected':'' }}>Increase</option>
                        <option value="decrease" {{ request('adjustment_type')=='decrease'?'selected':'' }}>Decrease</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="reason_type" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Reason --</option>
                        <option value="damage" {{ request('reason_type')=='damage'?'selected':'' }}>Damage</option>
                        <option value="stock take" {{ request('reason_type')=='stock take'?'selected':'' }}>Stock Take</option>
                        <option value="correction" {{ request('reason_type')=='correction'?'selected':'' }}>Correction</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Search</button>
                    <a href="{{ route('admin.bom-stock-adjustments.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Component</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Quantity</th>
                            <th>Adjusted By</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                        <tr>
                            <td>{{ $adj->id }}</td>
                            <td>{{ $adj->bomStock->bomComponent->name ?? '-' }} ({{ $adj->bomStock->bomComponent->product_code ?? '-' }})</td>
                            <td>
                                @if($adj->adjustment_type == 'increase')
                                    <span class="badge bg-success">Increase</span>
                                @else
                                    <span class="badge bg-danger">Decrease</span>
                                @endif
                            </td>
                            <td><span class="badge bg-info">{{ ucfirst($adj->reason_type) }}</span></td>
                            <td>{{ $adj->quantity }}</td>
                            <td>{{ $adj->adjustedByUser->name ?? '-' }}</td>
                            <td>{{ $adj->created_at->format('d M Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.bom-stock-adjustments.edit', $adj->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">No adjustments found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($adjustments->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $adjustments->withQueryString()->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
