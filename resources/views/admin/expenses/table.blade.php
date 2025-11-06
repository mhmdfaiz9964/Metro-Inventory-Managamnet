    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Expenses</h4>
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-success">Add Expense</a>
        </div>

        <div class="card-body">
            {{-- Search & Filter --}}
            <form method="GET" action="{{ route('admin.expenses.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                        placeholder="Search by reason or paid by" onkeypress="if(event.keyCode==13){this.form.submit();}">
                </div>
                <div class="col-md-4">
                    <select name="bank" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Filter by Bank --</option>
                        @foreach ($banks as $bank)
                            <option value="{{ $bank->id }}" {{ request('bank') == $bank->id ? 'selected' : '' }}>
                                {{ $bank->bank_name }} - {{ $bank->account_number }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex">
                    <button type="submit" class="btn btn-primary me-2">Search</button>
                    <a href="{{ route('admin.expenses.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Reason</th>
                            <th>Date</th>
                            <th>Bank</th>
                            <th>Amount</th>
                            <th>Paid By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr>
                                <td>{{ $expense->id }}</td>
                                <td>{{ $expense->reason }}</td>
                                <td>{{ $expense->date }}</td>
                                <td>{{ $expense->bankAccount->bank_name ?? '' }}</td>
                                <td>{{ number_format($expense->amount,2) }}</td>
                                <td>{{ $expense->paid_by }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="deleteExpense({{ $expense->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <form id="delete-form-{{ $expense->id }}" action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" style="display:none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No expenses found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($expenses->hasPages())
                <div class="mt-3 d-flex justify-content-center">
                    {{ $expenses->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteExpense(id) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
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