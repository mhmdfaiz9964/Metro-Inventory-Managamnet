@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4><i class="bi bi-cash-stack me-2"></i> Customer Loans</h4>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createLoanModal">
                <i class="bi bi-plus-circle"></i> Add Loan
            </button>
        </div>

        <div class="card-body">
            {{-- Filter Section --}}
            <form method="GET" action="{{ route('admin.customer-loans.index') }}" class="row g-3 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search by customer name or reason">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Status --</option>
                        <option value="proceeding" {{ request('status')=='proceeding'?'selected':'' }}>Proceeding</option>
                        <option value="given_to_customer" {{ request('status')=='given_to_customer'?'selected':'' }}>Given to Customer</option>
                        <option value="waiting_due_date" {{ request('status')=='waiting_due_date'?'selected':'' }}>Waiting Due Date</option>
                        <option value="customer_paid" {{ request('status')=='customer_paid'?'selected':'' }}>Customer Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.customer-loans.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            {{-- Loans Table --}}
            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reference No</th>
                            <th>Customer Name</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Paid Date</th>
                            <th>Amount</th>
                            <th>Bank</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $loan->reference_number }}</td>
                            <td>{{ $loan->customer_name }}</td>
                            <td>{{ $loan->date }}</td>
                            <td>{{ $loan->loan_due_date ?? '-' }}</td>
                            <td>{{ $loan->paid_date ?? '-' }}</td>
                            <td>{{ number_format($loan->amount, 2) }}</td>
                            <td>{{ $loan->bankAccount->bank_name ?? '-' }}</td>
                            <td>{{ Str::limit($loan->reason, 30) }}</td>
                            <td>
                                @php
                                    $statusColors = [
                                        'proceeding'=>'secondary',
                                        'given_to_customer'=>'info',
                                        'waiting_due_date'=>'warning text-dark',
                                        'customer_paid'=>'success'
                                    ];
                                @endphp
                                <span class="badge bg-{{ $statusColors[$loan->status] ?? 'secondary' }}">
                                    {{ str_replace('_',' ', ucfirst($loan->status)) }}
                                </span>
                            </td>
                            <td>
                                {{-- Edit Button --}}
                                <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#editLoanModal{{ $loan->id }}">
                                    <i class="bi bi-pencil"></i>
                                </button>

                                {{-- Update Status Button --}}
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                    data-bs-target="#statusLoanModal{{ $loan->id }}">
                                    <i class="bi bi-arrow-repeat"></i>
                                </button>

                                {{-- SweetAlert Delete Button --}}
                                <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                    data-id="{{ $loan->id }}">
                                    <i class="bi bi-trash"></i>
                                </button>

                                {{-- Delete Form (hidden) --}}
                                <form id="delete-form-{{ $loan->id }}" 
                                    action="{{ route('admin.customer-loans.destroy', $loan->id) }}" 
                                    method="POST" style="display:none;">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editLoanModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <form action="{{ route('admin.customer-loans.update', $loan->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Edit Loan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body row g-3">
                                            <div class="col-md-6">
                                                <label>Reference Number</label>
                                                <input type="text" name="reference_number" value="{{ $loan->reference_number }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label>Customer Name</label>
                                                <input type="text" name="customer_name" value="{{ $loan->customer_name }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Date</label>
                                                <input type="date" name="date" value="{{ $loan->date }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label>Due Date</label>
                                                <input type="date" name="loan_due_date" value="{{ $loan->loan_due_date }}" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Paid Date</label>
                                                <input type="date" name="paid_date" value="{{ $loan->paid_date }}" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label>Amount</label>
                                                <input type="number" step="0.01" name="amount" value="{{ $loan->amount }}" class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label>From Bank Account</label>
                                                <select name="from_bank_account_id" class="form-select">
                                                    <option value="">-- None --</option>
                                                    @foreach($bankAccounts as $bank)
                                                        <option value="{{ $bank->id }}" {{ $loan->from_bank_account_id == $bank->id ? 'selected' : '' }}>
                                                            {{ $bank->bank_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label>Reason</label>
                                                <textarea name="reason" class="form-control">{{ $loan->reason }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary">Update Loan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Status Update Modal --}}
                        <div class="modal fade" id="statusLoanModal{{ $loan->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.customer-loans.updateStatus', $loan->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header">
                                            <h5 class="modal-title"><i class="bi bi-arrow-repeat me-2"></i> Update Status</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Status</label>
                                                <select name="status" class="form-select" required>
                                                    <option value="proceeding" {{ $loan->status=='proceeding'?'selected':'' }}>Proceeding</option>
                                                    <option value="given_to_customer" {{ $loan->status=='given_to_customer'?'selected':'' }}>Given to Customer</option>
                                                    <option value="waiting_due_date" {{ $loan->status=='waiting_due_date'?'selected':'' }}>Waiting Due Date</option>
                                                    <option value="customer_paid" {{ $loan->status=='customer_paid'?'selected':'' }}>Customer Paid</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Paid Date (Optional)</label>
                                                <input type="date" name="paid_date" value="{{ $loan->paid_date }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-success">Update Status</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="11" class="text-center">No loans found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $loans->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Create Loan Modal --}}
<div class="modal fade" id="createLoanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.customer-loans.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Add New Loan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-6">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label>Due Date</label>
                        <input type="date" name="loan_due_date" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label>Paid Date (Optional)</label>
                        <input type="date" name="paid_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label>Amount</label>
                        <input type="number" step="0.01" name="amount" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label>From Bank Account</label>
                        <select name="from_bank_account_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($bankAccounts as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->bank_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label>Reason</label>
                        <textarea name="reason" class="form-control"></textarea>
                    </div>
                    <div class="col-12">
                        <label>Status</label>
                        <select name="status" class="form-select" required>
                            <option value="proceeding">Proceeding</option>
                            <option value="given_to_customer">Given to Customer</option>
                            <option value="waiting_due_date">Waiting Due Date</option>
                            <option value="customer_paid">Customer Paid</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Save Loan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.btn-delete').forEach(btn => {
    btn.addEventListener('click', function () {
        const id = this.getAttribute('data-id');
        Swal.fire({
            title: "Are you sure?",
            text: "This loan will be permanently deleted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6"
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    });
});
</script>
@endpush
