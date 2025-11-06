    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-cash-coin me-2"></i> Receiving Payments</h4>
            <a href="{{ route('admin.receiving-payments.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add Payment
            </a>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.receiving-payments.index') }}" class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by reason / paid by">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Status --</option>
                        <option value="paid" {{ request('status')=='paid'?'selected':'' }}>Paid</option>
                        <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.receiving-payments.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reason</th>
                            <th>Paid Date</th>
                            <th>Paid By</th>
                            <th>Status</th>
                            <th>Bank</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->id }}</td>
                            <td>{{ $payment->reason }}</td>
                            <td>{{ $payment->paid_date }}</td>
                            <td>{{ $payment->paid_by }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status=='paid'?'success':'warning' }}">
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>{{ $payment->bank->bank_name ?? '-' }}</td>
                            <td>{{ number_format($payment->amount,2) }}</td>
                            <td>
                                <a href="{{ route('admin.receiving-payments.edit', $payment->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No payments found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>