@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Transactions</h4>
        </div>

        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-3">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" placeholder="From Date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" placeholder="To Date">
                </div>
                <div class="col-md-3">
                    <select name="bank_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter by Bank --</option>
                        @foreach ($banks as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }} ({{ $bank->account_number }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="created_by" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter by Created By --</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Transaction ID</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>From Bank</th>
                            <th>To Bank</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $txn)
                        <tr>
                            <td>{{ $txn->id }}</td>
                            <td>{{ $txn->transaction_id }}</td>
                            <td>{{ number_format($txn->amount, 2) }}</td>
                            <td>
                                @if($txn->type == 'credited')
                                    <span class="badge bg-success">Credited</span>
                                @else
                                    <span class="badge bg-danger">Debited</span>
                                @endif
                            </td>
                            <td>{{ $txn->fromBank?->bank_name ?? '-' }}</td>
                            <td>{{ $txn->toBank?->bank_name ?? '-' }}</td>
                            <td>{{ $txn->creator?->name ?? '-' }}</td>
                            <td>{{ $txn->updater?->name ?? '-' }}</td>
                            <td>{{ $txn->created_at->format('d M, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No transactions found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($transactions->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
