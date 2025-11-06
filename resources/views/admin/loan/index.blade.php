@extends('layouts.app')

@section('title', 'Credits')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Credits</h4>
        </div>

        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                           placeholder="Search by customer/supplier">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="given" {{ request('type')=='given' ? 'selected' : '' }}>Given</option>
                        <option value="received" {{ request('type')=='received' ? 'selected' : '' }}>Received</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status')=='paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partially_paid" {{ request('status')=='partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference</th>
                            <th>Customer</th>
                            <th>Supplier</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Amount</th>
                            <th>Loan Date</th>
                            <th>Due Date</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loan->id }}</td>
                            <td>{{ $loan->reference_number ?? '-' }}</td>
                            <td>{{ $loan->customer->name ?? '-' }}</td>
                            <td>{{ $loan->supplier->name ?? '-' }}</td>
                            <td>{{ ucfirst($loan->type) }}</td>
                            <td>{{ ucfirst(str_replace('_',' ',$loan->status)) }}</td>
                            <td>{{ number_format($loan->amount, 2) }}</td>
                            <td>{{ $loan->loan_date->format('Y-m-d') }}</td>
                            <td>{{ $loan->due_date ? $loan->due_date->format('Y-m-d') : '-' }}</td>
                            <td>{{ $loan->note ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No loans found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($loans->hasPages())
            <div class="mt-3 d-flex justify-content-center">
                {{ $loans->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
