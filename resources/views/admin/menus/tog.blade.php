@extends('layouts.app')

@section('title', 'TOG Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Transfers of Goods (TOG)</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- Create Warehouse -->
        <div class="col">
            <a href="{{ route('admin.warehouses.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="plus-square" class="text-success mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Warehouse</h6>
                    <p class="text-muted small mb-0">Add a new warehouse</p>
                </div>
            </a>
        </div>

        <!-- All Warehouses -->
        <div class="col">
            <a href="{{ route('admin.warehouses.index') }}" class="text-decoration-none" data-title="All Warehouses">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="home" class="text-primary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">All Warehouses</h6>
                    <p class="text-muted small mb-0">View and manage warehouses</p>
                </div>
            </a>
        </div>

        <!-- Create Transfer -->
        <div class="col">
            <a href="{{ route('admin.transfers.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="arrow-right-circle" class="text-success mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Transfer</h6>
                    <p class="text-muted small mb-0">Add a new transfer of goods</p>
                </div>
            </a>
        </div>

        <!-- All Transfers -->
        <div class="col">
            <a href="{{ route('admin.transfers.index') }}" class="text-decoration-none" data-title="All Transfers">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="layers" class="text-warning mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">All Transfers</h6>
                    <p class="text-muted small mb-0">View and manage all transfers</p>
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

// Universal modal for all table views
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
