@extends('layouts.app')

@section('title', 'Customers Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Customers Management</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        <!-- Create Customer -->
        <div class="col">
            <a href="{{ route('admin.customers.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="user-plus" class="text-primary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Customer</h6>
                    <p class="text-muted small mb-0">Add a new customer</p>
                </div>
            </a>
        </div>

        <!-- Customers -->
        <div class="col">
            <a href="{{ route('admin.customers.table') }}" class="text-decoration-none open-modal" data-title="Customers">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="users" class="text-dark mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Customers</h6>
                    <p class="text-muted small mb-0">View all customers</p>
                </div>
            </a>
        </div>

        <!-- Customer Payments -->
        <div class="col">
            <a href="{{ route('admin.customers-payments.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="credit-card" class="text-success mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Customer Payments</h6>
                    <p class="text-muted small mb-0">View all customer payments</p>
                </div>
            </a>
        </div>

        <!-- ✅ Customer Ledger -->
        <div class="col">
            <a href="{{ route('admin.customers.ledger') }}" class="text-decoration-none open-ledger-modal" data-title="Customer Ledger">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="book-open" class="text-info mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Customer Ledger</h6>
                    <p class="text-muted small mb-0">View customer ledger history</p>
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

// Default modal (for Customers, etc.)
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

// ✅ Ledger modal (select customer to redirect)
$('.open-ledger-modal').on('click', function(e) {
    e.preventDefault();
    const url = $(this).attr('href');
    const title = $(this).data('title');
    $('#universalModal .modal-title').text(title);
    $('#universalModalBody').html('<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
    $('#universalModal').modal('show');

    $.get(url, function(data) {
        $('#universalModalBody').html(data);

        // When clicking on customer row
        $('.select-customer').on('click', function() {
            const customerId = $(this).data('id');
            const redirectUrl = `/admin/customers/${customerId}/history`;
            window.location.href = redirectUrl;
        });
    });
});
</script>
@endsection
