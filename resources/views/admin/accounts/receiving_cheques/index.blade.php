@extends('layouts.app')

@section('content')
<div class="container my-5">
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
                    <tbody>
                        @forelse($cheques as $cheque)
                        <tr>
                            <td>{{ $cheque->id }}</td>
                            <td>{{ $cheque->cheque_no }}</td>
                            <td>
                                <span class="badge bg-{{ $cheque->cheque_type == 'Crossed Cheque' ? 'primary' : ($cheque->cheque_type == 'Cash Cheque' ? 'success' : 'secondary') }}">
                                    {{ $cheque->cheque_type }}
                                </span>
                            </td>
                            <td>{{ $banks[$cheque->bank_id] ?? '-' }}</td>
                            <td>{{ $cheque->paid_by ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $cheque->status == 'paid' ? 'success' : 'warning text-dark' }}">
                                    {{ ucfirst($cheque->status) }}
                                </span>
                            </td>
                            <td>{{ $cheque->paid_date?->format('Y-m-d') ?? '-' }}</td>
                            <td>{{ $cheque->cheque_date->format('Y-m-d') }}</td>
                            <td>Rs. {{ number_format($cheque->amount, 2) }}</td>
                            <td>{{ $cheque->reason }}</td>
                            <td class="text-center">
                               @if($cheque->status == 'rejected')
    <span class="badge bg-danger">Cheque Rejected / Returned</span>
@else
    <a href="{{ route('admin.receiving-cheques.edit', $cheque->id) }}" class="btn btn-sm btn-primary">
        <i class="bi bi-pencil"></i>
    </a>

    @if ($cheque->status == 'pending' && $cheque->cheque_type == 'Crossed Cheque')
        <button class="btn btn-sm btn-success btn-mark-paid" data-id="{{ $cheque->id }}" data-bs-toggle="modal" data-bs-target="#markPaidModal">
            Mark as Paid
        </button>
    @endif

    <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#returnChequeModal{{ $cheque->id }}">
        <i class="bi bi-arrow-counterclockwise"></i>
    </button>

    <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $cheque->id }}">
        <i class="bi bi-trash"></i>
    </button>

    {{-- Delete form --}}
    <form id="delete-form-{{ $cheque->id }}" action="{{ route('admin.receiving-cheques.destroy', $cheque->id) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endif


                                {{-- Return Cheque Modal --}}
                                <div class="modal fade" id="returnChequeModal{{ $cheque->id }}" tabindex="-1" aria-labelledby="returnChequeLabel{{ $cheque->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                       <form action="{{ route('admin.cheques.return.store', $cheque->id) }}"
                                                    method="POST">
                                            @csrf
                                            <input type="hidden" name="cheque_bank" value="{{ $cheque->bank->id ?? '' }}">
                                            <input type="hidden" name="type" value="Cheque in Hand">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="returnChequeLabel{{ $cheque->id }}">
                                                        Return Cheque: {{ $cheque->cheque_no }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label">Return Date</label>
                                                        <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Return Reason</label>
                                                        <textarea name="return_reason" class="form-control" rows="3" required></textarea>
                                                    </div>
                                                    <p><strong>Amount:</strong> {{ number_format($cheque->amount, 2) }}</p>
                                                    <p><strong>Bank:</strong> {{ $banks[$cheque->bank_id] ?? '-' }}</p>
                                                    <p><strong>Paid To:</strong> {{ $cheque->paid_by ?? '-' }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                   <button type="submit" class="btn btn-primary">Create Return Cheque</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                {{-- End Return Cheque Modal --}}
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
</div>

{{-- Mark Paid Modal --}}
<div class="modal fade" id="markPaidModal" tabindex="-1" aria-labelledby="markPaidModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="markPaidForm">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="markPaidModalLabel">Mark Cheque as Paid</h5>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete buttons
    document.querySelectorAll('.btn-delete').forEach(button => {
        button.addEventListener('click', function() {
            const chequeId = this.dataset.id;
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + chequeId).submit();
                }
            });
        });
    });

    // Mark Paid modal open
    document.querySelectorAll('.btn-mark-paid').forEach(btn => {
        btn.addEventListener('click', function() {
            const chequeId = this.dataset.id;
            document.getElementById('modal_cheque_id').value = chequeId;
            document.getElementById('modal_paid_date').valueAsDate = new Date();
        });
    });

    // Submit Mark Paid
    document.getElementById('markPaidForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const chequeId = document.getElementById('modal_cheque_id').value;
        const paidDate = document.getElementById('modal_paid_date').value;
        const paidBank = document.getElementById('modal_paid_bank_account_id').value;
        const token = document.querySelector('input[name="_token"]').value;

        fetch(`/admin/receiving-cheques/mark-received/${chequeId}`, {
            method: 'POST',
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
            body: JSON.stringify({ paid_date: paidDate, paid_bank_account_id: paidBank })
        })
        .then(res => res.json())
        .then(res => { if(res.success) location.reload(); });
    });
});
</script>
@endsection
