@extends('layouts.app')

@section('title', 'Products Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Products Management</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">
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
        <!-- Create Product Categories -->
        <div class="col">
            <a href="{{ route('admin.product-categories.create') }}" class="text-decoration-none" data-title="Product Categories">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="folder-plus" class="text-secondary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Product Categories</h6>
                    <p class="text-muted small mb-0">Create products by category</p>
                </div>
            </a>
        </div>
        <!-- Product Categories (Modal) -->
        <div class="col">
            <a href="{{ route('admin.product-categories.table') }}" class="text-decoration-none open-modal" data-title="Product Categories">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="grid" class="text-secondary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Product Categories</h6>
                    <p class="text-muted small mb-0">Organize products by category</p>
                </div>
            </a>
        </div>
        <!-- Create Products (Full Page) -->
        <div class="col">
            <a href="{{ route('admin.products.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="plus-square" class="text-primary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Products</h6>
                    <p class="text-muted small mb-0">Add new products to system</p>
                </div>
            </a>
        </div>

        <!-- All Products (Modal) -->
        <div class="col">
            <a href="{{ route('admin.products.table') }}" class="text-decoration-none open-modal" data-title="All Products">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="package" class="text-dark mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">All Products</h6>
                    <p class="text-muted small mb-0">View and manage products</p>
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
