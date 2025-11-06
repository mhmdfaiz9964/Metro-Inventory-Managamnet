@extends('layouts.app')

@section('content')
    <div class="container my-5">
        <div class="card shadow-lg border-0 rounded-3">
            <div
                class="card-header d-flex justify-content-between align-items-center bg-primary text-white border-bottom-0 rounded-top">
                <h4 class="mb-0 text-white"><i class="bi bi-eye me-2 text-white"></i>Sale Details</h4>
                <a href="{{ route('admin.sales.index') }}" class="btn btn-outline-light btn-sm text-white">
                    <i class="bi bi-arrow-left me-1 text-white"></i>Back
                </a>
            </div>

            <div class="card-body p-4">
                <!-- Sale Information -->
                <div class="mb-5">
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-2"></i>Sale Information</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Sale ID:</strong> {{ $sale->id }}</p>
                            <p class="mb-2"><strong>Salesperson:</strong> {{ $sale->salesperson->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Sale Date:</strong>
                                {{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}</p>
                            <p class="mb-2"><strong>Total Amount:</strong> Rs. {{ number_format($sale->total_amount, 2) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Products Table -->
                <div class="table-responsive mb-5">
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-cart me-2"></i>Products</h5>
                    <table class="table table-bordered table-striped align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Sale Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->product->name ?? 'Deleted Product' }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>Rs. {{ number_format($item->sale_price, 2) }}</td>
                                    <td>Rs. {{ number_format($item->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="4" class="text-end">Total Amount:</th>
                                <th>Rs. {{ number_format($sale->total_amount, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Payment Summary -->
                <div class="bg-light p-4 rounded-3 shadow-sm">
                    <h5 class="fw-bold text-primary mb-3"><i class="bi bi-wallet2 me-2"></i>Payment Summary</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <p class="mb-2">
                                <strong>Payment Method:</strong>
                                @if ($sale->payments->first()?->payment_method)
                                    <span class="badge bg-success text-uppercase">
                                        {{ strtoupper($sale->payments->first()->payment_method) }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </p>
                            <p class="mb-2">
                                <strong>Bank Account:</strong>
                                @if ($sale->payments->first()?->bank)
                                    <span class="badge bg-info text-uppercase">
                                        {{ strtoupper($sale->payments->first()->bank->bank_name ?? '-') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-2"><strong>Paid By:</strong> {{ $sale->payments->first()?->paid_by ?? '-' }}</p>
                            <p class="mb-2"><strong>Paid Date:</strong>
                                {{ $sale->payments->first()?->paid_date ? \Carbon\Carbon::parse($sale->payments->first()->paid_date)->format('Y-m-d') : '-' }}
                            </p>
                            <p class="mb-2"><strong>Amount Paid:</strong> Rs.
                                {{ number_format($sale->payments->first()?->payment_paid ?? 0, 2) }}</p>
                            <p class="mb-2"><strong>Discount:</strong> Rs.
                                {{ number_format($sale->payments->first()?->discount ?? 0, 2) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Print PDF Button -->
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('admin.sales.print', $sale->id) }}" class="btn btn-primary btn-lg" target="_blank">
                        <i class="bi bi-printer me-2"></i>Print as PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card-body p {
            font-size: 0.95rem;
            color: #333;
        }

        .table thead th {
            background-color: #e9ecef;
            color: #333;
        }

        .badge {
            font-size: 0.85rem;
            padding: 0.5em 1em;
        }

        .table-primary {
            background-color: #007bff !important;
            color: white !important;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
@endsection
