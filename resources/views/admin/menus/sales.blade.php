@extends('layouts.app')

@section('title', 'Sales Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Sales Dashboard</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">

    
        <!-- Sales Section -->
        <div class="col">
            <a href="{{ route('admin.sales.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <i data-feather="shopping-cart" class="text-danger mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Sales</h6>
                    <p class="text-muted small mb-0">Add new sales entry</p>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="{{ route('admin.sales.table') }}" class="text-decoration-none open-modal" data-title="All Sales">
                <div class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <i data-feather="list" class="text-primary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">All Sales</h6>
                    <p class="text-muted small mb-0">View all sales</p>
                </div>
            </a>
        </div>

        <div class="col">
            <a href="{{ route('admin.invoices.sales') }}" class="text-decoration-none open-modal" data-title="Sales Invoices">
                <div class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <i data-feather="file-text" class="text-success mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Sales Invoices</h6>
                    <p class="text-muted small mb-0">View sales invoices</p>
                </div>
            </a>
        </div>
        <!-- Sales Return -->
        <div class="col">
            <a href="{{ route('admin.sales-return.table') }}" class="text-decoration-none open-modal" data-title="Sales Return">
                <div class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <i data-feather="corner-down-left" class="text-warning mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Sales Return</h6>
                    <p class="text-muted small mb-0">Manage returned sales</p>
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

// Universal modal for all index/table views
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
