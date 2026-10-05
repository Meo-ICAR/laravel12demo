@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Calls Dashboard
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Date Filter Form -->
                    <form action="{{ route('calls.dashboard') }}" method="GET" class="row mb-4">
                        <div class="col-md-3">
                            <label for="date_from">Date From:</label>
                            <input type="date" name="date_from" id="date_from" class="form-control"
                                   value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <label for="date_to">Date To:</label>
                            <input type="date" name="date_to" id="date_to" class="form-control"
                                   value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('calls.dashboard') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </form>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($totalCalls) }}</h3>
                                    <p>Total Calls</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ number_format($answeredCalls) }}</h3>
                                    <p>Answered Calls</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($busyCalls) }}</h3>
                                    <p>Busy Calls</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ number_format($noAnswerCalls) }}</h3>
                                    <p>No Answer</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie"></i> Call Status Distribution
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="statusChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-users"></i> Top Operators
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Operator</th>
                                                    <th>Calls</th>
                                                    <th>Total Duration</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topOperators as $operator => $stats)
                                                    <tr>
                                                        <td>{{ $operator ?: 'Unknown' }}</td>
                                                        <td>{{ $stats['count'] }}</td>
                                                        <td>{{ gmdate('H:i:s', $stats['total_duration']) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Row -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-bar"></i> Calls by Hour
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="hourChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-line"></i> Top Outcomes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Outcome</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($topOutcomes as $outcome => $count)
                                                    <tr>
                                                        <td>{{ $outcome ?: 'Unknown' }}</td>
                                                        <td>{{ $count }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Calls Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-list"></i> Recent Calls
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Numero Chiamato</th>
                                            <th>Data Inizio</th>
                                            <th>Durata</th>
                                            <th>Stato Chiamata</th>
                                            <th>Esito</th>
                                            <th>Utente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentCalls as $call)
                                            <tr>
                                                <td>{{ $call->id }}</td>
                                                <td>{{ $call->numero_chiamato }}</td>
                                                <td>{{ $call->data_inizio ? $call->data_inizio->format('d/m/Y H:i:s') : '-' }}</td>
                                                <td class="text-center">{{ $call->getFormattedDuration() }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $call->stato_chiamata === 'ANSWER' ? 'success' : ($call->stato_chiamata === 'BUSY' ? 'warning' : 'secondary') }}">
                                                        {{ $call->stato_chiamata }}
                                                    </span>
                                                </td>
                                                <td>{{ $call->esito }}</td>
                                                <td>{{ $call->utente }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No calls found.</td>
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
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status Distribution Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Answered', 'Busy', 'No Answer', 'Other'],
            datasets: [{
                data: [{{ $answeredCalls }}, {{ $busyCalls }}, {{ $noAnswerCalls }}, {{ $otherStatusCalls }}],
                backgroundColor: [
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ]
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

    // Calls by Hour Chart
    const hourCtx = document.getElementById('hourChart').getContext('2d');
    const hourLabels = @json($callsByHour->keys());
    const hourData = @json($callsByHour->values());

    new Chart(hourCtx, {
        type: 'bar',
        data: {
            labels: hourLabels,
            datasets: [{
                label: 'Calls',
                data: hourData,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>
@endsection
