@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Filter Form -->
    <form method="GET" class="mb-4">
        <div class="row align-items-end">
            <div class="col-md-3">
                <label for="date_from">From</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
            </div>
            <div class="col-md-3">
                <label for="date_to">To</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
            </div>
            <div class="col-md-4">
                <label for="fornitore">Fornitore</label>
                <select name="fornitore" id="fornitore" class="form-control select2">
                    <option value="">-- All Fornitori --</option>
                    @foreach($fornitoriList as $f)
                        <option value="{{ $f }}" {{ $fornitore == $f ? 'selected' : '' }}>{{ $f }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Filter</button>
            </div>
        </div>
    </form>
    <!-- End Filter Form -->
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalInvoices) }}</h3>
                    <p>Total Invoices</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>€ {{ number_format($totalAmount, 2, ',', '.') }}</h3>
                    <p>Total Amount</p>
                </div>
                <div class="icon">
                    <i class="fas fa-euro-sign"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($unreconciledCount) }}</h3>
                    <p>Unreconciled</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ number_format($paidInvoices) }}</h3>
                    <p>Paid Invoices</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Comparison -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-info">
                    <i class="fas fa-calendar-alt"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">This Month</span>
                    <span class="info-box-number">{{ number_format($thisMonthCount) }} invoices</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        € {{ number_format($thisMonthAmount, 2, ',', '.') }}
                        @if($countChange != 0)
                            <span class="badge badge-{{ $countChange > 0 ? 'success' : 'danger' }}">
                                {{ $countChange > 0 ? '+' : '' }}{{ number_format($countChange, 1) }}%
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-box">
                <span class="info-box-icon bg-warning">
                    <i class="fas fa-chart-line"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Last Month</span>
                    <span class="info-box-number">{{ number_format($lastMonthCount) }} invoices</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        € {{ number_format($lastMonthAmount, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-info">
                    <i class="fas fa-calculator"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Average Amount</span>
                    <span class="info-box-number">€ {{ number_format($averageAmount, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-warning">
                    <i class="fas fa-clock"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Unpaid</span>
                    <span class="info-box-number">{{ number_format($unpaidInvoices) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-primary">
                    <i class="fas fa-link"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Reconciled</span>
                    <span class="info-box-number">{{ number_format($reconciledCount) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie mr-2"></i>Invoices by Status
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>Amount by Status
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="amountChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Trends -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line mr-2"></i>Monthly Trends ({{ now()->year }})
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-building mr-2"></i>Top Fornitori by Amount
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Fornitore</th>
                                    <th class="text-right">Count</th>
                                    <th class="text-right">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topFornitori as $fornitore)
                                    <tr>
                                        <td>{{ $fornitore->fornitore ?: 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($fornitore->count) }}</td>
                                        <td class="text-right text-success font-weight-bold">
                                            € {{ number_format($fornitore->total_amount, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>Top Clienti by Amount
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th class="text-right">Count</th>
                                    <th class="text-right">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topClienti as $cliente)
                                    <tr>
                                        <td>{{ $cliente->cliente ?: 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($cliente->count) }}</td>
                                        <td class="text-right text-success font-weight-bold">
                                            € {{ number_format($cliente->total_amount, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history mr-2"></i>Recent Invoices (Last 30 Days)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Fornitore</th>
                                    <th>Cliente</th>
                                    <th>Status</th>
                                    <th class="text-right">Amount</th>
                                    <th>Date</th>
                                    <th>Reconciled</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number ?: 'N/A' }}</td>
                                        <td>{{ $invoice->fornitore ?: 'N/A' }}</td>
                                        <td>{{ $invoice->cliente ?: 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $invoice->status == 'paid' ? 'success' : ($invoice->status == 'pending' ? 'warning' : 'info') }}">
                                                {{ $invoice->status ?: 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            @if($invoice->total_amount)
                                                <span class="text-success font-weight-bold">
                                                    € {{ number_format($invoice->total_amount, 2, ',', '.') }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            @if($invoice->isreconiled)
                                                <span class="badge badge-success">Yes</span>
                                            @else
                                                <span class="badge badge-warning">No</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No recent invoices</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .small-box .icon {
        transition: transform 0.3s ease;
    }
    .small-box:hover .icon {
        transform: scale(1.1);
    }
    .info-box {
        margin-bottom: 1rem;
    }
    .info-box-number {
        font-size: 1.1rem;
        font-weight: 600;
    }
    .progress-description {
        font-size: 0.9rem;
        margin-top: 0.5rem;
    }
</style>
@endsection

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select2 for fornitore
    $('.select2').select2({
        width: '100%',
        placeholder: '-- All Fornitori --',
        allowClear: true
    });
    // Prepare data for charts
    const statusLabels = {!! json_encode($totalByStatus->pluck('status')) !!};
    const statusData = {!! json_encode($totalByStatus->pluck('count')) !!};
    const statusAmountData = {!! json_encode($totalByStatus->pluck('total_amount')) !!};

    // Monthly data
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthlyData = {!! json_encode($monthlyStats->pluck('count')) !!};
    const monthlyAmount = {!! json_encode($monthlyStats->pluck('total_amount')) !!};
    const monthlyTax = {!! json_encode($monthlyStats->pluck('total_tax')) !!};

    // Chart colors
    const colors = [
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];

    // Status Chart (Pie)
    const ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'pie',
        data: {
            labels: statusLabels,
            datasets: [{
                data: statusData,
                backgroundColor: colors.slice(0, statusLabels.length),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Amount Chart (Bar)
    const ctxAmount = document.getElementById('amountChart').getContext('2d');
    new Chart(ctxAmount, {
        type: 'bar',
        data: {
            labels: statusLabels,
            datasets: [{
                label: 'Total Amount (€)',
                data: statusAmountData,
                backgroundColor: colors.slice(0, statusLabels.length),
                borderColor: colors.slice(0, statusLabels.length).map(c => c.replace('0.8', '1')),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '€ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Monthly Chart (Line)
    const ctxMonthly = document.getElementById('monthlyChart').getContext('2d');
    new Chart(ctxMonthly, {
        type: 'line',
        data: {
            labels: months,
            datasets: [{
                label: 'Count',
                data: monthlyData,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                fill: true,
                yAxisID: 'y'
            }, {
                label: 'Amount (€)',
                data: monthlyAmount,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                borderWidth: 2,
                fill: true,
                yAxisID: 'y1'
            }, {
                label: 'Tax (€)',
                data: monthlyTax,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                borderWidth: 2,
                fill: true,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Month'
                    }
                },
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Count'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Amount (€)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return '€ ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
