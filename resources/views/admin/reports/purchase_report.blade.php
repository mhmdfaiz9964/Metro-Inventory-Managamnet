@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">BOM Components Purchase Report</h4>
            <a href="{{ route('admin.purchase.report.pdf', request()->query()) }}" target="_blank" class="btn btn-success">Export PDF</a>
        </div>
        <div class="card-body">

            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.purchase.report.index') }}" class="row g-3 mb-4">
                <div class="col-md-3">
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="bom_component_id" class="form-select">
                        <option value="">All BOM Components</option>
                        @foreach(\App\Models\BomComponent::all() as $bom)
                            <option value="{{ $bom->id }}" {{ request('bom_component_id') == $bom->id ? 'selected' : '' }}>
                                {{ $bom->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" placeholder="From Date">
                </div>

                <div class="col-md-2">
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" placeholder="To Date">
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.purchase.report.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>

            {{-- Summary Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Supplier</th>
                            <th>BOM Component</th>
                            <th>Purchase Date</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Cost Price</th>
                            <th class="text-end">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $counter = 1; @endphp
                        @foreach($purchases as $purchase)
                            @foreach($purchase->bomProducts as $bom)
                                @if(request('bom_component_id') && request('bom_component_id') != $bom->bom_id)
                                    @continue
                                @endif
                                <tr>
                                    <td>{{ $counter++ }}</td>
                                    <td>{{ $purchase->supplier?->name ?? '-' }}</td>
                                    <td>{{ $bom->bomComponent?->name ?? '-' }}</td>
                                    <td>{{ $purchase->purchase_date->format('Y-m-d') }}</td>
                                    <td class="text-end">{{ $bom->qty }}</td>
                                    <td class="text-end">{{ number_format($bom->cost_price, 2) }}</td>
                                    <td class="text-end">{{ number_format($bom->qty * $bom->cost_price, 2) }}</td>
                                </tr>
                            @endforeach
                        @endforeach
                        @if($counter === 1)
                            <tr>
                                <td colspan="7" class="text-center text-muted">No BOM purchases found.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            {{-- Chart --}}
            <div class="card mb-3">
                <div class="card-header">BOM Components Purchase Summary</div>
                <div class="card-body">
                    <canvas id="bomChart" height="100"></canvas>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('bomChart').getContext('2d');

    // Aggregate BOM data for chart
    const bomLabels = @json($purchases->flatMap(fn($p) => $p->bomProducts)->map(fn($b) => $b->bomComponent?->name ?? 'Unknown'));
    const bomData = @json($purchases->flatMap(fn($p) => $p->bomProducts)->map(fn($b) => $b->qty * $b->cost_price));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: bomLabels,
            datasets: [{
                label: 'Purchase Amount (Rs)',
                data: bomData,
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: true },
                tooltip: { enabled: true }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Amount (Rs)' }
                },
                x: {
                    title: { display: true, text: 'BOM Component' }
                }
            }
        }
    });
</script>
@endsection
