@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Cheque It Out</h4>
                <a href="{{ route('admin.cheques.create') }}" class="btn btn-success">Add Cheque</a>
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.cheques.index') }}" class="row g-2 mb-3">
                    <div class="col-md-2">
                        <input type="text" name="cheque_no" value="{{ request('cheque_no') }}" class="form-control"
                            placeholder="Cheque No">
                    </div>
                    <div class="col-md-2">
                        <select name="bank_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Bank --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank->id }}" {{ request('bank_id') == $bank->id ? 'selected' : '' }}>
                                    {{ $bank->bank_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="created_by" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Created By --</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ request('created_by') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Status --</option>
                            @foreach (['processing', 'pending', 'approved', 'rejected'] as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>
                    <div class="col-md-12 mt-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="{{ route('admin.cheques.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cheque No</th>
                                <th>Reason</th>
                                <th>Bank</th>
                                <th>Amount</th>
                                <th>Paid To</th>
                                <th>Created By</th>
                                <th>Status</th>
                                <th>Cheque Date</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cheques as $cheque)
                                <tr>
                                    <td>{{ $cheque->id }}</td>
                                    <td>{{ $cheque->cheque_no }}</td>
                                    <td>{{ $cheque->reason }}</td>
                                    <td>{{ $cheque->bank->bank_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($cheque->amount, 2) }}</td>
                                    <td>{{ $cheque->paid_to ?? '-' }}</td>
                                    <td>{{ $cheque->creator->name ?? '-' }}</td>
                                    <td>
                                        @if ($cheque->status == 'approved')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Approved
                                            </span>
                                        @elseif($cheque->status == 'pending')
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock-history me-1"></i> Pending
                                            </span>
                                        @elseif($cheque->status == 'processing')
                                            <span class="badge bg-primary">
                                                <i class="bi bi-gear-fill me-1"></i> Processing
                                            </span>
                                        @elseif($cheque->status == 'rejected')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i> Rejected
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Unknown</span>
                                        @endif
                                    </td>

                                    <td>{{ \Carbon\Carbon::parse($cheque->cheque_date)->format('d M Y') }}</td>
                                    <td class="text-center">
                                        {{-- Check if cheque is rejected --}}
                                        @php
                                            $isRejected = $cheque->status == 'rejected';
                                        @endphp

                                        {{-- Edit --}}
                                        <a href="{{ route('admin.cheques.edit', $cheque->id) }}"
                                            class="btn btn-sm btn-primary {{ $isRejected ? 'disabled' : '' }}"
                                            {{ $isRejected ? 'aria-disabled=true tabindex=-1' : '' }}>
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        {{-- Return Cheque --}}
                                        <button type="button"
                                            class="btn btn-sm btn-warning {{ $isRejected ? 'disabled' : '' }}"
                                            data-bs-toggle="modal" data-bs-target="#returnChequeModal{{ $cheque->id }}"
                                            {{ $isRejected ? 'disabled' : '' }}>
                                            <i class="bi bi-arrow-counterclockwise"></i>
                                        </button>

                                        {{-- Delete --}}
                                        <form action="{{ route('admin.cheques.destroy', $cheque->id) }}" method="POST"
                                            class="d-inline delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                class="btn btn-sm btn-danger btn-delete {{ $isRejected ? 'disabled' : '' }}"
                                                {{ $isRejected ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>

                                        {{-- Return Cheque Modal --}}
                                        <div class="modal fade" id="returnChequeModal{{ $cheque->id }}" tabindex="-1"
                                            aria-labelledby="returnChequeLabel{{ $cheque->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('admin.cheques.return.store', $cheque->id) }}"
                                                    method="POST">
                                                    @csrf
                                                    <input type="hidden" name="cheque_bank"
                                                        value="{{ $cheque->bank->id ?? '' }}">
                                                        <input type="hidden" name="type" value="Cheque It Out"> {{-- Added hidden type input --}}
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="returnChequeLabel{{ $cheque->id }}">Return Cheque:
                                                                {{ $cheque->cheque_no }}</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">Return Date</label>
                                                                <input type="date" name="return_date"
                                                                    class="form-control" value="{{ date('Y-m-d') }}"
                                                                    required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label">Return Reason</label>
                                                                <textarea name="return_reason" class="form-control" rows="3" required></textarea>
                                                            </div>
                                                            <p><strong>Amount:</strong>
                                                                {{ number_format($cheque->amount, 2) }}</p>
                                                            <p><strong>Bank:</strong>
                                                                {{ $cheque->bank->bank_name ?? 'N/A' }}</p>
                                                            <p><strong>Paid To:</strong> {{ $cheque->paid_to }}</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit"
                                                                class="btn btn-primary {{ $isRejected ? 'disabled' : '' }}">Create
                                                                Return Cheque</button>
                                                        </div>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center">No cheques found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex justify-content-center mt-3">
                    {{ $cheques->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Delete confirmation
            const deleteForms = document.querySelectorAll(".delete-form");
            deleteForms.forEach(form => {
                const btn = form.querySelector(".btn-delete");
                btn.addEventListener("click", function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: "Are you sure?",
                        text: "This action cannot be undone!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#3085d6",
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) form.submit();
                    });
                });
            });
        });
    </script>
@endsection
