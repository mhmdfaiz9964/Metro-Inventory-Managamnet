@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    :root {
        --primary-color: #1B2850;
        --secondary-color: #2c3e50;
        --accent-color: #3498db;
        --success-color: #27ae60;
        --warning-color: #f39c12;
        --danger-color: #e74c3c;
        --info-color: #17a2b8;
        --light-bg: #f8f9fa;
        --white: #ffffff;
    }
    body {
        background-color: var(--light-bg);
    }
    .dashboard-container {
        padding: 10px 0;
        max-width: 100%;
    }
    .header-title {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: var(--white);
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .header-title h2 {
        margin: 0;
        font-size: 1.4rem;
        font-weight: 600;
    }
    .header-title p {
        margin: 5px 0 0;
        font-size: 0.9rem;
        opacity: 0.9;
    }
    .metrics-section {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-radius: 12px;
        padding: 20px 10px;
        margin-bottom: 15px;
        color: var(--white);
    }
    .metrics-section h3 {
        text-align: center;
        margin-bottom: 20px;
        font-weight: 600;
        font-size: 1.2rem;
        text-shadow: 0 1px 3px rgba(0,0,0,0.3);
    }
    .metric-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, var(--accent-color), var(--success-color));
    }
    .metric-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(0,0,0,0.15);
        background: rgba(255, 255, 255, 0.12);
    }
    .metric-icon {
        font-size: 1.5rem;
        margin-bottom: 8px;
        opacity: 0.9;
    }
    .metric-value {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 4px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }
    .metric-label {
        font-size: 0.75rem;
        opacity: 0.9;
        font-weight: 500;
    }
    .charts-section {
        background: var(--white);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }
    .charts-section h4 {
        color: var(--primary-color);
        margin-bottom: 15px;
        text-align: center;
        font-weight: 600;
        font-size: 1rem;
    }
    .top-products-section {
        background: var(--white);
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 4px 18px rgba(0,0,0,0.06);
    }
    .top-products-section h4 {
        color: var(--primary-color);
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 1rem;
    }
    .notification-card {
        background: var(--white);
        border-radius: 10px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        padding: 15px;
        position: relative;
        color: var(--primary-color);
        border-left: 2px solid var(--accent-color);
    }
    .notification-bell {
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        width: 40px;
        height: 40px;
        background: var(--warning-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--white);
        box-shadow: 0 2px 8px rgba(243, 156, 18, 0.25);
    }
    .notification-bell i {
        font-size: 1rem;
    }
    .notification-close {
        position: absolute;
        top: 8px;
        right: 8px;
        font-size: 1.1rem;
        color: var(--danger-color);
        cursor: pointer;
        background: none;
        border: none;
    }
    .message-text {
        text-align: center;
        font-size: 0.85rem;
        line-height: 1.3;
        margin-bottom: 12px;
        color: var(--secondary-color);
    }
    .btn-custom {
        background: var(--light-bg);
        border: 1px solid var(--secondary-color);
        padding: 6px 16px;
        border-radius: 15px;
        color: var(--primary-color);
        font-weight: 500;
        transition: all 0.3s;
        font-size: 0.8rem;
    }
    .btn-custom:hover {
        background: var(--secondary-color);
        color: var(--white);
        transform: translateY(-1px);
    }
    .btn-got-it {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: var(--white);
    }
    .btn-got-it:hover {
        background: var(--secondary-color);
        border-color: var(--secondary-color);
    }
    @media (max-width: 768px) {
        .metrics-section {
            padding: 15px 8px;
        }
        .metric-value {
            font-size: 1.1rem;
        }
        .metric-icon {
            font-size: 1.3rem;
        }
        .notification-card {
            margin: 0 5px 10px;
        }
        .btn-custom {
            font-size: 0.75rem;
            padding: 5px 12px;
        }
        .charts-section, .top-products-section {
            padding: 12px;
        }
        .metrics-section h3 {
            font-size: 1.1rem;
        }
        .header-title h2 {
            font-size: 1.2rem;
        }
    }
    @media (max-width: 576px) {
        .dashboard-container {
            padding: 5px 0;
        }
        .metric-card {
            padding: 10px 8px;
        }
        .metric-value {
            font-size: 1rem;
        }
        .metric-label {
            font-size: 0.7rem;
        }
    }
</style>

