@extends('layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
<div class="container my-5">
    <h4 class="mb-4">Reports Dashboard</h4>

    <div class="row row-cols-1 row-cols-md-3 g-4">

        <!-- Purchase Report -->
        <div class="col">
            <a href="{{ route('admin.purchase.report.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <i data-feather="bar-chart-2" class="text-primary mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Purchase Report</h6>
                    <p class="text-muted small mb-0">View all purchase reports</p>
                </div>
            </a>
        </div>

        <!-- Bank Account Report -->
        <div class="col">
            <a href="{{ route('admin.account.report.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <i data-feather="file-text" class="text-success mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Bank Account Report</h6>
                    <p class="text-muted small mb-0">View bank account statements</p>
                </div>
            </a>
        </div>

        <!-- Sales Report -->
        <div class="col">
            <a href="{{ route('admin.sales.report.index') }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm d-flex flex-column align-items-center justify-content-center text-center p-3">
                    <i data-feather="bar-chart-2" class="text-warning mb-3" width="40" height="40"></i>
                    <h6 class="fw-bold">Sales Report</h6>
                    <p class="text-muted small mb-0">View all sales reports</p>
                </div>
            </a>
        </div>

    </div>
</div>
@endsection

