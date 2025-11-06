@extends('layouts.app')

@section('title', 'Purchase Management')

@section('content')
    <div class="container my-5">
        <h4 class="mb-4">Purchase Dashboard</h4>

        <div class="row row-cols-1 row-cols-md-4 g-4">
            <!-- Make Purchase -->
            <div class="col">
                <a href="{{ route('admin.purchases.create') }}" class="text-decoration-none">
                    <div
                        class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                        <i data-feather="plus-circle" class="text-success mb-3" width="40" height="40"></i>
                        <h6 class="fw-bold">Make Purchase</h6>
                        <p class="text-muted small mb-0">Add a new purchase</p>
                    </div>
                </a>
            </div>

            <!-- All Purchases -->
            <div class="col">
                <a href="{{ route('admin.purchases.table') }}" class="text-decoration-none open-modal"
                    data-title="All Purchases">
                    <div
                        class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                        <i data-feather="shopping-bag" class="text-primary mb-3" width="40" height="40"></i>
                        <h6 class="fw-bold">All Purchases</h6>
                        <p class="text-muted small mb-0">View all purchases</p>
                    </div>
                </a>
            </div>



            <!-- Purchase Invoices -->
            <div class="col">
                <a href="{{ route('admin.invoices.purchases') }}" class="text-decoration-none open-modal"
                    data-title="Purchase Invoices">
                    <div
                        class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                        <i data-feather="file-text" class="text-warning mb-3" width="40" height="40"></i>
                        <h6 class="fw-bold">Purchase Invoices</h6>
                        <p class="text-muted small mb-0">View purchase invoices</p>
                    </div>
                </a>
            </div>

            <!-- Purchase Returns -->
            <div class="col">
                <a href="{{ route('admin.purchase-returns.table') }}" class="text-decoration-none open-modal"
                    data-title="Purchase Returns">
                    <div
                        class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                        <i data-feather="corner-down-left" class="text-danger mb-3" width="40" height="40"></i>
                        <h6 class="fw-bold">Purchase Returns</h6>
                        <p class="text-muted small mb-0">View all purchase returns</p>
                    </div>
                </a>
            </div>

            <!-- Make Manufactured Product Purchase -->
            <div class="col">
                <a href="{{ route('admin.purchase_manufactured_products.create') }}" class="text-decoration-none">
                    <div
                        class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                        <i data-feather="box" class="text-success mb-3" width="40" height="40"></i>
                        <h6 class="fw-bold">Manufactured Purchase</h6>
                        <p class="text-muted small mb-0">Add a new manufactured product purchase</p>
                    </div>
                </a>
            </div>

            <!-- All Manufactured Product Purchases -->
            <div class="col">
                <a href="{{ route('admin.purchase_manufactured_products.index') }}" class="text-decoration-none"
                    data-title="All Manufactured Purchases">
                    <div
                        class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                        <i data-feather="layers" class="text-primary mb-3" width="40" height="40"></i>
                        <h6 class="fw-bold">All Manufactured Purchases</h6>
                        <p class="text-muted small mb-0">View all manufactured product purchases</p>
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
            $('#universalModalBody').html(
                '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
            $('#universalModal').modal('show');

            $.get(url, function(data) {
                $('#universalModalBody').html(data);
            });
        });
    </script>
@endsection