<div class="dashboard-container">
    {{-- Header --}}
    <div class="header-title">
        <h2 class="text-white"><i class="bi bi-gear-fill me-2 text-white"></i>Production Metrics Dashboard</h2>
    </div>

    {{-- Key Metrics Section - 6 Cards --}}
    <div class="metrics-section">
        <h3 class="text-white"><i class="bi bi-bar-chart-line me-2 text-white"></i>Key Metrics Overview</h3>
        <div class="row g-2 justify-content-center">
            {{-- Sample data for manufacturing metrics --}}
            @php
                $sampleMetrics = [
                    'total_production' => 1250,
                    'rework_qty' => 50,
                    'production_cost' => 150000,
                    'labor_cost' => 30000,
                    'product_qty_current' => 1200,
                    'direct_labor_cost' => 25000
                ];
            @endphp

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="metric-card">
                    <div class="metric-icon text-success">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div class="metric-value">{{ number_format($sampleMetrics['total_production'], 0) }}</div>
                    <div class="metric-label">Total Production</div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="metric-card">
                    <div class="metric-icon text-warning">
                        <i class="bi bi-arrow-repeat"></i>
                    </div>
                    <div class="metric-value">{{ $sampleMetrics['rework_qty'] }}</div>
                    <div class="metric-label">Rework Quantity</div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="metric-card">
                    <div class="metric-icon text-info">
                        <i class="bi bi-currency-dollar"></i>
                    </div>
                    <div class="metric-value">Rs. {{ number_format($sampleMetrics['production_cost'], 0) }}</div>
                    <div class="metric-label">Production Cost</div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="metric-card">
                    <div class="metric-icon text-danger">
                        <i class="bi bi-people"></i>
                    </div>
                    <div class="metric-value">Rs. {{ number_format($sampleMetrics['labor_cost'], 0) }}</div>
                    <div class="metric-label">Labor Cost</div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="metric-card">
                    <div class="metric-icon text-success">
                        <i class="bi bi-calendar-month"></i>
                    </div>
                    <div class="metric-value">{{ $sampleMetrics['product_qty_current'] }}</div>
                    <div class="metric-label">Current Month Qty</div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 col-sm-6">
                <div class="metric-card">
                    <div class="metric-icon text-primary">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div class="metric-value">Rs. {{ number_format($sampleMetrics['direct_labor_cost'], 0) }}</div>
                    <div class="metric-label">Direct Labor Cost</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="charts-section">
        <h4><i class="bi bi-graph-up me-2 text-primary"></i>Production Analytics</h4>
        <div class="row g-2">
            {{-- Run Time vs Down Time Bar Chart --}}
            <div class="col-12 col-md-6">
                <canvas id="runtimeChart" height="150"></canvas>
            </div>
            {{-- Monthly Production Cost Line Chart --}}
            <div class="col-12 col-md-6">
                <canvas id="costChart" height="150"></canvas>
            </div>
            {{-- Donut Charts: Availability, Performance, Quality, Efficiency --}}
            <div class="col-6 col-md-3">
                <canvas id="availabilityChart" height="150"></canvas>
            </div>
            <div class="col-6 col-md-3">
                <canvas id="performanceChart" height="150"></canvas>
            </div>
            <div class="col-6 col-md-3">
                <canvas id="qualityChart" height="150"></canvas>
            </div>
            <div class="col-6 col-md-3">
                <canvas id="efficiencyChart" height="150"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Products Bar Chart --}}
    <div class="top-products-section">
        <h4><i class="bi bi-fire me-2 text-warning"></i>Top Products Production</h4>
        <div class="row">
            <div class="col-12">
                <canvas id="topProductsChart" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Notifications Section --}}
    <div class="row g-2">
        @foreach($upcomingCheques as $cheque)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="notification-card">
                    <div class="notification-bell">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <span class="notification-close">&times;</span>
                    <div class="text-center mt-1">
                        <h6 class="mb-1 text-primary"><strong>Upcoming Cheque</strong></h6>
                        <div class="message-text">
                            <strong>Rem:</strong> #{{ $cheque->cheque_no }} | {{ $cheque->bank->bank_name ?? 'N/A' }} | Rs. {{ number_format($cheque->amount, 2) }} | {{ $cheque->cheque_date->format('d M Y') }}
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <button type="button" class="btn btn-custom btn-got-it" onclick="markChequePaid({{ $cheque->id }})">Got it</button>
                        <button type="button" class="btn btn-custom" onclick="this.closest('.notification-card').style.display='none'">Close</button>
                    </div>
                </div>
            </div>
        @endforeach

        @foreach($upcomingReceivings as $receiving)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="notification-card">
                    <div class="notification-bell">
                        <i class="bi bi-bell-fill"></i>
                    </div>
                    <span class="notification-close">&times;</span>
                    <div class="text-center mt-1">
                        <h6 class="mb-1 text-primary"><strong>Upcoming Receiving</strong></h6>
                        <div class="message-text">
                            <strong>Rem:</strong> #{{ $receiving->cheque_no }} | {{ $receiving->bank->bank_name ?? 'N/A' }} | Rs. {{ number_format($receiving->amount, 2) }} | {{ $receiving->paid_by }} | {{ $receiving->cheque_date->format('d M Y') }}
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-1">
                        <button type="button" class="btn btn-custom btn-got-it" onclick="markReceivingPaid({{ $receiving->id }})">Got it</button>
                        <button type="button" class="btn btn-custom" onclick="this.closest('.notification-card').style.display='none'">Close</button>
                    </div>
                </div>
            </div>
        @endforeach

        @if($upcomingCheques->isEmpty() && $upcomingReceivings->isEmpty())
            <div class="col-12 text-center mt-2">
                <div class="alert alert-success shadow-sm rounded-2 p-2" style="border-left: 2px solid var(--success-color); font-size: 0.85rem;">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    No notifications. All cheques are up to date!
                </div>
            </div>
        @endif
    </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios@1.6.8/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sample manufacturing data
    const manufacturingData = {
        runtimeLabels: ['Press', 'Laser', 'Packing', 'Grinding', 'Binding', 'Finishing'],
        runtimeData: [3000, 2500, 2000, 2800, 2200, 1800],
        downtimeData: [500, 800, 600, 400, 700, 900],
        costLabels: ['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov'],
        costData: [10000, 12000, 11000, 13000, 12500, 14000],
        availability: { available: 70, unavailable: 30 },
        performance: { good: 90, poor: 10 },
        quality: { high: 91, low: 9 },
        efficiency: { efficient: 60, inefficient: 40 },
        topProducts: {
            labels: ['Fan', 'Rice Cooker', 'Washing Machine', 'Table Fan', 'AC', 'Bulb'],
            data: [250, 200, 150, 180, 120, 100]
        }
    };

    // Run Time vs Down Time Bar Chart
    const runtimeCtx = document.getElementById('runtimeChart').getContext('2d');
    new Chart(runtimeCtx, {
        type: 'bar',
        data: {
            labels: manufacturingData.runtimeLabels,
            datasets: [{
                label: 'Run Time',
                data: manufacturingData.runtimeData,
                backgroundColor: 'rgba(75, 192, 192, 0.6)'
            }, {
                label: 'Down Time',
                data: manufacturingData.downtimeData,
                backgroundColor: 'rgba(255, 99, 132, 0.6)'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Monthly Production Cost Line Chart
    const costCtx = document.getElementById('costChart').getContext('2d');
    new Chart(costCtx, {
        type: 'line',
        data: {
            labels: manufacturingData.costLabels,
            datasets: [{
                label: 'Production Cost',
                data: manufacturingData.costData,
                borderColor: 'rgb(153, 102, 255)',
                backgroundColor: 'rgba(153, 102, 255, 0.1)',
                tension: 0.4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Donut Charts
    const createDonut = (ctxId, data, colors) => {
        const ctx = document.getElementById(ctxId).getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(data),
                datasets: [{
                    data: Object.values(data),
                    backgroundColor: colors
                }]
            },
            options: { responsive: true }
        });
    };
    createDonut('availabilityChart', manufacturingData.availability, ['#FF6384', '#36A2EB']);
    createDonut('performanceChart', manufacturingData.performance, ['#FFCE56', '#4BC0C0']);
    createDonut('qualityChart', manufacturingData.quality, ['#9966FF', '#FF9F40']);
    createDonut('efficiencyChart', manufacturingData.efficiency, ['#FF6384', '#C9CBCF']);

    // Top Products Bar Chart
    const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(topProductsCtx, {
        type: 'bar',
        data: {
            labels: manufacturingData.topProducts.labels,
            datasets: [{
                label: 'Units Produced',
                data: manufacturingData.topProducts.data,
                backgroundColor: 'rgba(75, 192, 192, 0.6)'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // Notification functions
    function markChequePaid(chequeId) {
        Swal.fire({
            title: 'Mark as Paid?',
            text: 'Confirm?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-success', cancelButton: 'btn btn-secondary ms-2' }
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/cheques/paid/${chequeId}`).then(() => location.reload()).catch(() => Swal.fire('Error!', 'Failed.', 'error'));
            }
        });
    }

    function markReceivingPaid(receivingId) {
        Swal.fire({
            title: 'Mark as Received?',
            text: 'Confirm?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            buttonsStyling: false,
            customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-secondary ms-2' }
        }).then((result) => {
            if (result.isConfirmed) {
                axios.post(`/admin/receiving-cheques/paid/${receivingId}`).then(() => location.reload()).catch(() => Swal.fire('Error!', 'Failed.', 'error'));
            }
        });
    }
</script>
@endsection