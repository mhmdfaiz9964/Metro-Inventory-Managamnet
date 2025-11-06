@extends('layouts.app')

@section('title', 'BOM Management')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">BOM Management</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">
        
        <!-- Create BOM Products (Full Page) -->
        <div class="col">
            <a href="{{ route('admin.bom.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="edit-3" class="text-success mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create BOM Products</h6>
                    <p class="text-muted small mb-0">Add new BOM product structures</p>
                </div>
            </a>
        </div>


        <!-- BOM Management (Modal) -->
        <div class="col">
            <a href="{{ route('admin.bom.table') }}" class="text-decoration-none open-modal" data-title="BOM Management">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="archive" class="text-primary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">BOM Management</h6>
                    <p class="text-muted small mb-0">Manage bills of materials</p>
                </div>
            </a>
        </div>


        <!-- BOM Stocks (Modal) -->
        <div class="col">
            <a href="{{ route('admin.bom-stocks.table') }}" class="text-decoration-none open-modal" data-title="BOM Stocks">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="layers" class="text-warning mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">BOM Stocks</h6>
                    <p class="text-muted small mb-0">Track BOM stock availability</p>
                </div>
            </a>
        </div>
        <!-- Create Manufactures (Full Page) -->
        <div class="col">
            <a href="{{ route('admin.manufactures.create') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="plus-circle" class="text-info mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Create Manufactures</h6>
                    <p class="text-muted small mb-0">Add new manufacture entries</p>
                </div>
            </a>
        </div>


        <!-- Manufactures (Modal) -->
        <div class="col">
            <a href="{{ route('admin.manufactures.table') }}" class="text-decoration-none open-modal" data-title="Manufactures">
                <div class="card h-100 shadow-sm text-center d-flex flex-column align-items-center justify-content-center p-3">
                    <i data-feather="cpu" class="text-danger mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Manufactures</h6>
                    <p class="text-muted small mb-0">View manufacture records</p>
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
