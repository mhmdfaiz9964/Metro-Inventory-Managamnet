@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="row">
        <div class="col-12">
            {{-- Main Card --}}
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Users</h4>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-success">Add User</a>
                </div>

                <div class="card-body">
                    {{-- Search Form --}}
                    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search users..." onkeypress="if(event.keyCode==13){this.form.submit();}">
                            <button class="btn btn-primary" type="submit">Search</button>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                        </div>
                    </form>

                    {{-- Users Table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name </th>
                                    <th>Roles</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Created At</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>
                                        <strong>{{ $user->name }}</strong><br>
                                    </td>
                                    <td>

                                        @foreach($user->roles as $role)
                                            <span class="badge bg-{{ $role->name == 'Admin' ? 'danger' : ($role->name == 'User' ? 'info' : 'secondary') }} me-1">
                                                @if($role->name == 'Admin')
                                                    <i class="bi bi-shield-lock-fill me-1"></i>
                                                @elseif($role->name == 'User')
                                                    <i class="bi bi-person-badge-fill me-1"></i>
                                                @else
                                                    <i class="bi bi-person-fill me-1"></i>
                                                @endif
                                                {{ ucfirst($role->name) }}
                                            </span>
                                        @endforeach
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->mobile_number ?? '-' }}</td>
                                    <td>{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="deleteUser({{ $user->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        {{-- Delete Form --}}
                                        <form id="delete-form-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No users found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Pagination --}}
                @if($users->hasPages())
                <div class="card-footer d-flex justify-content-center">
                    {{ $users->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function deleteUser(id) {
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
@endsection
