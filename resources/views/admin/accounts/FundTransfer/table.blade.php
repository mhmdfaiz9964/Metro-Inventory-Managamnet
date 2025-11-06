    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-cash-coin me-2"></i> Fund Transfers</h4>
            <a href="{{ route('admin.fund-transfers.create') }}" class="btn btn-success">
                <i class="bi bi-plus-circle me-1"></i> Add Transfer
            </a>
        </div>

        <div class="card-body">
            {{-- Filter/Search --}}
            <form method="GET" action="{{ route('admin.fund-transfers.index') }}" class="row g-2 mb-3">
                <div class="col-md-2">
                    <select name="bank_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- From Bank --</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="transferred_by" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Transferred By --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('transferred_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" onchange="this.form.submit()">
                </div>
                <div class="col-md-4 d-flex">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
                           class="form-control me-2">
                     <a href="{{ route('admin.fund-transfers.index') }}" class="btn btn-secondary me-2">Clear</a>
                    <button class="btn btn-primary"><i class="bi bi-search"></i></button>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reason</th>
                            <th>From Bank</th>
                            <th>To Bank</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Transferred By</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fundTransfers as $transfer)
                            <tr>
                                <td>{{ $transfer->id }}</td>
                                <td>{{ $transfer->reason }}</td>
                                <td>{{ $transfer->fromBank->bank_name ?? '-' }}</td>
                                <td>{{ $transfer->toBank->bank_name ?? '-' }}</td>
                                <td>{{ ucfirst(str_replace('_',' ', $transfer->type)) }}</td>
                                <td>{{ number_format($transfer->amount, 2) }}</td>
                                <td>{{ $transfer->transfer_date->format('Y-m-d') }}</td>
                                <td>{{ $transfer->transferredBy->name ?? '-' }}</td>
                                <td>
                                    @if ($transfer->status == 'completed')
                                        <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Completed</span>
                                    @elseif ($transfer->status == 'pending')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Pending</span>
                                    @elseif ($transfer->status == 'processing')
                                        <span class="badge bg-info text-dark"><i class="bi bi-arrow-repeat me-1"></i> Processing</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="bi bi-clock-history me-1"></i> Pending Approval</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.fund-transfers.edit', $transfer->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteTransfer({{ $transfer->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $transfer->id }}" action="{{ route('admin.fund-transfers.destroy', $transfer->id) }}" method="POST" style="display:none;">
                                        @csrf @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center">No transfers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($fundTransfers->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $fundTransfers->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteTransfer(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This transfer will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>