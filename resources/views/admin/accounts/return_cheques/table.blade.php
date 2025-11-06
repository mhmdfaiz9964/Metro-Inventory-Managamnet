    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Return Cheques</h4>
                {{-- <a href="{{ route('admin.return-cheques.create') }}" class="btn btn-success">Add Return Cheque</a> --}}
            </div>

            <div class="card-body">
                {{-- Filters --}}
                <form method="GET" action="{{ route('admin.return-cheques.index') }}" class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input type="text" name="cheque_no" value="{{ request('cheque_no') }}" class="form-control"
                            placeholder="Cheque No">
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="return_cheque_no" value="{{ request('return_cheque_no') }}"
                            class="form-control" placeholder="Return Cheque No">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control">
                    </div>
                    <div class="col-md-2 d-flex">
                        <button type="submit" class="btn btn-primary me-2">Filter</button>
                        <a href="{{ route('admin.return-cheques.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>

                {{-- Table --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Cheque No</th>
                                <th>Return Cheque No</th>
                                <th>Return Date</th>
                                <th>Amount</th>
                                <th>Bank</th>
                                <th>Reason</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returnCheques as $rc)
                                <tr>
                                    <td>{{ $rc->id }}</td>
                                    <td>{{ $rc->cheque_no }}</td>
                                    <td>{{ $rc->return_cheque_no }}</td>
                                    <td>{{ $rc->return_date->format('Y-m-d') }}</td>
                                    <td>{{ number_format($rc->amount, 2) }}</td>
                                    <td>{{ $rc->bank->bank_name ?? 'N/A' }}</td>
                                    <td>{{ $rc->return_reason }}</td>
                                    <td class="text-center">
                                        {{-- <a href="{{ route('admin.return-cheques.edit', $rc->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                <form action="{{ route('admin.return-cheques.destroy', $rc->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                </form> --}}
                                        <div class="alert alert-warning mt-2 p-1 text-center" role="alert"
                                            style="font-size: 0.8rem;">
                                            Actions temporarily disabled
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No return cheques found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if ($returnCheques->hasPages())
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $returnCheques->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>