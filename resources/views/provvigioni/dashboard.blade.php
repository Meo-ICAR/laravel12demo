@extends('layouts.admin')

@section('title', 'Provvigioni Dashboard')

@section('content_header')
    <h1>Provvigioni Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalCount) }}</h3>
                    <p>Total Provvigioni</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>€ {{ number_format($totalImporto, 2, ',', '.') }}</h3>
                    <p>Total Importo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-euro-sign"></i>
                </div>
            </div>
        </div>
        <!-- Income (Entrata) Card -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>€ {{ number_format($incomeImporto, 2, ',', '.') }}</h3>
                    <p>Income (Entrata)</p>
                    <small>{{ number_format($incomeCount) }} records</small>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>
        <!-- Costs (Uscita/Other) Card -->
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>€ {{ number_format($costImporto, 2, ',', '.') }}</h3>
                    <p>Costs (Uscita/Other)</p>
                    <small>{{ number_format($costCount) }} records</small>
                </div>
                <div class="icon">
                    <i class="fas fa-arrow-up"></i>
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
                    <span class="info-box-number">{{ number_format($thisMonthCount) }} records</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        € {{ number_format($thisMonthImporto, 2, ',', '.') }}
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
                    <span class="info-box-number">{{ number_format($lastMonthCount) }} records</span>
                    <div class="progress">
                        <div class="progress-bar" style="width: 100%"></div>
                    </div>
                    <span class="progress-description">
                        € {{ number_format($lastMonthImporto, 2, ',', '.') }}
                    </span>
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
                        <i class="fas fa-chart-pie mr-2"></i>Provvigioni by Stato
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="statoChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar mr-2"></i>Importo by Stato
                    </h3>
                </div>
                <div class="card-body">
                    <canvas id="importoChart" height="200"></canvas>
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
                        <i class="fas fa-trophy mr-2"></i>Top Denominazioni by Importo
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Denominazione</th>
                                    <th class="text-right">Count</th>
                                    <th class="text-right">Total Importo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topDenominazioni as $denom)
                                    <tr>
                                        <td>{{ $denom->denominazione_riferimento ?: 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($denom->count) }}</td>
                                        <td class="text-right text-success font-weight-bold">
                                            € {{ number_format($denom->total_importo, 2, ',', '.') }}
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
                        <i class="fas fa-university mr-2"></i>Top Istituti Finanziari
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Istituto</th>
                                    <th class="text-right">Count</th>
                                    <th class="text-right">Total Importo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topIstituti as $istituto)
                                    <tr>
                                        <td>{{ $istituto->istituto_finanziario ?: 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($istituto->count) }}</td>
                                        <td class="text-right text-success font-weight-bold">
                                            € {{ number_format($istituto->total_importo, 2, ',', '.') }}
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
                        <i class="fas fa-history mr-2"></i>Recent Activity (Last 30 Days)
                    </h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Descrizione</th>
                                    <th>Denominazione</th>
                                    <th>Stato</th>
                                    <th class="text-right">Importo</th>
                                    <th>Created</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivity as $activity)
                                    <tr>
                                        <td>{{ $activity->descrizione ?: 'N/A' }}</td>
                                        <td>{{ $activity->denominazione_riferimento ?: 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $activity->stato == 'Pagato' ? 'success' : ($activity->stato == 'Proforma' ? 'warning' : 'info') }}">
                                                {{ $activity->stato ?: 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            @if($activity->importo)
                                                <span class="text-success font-weight-bold">
                                                    € {{ number_format($activity->importo, 2, ',', '.') }}
                                                </span>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $activity->created_at ? $activity->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No recent activity</td>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prepare data for charts
    const statoLabels = {!! json_encode($statoCounts->pluck('stato')) !!};
    const statoData = {!! json_encode($statoCounts->pluck('count')) !!};
    const statoImportoData = {!! json_encode($statoImporto->pluck('total_importo')) !!};

    // Monthly data
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const monthlyData = {!! json_encode($monthlyStats->pluck('count')) !!};
    const monthlyImporto = {!! json_encode($monthlyStats->pluck('total_importo')) !!};

    // Chart colors
    const colors = [
        'rgba(54, 162, 235, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];

    // Stato Chart (Pie)
    const ctxStato = document.getElementById('statoChart').getContext('2d');
    new Chart(ctxStato, {
        type: 'pie',
        data: {
            labels: statoLabels,
            datasets: [{
                data: statoData,
                backgroundColor: colors.slice(0, statoLabels.length),
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

    // Importo Chart (Bar)
    const ctxImporto = document.getElementById('importoChart').getContext('2d');
    new Chart(ctxImporto, {
        type: 'bar',
        data: {
            labels: statoLabels,
            datasets: [{
                label: 'Total Importo (€)',
                data: statoImportoData,
                backgroundColor: colors.slice(0, statoLabels.length),
                borderColor: colors.slice(0, statoLabels.length).map(c => c.replace('0.8', '1')),
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
                label: 'Importo (€)',
                data: monthlyImporto,
                borderColor: 'rgba(255, 99, 132, 1)',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
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
                        text: 'Importo (€)'
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
