@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <div class="container my-5">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Customers</h4>
                <a href="{{ route('admin.customers.create') }}" class="btn btn-success">Add Customer</a>
            </div>

            <div class="card-body">
                <form method="GET" action="{{ route('admin.customers.index') }}" class="row g-2 mb-3">
                    <div class="col-md-8">
                        <input type="text" name="search" value="{{ $search ?? '' }}" class="form-control"
                            placeholder="Search by name, email, or mobile"
                            onkeypress="if(event.keyCode==13){this.form.submit();}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ route('admin.customers.index') }}" class="btn btn-secondary">Clear</a>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Mobile Number 2</th>
                                <th>Email</th>
                                <th>Balance Due</th>
                                <th>Total Paid</th>
                                <th>Credit Limit</th>
                                <th>Note</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customers as $customer)
                                <tr>
                                    <td>{{ $customer->id }}</td>
                                    <td>{{ $customer->name }}</td>
                                    <td>{{ $customer->mobile_number }}</td>
                                    <td>{{ $customer->mobile_number_2 ?? 'NOT ADDED' }}</td>
                                    <td>{{ $customer->email }}</td>

                                    {{-- Plain Amounts --}}
                                    <td>{{ number_format($customer->balance_due ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->total_paid ?? 0, 2) }}</td>
                                    <td>{{ number_format($customer->credit_limit ?? 0, 2) }}</td>

                                    <td>{{ $customer->note }}</td>

                                    {{-- Actions --}}
                                    <td class="text-center">
                                        <a href="{{ route('admin.customers.edit', $customer->id) }}"
                                            class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="{{ route('admin.customers.history', $customer->id) }}"
                                            class="btn btn-sm btn-info" title="View History" target="_blank">
                                            <i class="bi bi-clock-history"></i>
                                        </a>

                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteCustomer({{ $customer->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        <form id="delete-form-{{ $customer->id }}"
                                            action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST"
                                            style="display:none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No customers found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($customers->hasPages())
                    <div class="mt-3 d-flex justify-content-center">
                        {{ $customers->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function deleteCustomer(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This customer will be deleted!",
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
@endsection
