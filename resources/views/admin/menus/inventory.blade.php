@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
    <div class="container my-5">
        <h4 class="mb-4">Inventory Dashboard</h4>

        <div class="row row-cols-1 row-cols-md-3 g-4">

            <!-- BOM Stocks -->
            <div class="col">
                <a href="{{ route('admin.bom-stocks.table') }}" class="text-decoration-none open-modal"
                    data-title="BOM Stocks">
                    <div
                        class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                        <i data-feather="archive" class="text-warning mb-3"></i>
                        <h6 class="fw-bold">BOM Stocks</h6>
                        <p class="text-muted small mb-0">Track BOM stock availability</p>
                    </div>
                </a>
            </div>
            <!-- BOM Stock Adjustment -->
            <div class="col">
                <a href="{{ route('admin.bom-stock-adjustments.table') }}" class="text-decoration-none open-modal"
                    data-title="BOM Stock Adjustment">
                    <div
                        class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                        <i data-feather="package" class="text-warning mb-3"></i> <!-- Changed from archive to package -->
                        <h6 class="fw-bold">BOM Stock Adjustment</h6>
                        <p class="text-muted small mb-0">BOM Adjust stock quantities</p>
                    </div>
                </a>
            </div>


            <!-- Inventory Management -->
            <div class="col">
                <a href="{{ route('admin.stocks.table') }}" class="text-decoration-none open-modal"
                    data-title="Inventory Management">
                    <div
                        class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                        <i data-feather="archive" class="text-primary mb-3"></i>
                        <h6 class="fw-bold">Inventory</h6>
                        <p class="text-muted small mb-0">Manage all stock items</p>
                    </div>
                </a>
            </div>

            <!-- Stock Adjustment -->
            <div class="col">
                <a href="{{ route('admin.stock-adjustments.table') }}" class="text-decoration-none open-modal"
                    data-title="Stock Adjustment">
                    <div
                        class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                        <i data-feather="edit-3" class="text-success mb-3"></i>
                        <h6 class="fw-bold">Stock Adjustment</h6>
                        <p class="text-muted small mb-0">Adjust stock quantities</p>
                    </div>
                </a>
            </div>

            <!-- Stock Alert -->
            <div class="col">
                <a href="#" class="text-decoration-none">
                    <div
                        class="card h-100 shadow-sm text-center p-3 d-flex flex-column align-items-center justify-content-center">
                        <i data-feather="bell" class="text-danger mb-3"></i>
                        <h6 class="fw-bold">Stock Alert</h6>
                        <p class="text-muted small mb-0">View low stock warnings</p>
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

        // Universal modal for all inventory tables
        $('.open-modal').on('click', function(e) {
            e.preventDefault();

            const url = $(this).attr('href');
            const title = $(this).data('title');

            $('#universalModal .modal-title').text(title);
            $('#universalModalBody').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            $('#universalModal').modal('show');

            $.get(url, function(data) {
                $('#universalModalBody').html(data);
            });
        });
    </script>
@endsection
