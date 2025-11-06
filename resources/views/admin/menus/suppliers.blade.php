@extends('layouts.app')

@section('title', 'Suppliers Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Suppliers Management</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Create Supplier -->
        <div class="col">
            <a href="{{ route('admin.suppliers.create') }}" class="text-decoration-none" data-title="Create Supplier">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="user-plus" class="text-primary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Supplier</h6>
                    <p class="text-muted small mb-0">Add a new supplier</p>
                </div>
            </a>
        </div>
        <!-- View Suppliers -->
        <div class="col">
            <a href="{{ route('admin.suppliers.table') }}" class="text-decoration-none open-modal" data-title="Suppliers Table">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="users" class="text-dark mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Suppliers</h6>
                    <p class="text-muted small mb-0">View all suppliers</p>
                </div>
            </a>
        </div>



        <!-- Supplier Payments -->
        <div class="col">
            <a href="{{ route('admin.supplier-payments.index') }}" class="text-decoration-none" data-title="Supplier Payments">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="credit-card" class="text-success mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Supplier Payments</h6>
                    <p class="text-muted small mb-0">View all supplier payments</p>
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
