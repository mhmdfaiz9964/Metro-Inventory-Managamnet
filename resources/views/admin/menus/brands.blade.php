@extends('layouts.app')

@section('title', 'Brands Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Brands Management</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- Product Brands Management -->
        <div class="col">
            <a href="{{ route('admin.brands.index') }}" class="text-decoration-none" data-title="Product Brands">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="tag" class="text-secondary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">BOM Brands</h6>
                    <p class="text-muted small mb-0">Manage BOM brands</p>
                </div>
            </a>
        </div>

        <!-- Product Brands (Full Page) -->
        <div class="col">
            <a href="{{ route('admin.product-brand.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="tag" class="text-secondary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Product Brands</h6>
                    <p class="text-muted small mb-0">Manage product brands</p>
                </div>
            </a>
        </div>

        {{-- <!-- Barcodes Management -->
        <div class="col">
            <a href="{{ route('admin.barcodes.index') }}" class="text-decoration-none" data-title="Barcodes Management">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="hash" class="text-info mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Barcodes Management</h6>
                    <p class="text-muted small mb-0">Manage barcodes</p>
                </div>
            </a>
        </div> --}}

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