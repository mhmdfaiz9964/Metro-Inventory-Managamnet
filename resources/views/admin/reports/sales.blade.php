@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Sales Report</h4>
            <a href="{{ route('admin.purchase.report.pdf', request()->query()) }}" target="_blank" class="btn btn-success">Export PDF</a>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.sales.report.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <select name="salesperson_id" class="form-select">
                        <option value="">All Salespersons</option>
                        @foreach(\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ request('salesperson_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.sales.report.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            {{-- Sales Chart --}}
            <div class="mb-4">
                <canvas id="salesChart" height="100"></canvas>
            </div>

            {{-- Summary Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Salesperson</th>
                            <th>Date</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Payments Received</th>
                            <th class="text-end">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $index => $sale)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $sale->salesperson?->name ?? '-' }}</td>
                            <td>{{ $sale->sale_date->format('Y-m-d') }}</td>
                            <td class="text-end">{{ number_format($sale->totalAmount(), 2) }}</td>
                            <td class="text-end">{{ number_format($sale->payments->sum('payment_amount'), 2) }}</td>
                            <td class="text-end">{{ number_format($sale->totalAmount() - $sale->payments->sum('payment_amount'), 2) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No sales found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Detailed Sale Items --}}
            @foreach($sales as $sale)
            <div class="card mb-3">
                <div class="card-header">
                    Sale #{{ $sale->id }} | {{ $sale->salesperson?->name ?? '-' }} | Date: {{ $sale->sale_date->format('Y-m-d') }}
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th class="text-end">Quantity</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->items as $item)
                                <tr>
                                    <td>{{ $item->product?->name ?? '-' }}</td>
                                    <td class="text-end">{{ $item->quantity }}</td>
                                    <td class="text-end">{{ number_format($item->sale_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6>Payments Received</h6>
                    <ul class="list-group">
                        @forelse($sale->payments as $payment)
                        <li class="list-group-item">
                            {{ $payment->payment_method }} | Rs. {{ number_format($payment->payment_amount, 2) }} | Paid by: {{ $payment->paid_by }} | Date: {{ $payment->paid_date }}
                        </li>
                        @empty
                        <li class="list-group-item text-muted">No payments received</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart').getContext('2d');

    // Prepare data for chart
    const salesData = @json($sales->groupBy(fn($s) => $s->salesperson?->name ?? 'Unknown')->map(fn($group) => $group->sum(fn($s) => $s->totalAmount())));
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(salesData),
            datasets: [{
                label: 'Total Sales (Rs)',
                data: Object.values(salesData),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                title: { display: true, text: 'Sales by Salesperson' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection
