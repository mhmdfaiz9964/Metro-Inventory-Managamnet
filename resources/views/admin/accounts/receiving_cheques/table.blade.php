{{-- resources/views/admin/receiving-cheques/table.blade.php --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4>Receiving Cheques</h4>
        <a href="{{ route('admin.receiving-cheques.create') }}" class="btn btn-success">Add Cheque</a>
    </div>

    <div class="card-body">
        {{-- Filters --}}
        <form method="GET" action="{{ route('admin.receiving-cheques.index') }}" class="row g-2 mb-3">
            <div class="col-md-3">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Cheque No / Paid By">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                </select>
            </div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('admin.receiving-cheques.index') }}" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Cheque No</th>
                        <th>Type</th>
                        <th>Bank</th>
                        <th>Customer Name</th>
                        <th>Status</th>
                        <th>Paid Date</th>
                        <th>Cheque Date</th>
                        <th>Amount</th>
                        <th>Reason</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody id="cheque-table-body">
                    @forelse($cheques as $cheque)
                        <tr id="cheque-row-{{ $cheque->id }}">
                            <td>{{ $cheque->id }}</td>
                            <td>{{ $cheque->cheque_no }}</td>
                            <td>
                                @if ($cheque->cheque_type == 'Crossed Cheque')
                                    <span class="badge bg-primary">Crossed Cheque</span>
                                @elseif($cheque->cheque_type == 'Cash Cheque')
                                    <span class="badge bg-success">Cash Cheque</span>
                                @else
                                    <span class="badge bg-secondary">{{ $cheque->cheque_type }}</span>
                                @endif
                            </td>
                            <td>{{ $banks[$cheque->bank_id] ?? '-' }}</td>
                            <td>{{ $cheque->paid_by ?? '-' }}</td>
                            <td>
                                @if ($cheque->status == 'paid')
                                    <span class="badge bg-success">Paid</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>{{ $cheque->paid_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $cheque->cheque_date->format('Y-m-d') }}</td>
                            <td>Rs. {{ number_format($cheque->amount, 2) }}</td>
                            <td>{{ $cheque->reason }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.receiving-cheques.edit', $cheque->id) }}"
                                    class="btn btn-sm btn-primary"><i class="bi bi-pencil"></i></a>

                                @if ($cheque->status == 'pending' && $cheque->cheque_type == 'Crossed Cheque')
                                    <button class="btn btn-sm btn-success btn-mark-paid" data-id="{{ $cheque->id }}">
                                        Mark as Paid
                                    </button>
                                @endif

                                <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $cheque->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>

                                <form id="delete-form-{{ $cheque->id }}"
                                    action="{{ route('admin.receiving-cheques.destroy', $cheque->id) }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center">No receiving cheques found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($cheques->hasPages())
            <div class="mt-3 d-flex justify-content-center">
                {{ $cheques->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Mark Paid Modal -->
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="markPaidForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="markPaidModalLabel">Mark Cheque as Paid</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cheque_id" id="modal_cheque_id">
                    <div class="mb-3">
                        <label for="paid_date" class="form-label">Paid Date</label>
                        <input type="date" name="paid_date" id="modal_paid_date" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="paid_bank_account_id" class="form-label">Paid Bank Account</label>
                        <select name="paid_bank_account_id" id="modal_paid_bank_account_id" class="form-select" required>
                            <option value="">-- Select Bank Account --</option>
                            @foreach ($bankAccounts as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Mark as Paid</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>