        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Bank Accounts</h4>
                <a href="{{ route('admin.bank-accounts.create') }}" class="btn btn-success">Add Account</a>
            </div>

            <div class="card-body">
                {{-- Search & Filter --}}
                <form method="GET" action="{{ route('admin.bank-accounts.index') }}" class="row g-2 mb-3">
                    <div class="col-md-5">
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                            placeholder="Search by owner or bank" onkeypress="if(event.keyCode==13){this.form.submit();}">
                    </div>
                    <div class="col-md-4">
                        <select name="bank" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Filter by Bank --</option>
                            @foreach ($banks as $bank)
                                <option value="{{ $bank }}" {{ request('bank') == $bank ? 'selected' : '' }}>
                                    {{ $bank }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex">
                        <button type="submit" class="btn btn-primary me-2">Search</button>
                        <a href="{{ route('admin.bank-accounts.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Owner Name</th>
                                <th>Bank</th>
                                <th>Account Number</th>
                                <th>Balance</th>
                                <th>Branch</th>
                                <th>Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>{{ $account->id }}</td>
                                    <td>{{ $account->owner_name }}</td>
                                    <td>{{ $account->bank_name }}</td>
                                    <td>{{ $account->account_number }}</td>
                                    <td>{{ $account->bank_balance }}</td>
                                    <td>{{ $account->branch_name }}</td>
                                    <td>
                                        @if ($account->status == 'active')
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="bi bi-x-circle me-1"></i> Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <td class="text-center">
                                        <a href="{{ route('admin.bank-accounts.edit', $account->id) }}"
                                            class="btn btn-sm btn-primary" title="Edit"><i
                                                class="bi bi-pencil-square"></i></a>

                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteAccount({{ $account->id }})" title="Delete"><i
                                                class="bi bi-trash"></i></button>

                                        <form id="delete-form-{{ $account->id }}"
                                            action="{{ route('admin.bank-accounts.destroy', $account->id) }}"
                                            method="POST" style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No bank accounts found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($accounts->hasPages())
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $accounts->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
        function deleteAccount(id) {
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