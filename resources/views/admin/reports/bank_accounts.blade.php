@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-bank me-2"></i>Bank Accounts Report</h4>
            <a href="{{ route('admin.account.report.pdf', request()->query()) }}" target="_blank" class="btn btn-success">
                <i class="bi bi-file-earmark-pdf me-1"></i>Export PDF
            </a>
        </div>

        <div class="card-body">
            {{-- Filter/Search Section --}}
            <form method="GET" action="{{ route('admin.account.report.index') }}" class="row g-3 mb-5">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <input type="text" name="bank_name" value="{{ request('bank_name') }}" class="form-control" placeholder="Bank Name">
                </div>
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <input type="text" name="owner_name" value="{{ request('owner_name') }}" class="form-control" placeholder="Account Owner">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-12">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-lg-4 col-md-12 col-sm-12 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search me-1"></i>Filter</button>
                    <a href="{{ route('admin.account.report.index') }}" class="btn btn-secondary"><i class="bi bi-x-circle me-1"></i>Clear</a>
                </div>
            </form>

            {{-- Summary Table --}}
            <div class="table-responsive mb-5">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Bank</th>
                            <th>Owner</th>
                            <th>Account Number</th>
                            <th class="text-end">Balance (Rs.)</th>
                            <th>Status</th>
                            <th class="text-end">Transactions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bankAccounts as $index => $account)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $account->bank_name }}</td>
                                <td>{{ $account->owner_name }}</td>
                                <td>{{ $account->account_number }}</td>
                                <td class="text-end">{{ number_format($account->bank_balance, 2) }}</td>
                                <td>{{ ucfirst($account->status) }}</td>
                                <td class="text-end">{{ $account->transactionsFrom->count() + $account->transactionsTo->count() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No bank accounts found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Charts Section --}}
            <div class="row mb-5">
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="mb-0">Total Account Balances (Bar)</h6></div>
                        <div class="card-body"><canvas id="balanceChart" style="max-height: 300px;"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="mb-0">Transactions per Account (Bar)</h6></div>
                        <div class="card-body"><canvas id="transactionsChart" style="max-height: 300px;"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="mb-0">Balance Distribution (Pie)</h6></div>
                        <div class="card-body"><canvas id="balancePieChart" style="max-height: 300px;"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="mb-0">Account Status (Doughnut)</h6></div>
                        <div class="card-body"><canvas id="statusChart" style="max-height: 300px;"></canvas></div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-12 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header"><h6 class="mb-0">Debits vs Credits (Bar)</h6></div>
                        <div class="card-body"><canvas id="debitsCreditsChart" style="max-height: 300px;"></canvas></div>
                    </div>
                </div>
            </div>

            {{-- Detailed Account Data --}}
            @foreach($bankAccounts as $account)
                <div class="card mb-4 shadow-sm">
                    <div class="card-header">
                        <h5 class="mb-0">{{ $account->bank_name }} - {{ $account->owner_name }} (Balance: Rs. {{ number_format($account->bank_balance, 2) }})</h5>
                    </div>
                    <div class="card-body">
                        {{-- Transactions Table --}}
                        <h6 class="mb-3">Transactions</h6>
                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Type</th>
                                        <th class="text-end">Amount (Rs.)</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($account->transactionsFrom as $txn)
                                        <tr>
                                            <td>{{ $txn->transaction_id }}</td>
                                            <td>Debit</td>
                                            <td class="text-end">{{ number_format($txn->amount, 2) }}</td>
                                            <td>{{ $txn->fromBank?->owner_name ?? '-' }}</td>
                                            <td>{{ $txn->toBank?->owner_name ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($txn->created_at)->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                    @foreach($account->transactionsTo as $txn)
                                        <tr>
                                            <td>{{ $txn->transaction_id }}</td>
                                            <td>Credit</td>
                                            <td class="text-end">{{ number_format($txn->amount, 2) }}</td>
                                            <td>{{ $txn->fromBank?->owner_name ?? '-' }}</td>
                                            <td>{{ $txn->toBank?->owner_name ?? '-' }}</td>
                                            <td>{{ \Carbon\Carbon::parse($txn->created_at)->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                    @if($account->transactionsFrom->isEmpty() && $account->transactionsTo->isEmpty())
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">No transactions found.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@php
// Prepare data for charts
$owners = [];
$balances = [];
$transactionCounts = [];
$debitSums = [];
$creditSums = [];
$activeCount = 0;
$inactiveCount = 0;

foreach ($bankAccounts as $account) {
    $owners[] = addslashes($account->owner_name);
    $balances[] = floatval($account->bank_balance);
    $transactionCounts[] = $account->transactionsFrom->count() + $account->transactionsTo->count();
    $debitSums[] = floatval($account->transactionsFrom->sum('amount'));
    $creditSums[] = floatval($account->transactionsTo->sum('amount'));
    if ($account->status === 'active') $activeCount++;
    if ($account->status === 'inactive') $inactiveCount++;
}
@endphp

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Common chart options
    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 1000, // Smooth animation duration
            easing: 'easeOutQuart'
        },
        plugins: {
            legend: { position: 'top' },
            title: { display: true, font: { size: 16 } }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Amount (Rs.)' }
            },
            x: {
                ticks: { autoSkip: false, maxRotation: 45, minRotation: 45 }
            }
        }
    };

    // Balance Bar Chart
    new Chart(document.getElementById('balanceChart'), {
        type: 'bar',
        data: {
            labels: @json($owners),
            datasets: [{
                label: 'Balance (Rs.)',
                data: @json($balances),
                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                title: { display: true, text: 'Total Account Balances' }
            },
            scales: {
                ...commonOptions.scales,
                y: { ...commonOptions.scales.y, title: { display: true, text: 'Balance (Rs.)' } }
            }
        }
    });

    // Transactions Bar Chart
    new Chart(document.getElementById('transactionsChart'), {
        type: 'bar',
        data: {
            labels: @json($owners),
            datasets: [{
                label: 'Transactions',
                data: @json($transactionCounts),
                backgroundColor: 'rgba(255, 99, 132, 0.7)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                title: { display: true, text: 'Transactions per Account' }
            },
            scales: {
                ...commonOptions.scales,
                y: { ...commonOptions.scales.y, title: { display: true, text: 'Number of Transactions' } }
            }
        }
    });

    // Balance Pie Chart
    new Chart(document.getElementById('balancePieChart'), {
        type: 'pie',
        data: {
            labels: @json($owners),
            datasets: [{
                data: @json($balances),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                legend: { position: 'right' },
                title: { display: true, text: 'Balance Distribution' }
            },
            scales: {} // No scales for pie chart
        }
    });

    // Account Status Doughnut Chart
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: {
            labels: ['Active', 'Inactive'],
            datasets: [{
                data: [{{ $activeCount }}, {{ $inactiveCount }}],
                backgroundColor: ['rgba(40, 167, 69, 0.7)', 'rgba(108, 117, 125, 0.7)'],
                borderColor: ['rgba(40, 167, 69, 1)', 'rgba(108, 117, 125, 1)'],
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                legend: { position: 'right' },
                title: { display: true, text: 'Account Status Distribution' }
            },
            scales: {} // No scales for doughnut chart
        }
    });

    // Debits vs Credits Bar Chart
    new Chart(document.getElementById('debitsCreditsChart'), {
        type: 'bar',
        data: {
            labels: @json($owners),
            datasets: [
                {
                    label: 'Debits (Rs.)',
                    data: @json($debitSums),
                    backgroundColor: 'rgba(255, 99, 132, 0.7)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Credits (Rs.)',
                    data: @json($creditSums),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                title: { display: true, text: 'Debits vs Credits per Account' }
            }
        }
    });
</script>
@endsection