@extends('layouts.app')

@section('title', 'User & Supplier Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Management Dashboard</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <div class="col">
            <a href="{{ route('admin.users.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <div class="icon-wrapper mb-3 d-flex align-items-center justify-content-center">
                        <i data-feather="user-plus" class="text-success"></i>
                    </div>
                    <h6 class="fw-bold">Create User</h6>
                    <p class="text-muted small mb-0">Add new user</p>
                </div>
            </a>
        </div>
        <!-- Users Section -->
        <div class="col">
            <a href="{{ route('admin.users.table') }}" class="text-decoration-none open-modal" data-title="All Users">
                <div class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                    <i data-feather="users" class="text-primary mb-3"></i>
                    <h6 class="fw-bold">All Users</h6>
                    <p class="text-muted small mb-0">View all users</p>
                </div>
            </a>
        </div>



    </div>
</div>

<!-- Universal Modal -->
@include('layouts.model')
@endsection

@section('scripts')
<script>
feather.replace();

// Universal modal for all sections
$('.open-modal').on('click', function(e) {
    e.preventDefault();

    const url = $(this).attr('href');
    const title = $(this).data('title');

    $('#universalModal .modal-title').text(title);
    $('#universalModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
    $('#universalModal').modal('show');

    $.get(url, function(data) {
        $('#universalModalBody').html(data);
    });
});
</script>
@endsection
