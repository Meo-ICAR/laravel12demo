@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line"></i> Leads Dashboard
        </h1>
        <div>
            <a href="{{ route('leads.export', request()->query()) }}" class="btn btn-success btn-sm mr-2">
                <i class="fas fa-download"></i> Export Data
            </a>
            <a href="{{ route('leads.index') }}" class="btn btn-secondary btn-sm mr-2">
                <i class="fas fa-list"></i> View All Leads
            </a>
            <a href="{{ route('leads.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add New Lead
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-filter"></i> Dashboard Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('leads.dashboard') }}" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date_from">Date From</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="date_to">Date To</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}">
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-search"></i> Apply Filters
                        </button>
                        <a href="{{ route('leads.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalLeads) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($activeLeads) }}</div>
                            <div class="text-xs text-gray-600">{{ $totalLeads > 0 ? round(($activeLeads / $totalLeads) * 100, 1) : 0 }}% of total</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Calls</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalCalls) }}</div>
                            <div class="text-xs text-gray-600">{{ $avgCallsPerLead }} avg per lead</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-phone fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Leads with Calls</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($leadsWithCalls) }}</div>
                            <div class="text-xs text-gray-600">{{ $totalLeads > 0 ? round(($leadsWithCalls / $totalLeads) * 100, 1) : 0 }}% of total</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-phone-volume fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Monthly Trends Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Monthly Trends (Last 12 Months)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="monthlyTrendsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Campaign Performance -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Campaigns</h6>
                </div>
                <div class="card-body">
                    @if($campaignStats->count() > 0)
                        @foreach($campaignStats->take(5) as $campaign)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="font-weight-bold">{{ $campaign->campagna ?: 'N/A' }}</span>
                                    <span class="badge badge-primary">{{ $campaign->total }}</span>
                                </div>
                                <div class="progress mt-1" style="height: 8px;">
                                    @php
                                        $activePercentage = $campaign->total > 0 ? ($campaign->active / $campaign->total) * 100 : 0;
                                    @endphp
                                    <div class="progress-bar bg-success" style="width: {{ $activePercentage }}%"></div>
                                </div>
                                <small class="text-muted">
                                    {{ $campaign->active }} active / {{ $campaign->inactive }} inactive
                                    @if($campaign->avg_calls)
                                        | {{ round($campaign->avg_calls, 1) }} avg calls
                                    @endif
                                </small>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No campaign data available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Tables -->
    <div class="row mb-4">
        <!-- Outcome Statistics -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Outcome Distribution</h6>
                </div>
                <div class="card-body">
                    @if($outcomeStats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Outcome</th>
                                        <th>Count</th>
                                        <th>Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($outcomeStats as $outcome)
                                        <tr>
                                            <td>{{ $outcome->esito ?: 'N/A' }}</td>
                                            <td>{{ $outcome->total }}</td>
                                            <td>{{ $totalLeads > 0 ? round(($outcome->total / $totalLeads) * 100, 1) : 0 }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No outcome data available.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Regional Statistics -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Regional Distribution</h6>
                </div>
                <div class="card-body">
                    @if($regionalStats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Region</th>
                                        <th>Total</th>
                                        <th>Active</th>
                                        <th>Active %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($regionalStats->take(10) as $region)
                                        <tr>
                                            <td>{{ $region->regione ?: 'N/A' }}</td>
                                            <td>{{ $region->total }}</td>
                                            <td>{{ $region->active }}</td>
                                            <td>{{ $region->total > 0 ? round(($region->active / $region->total) * 100, 1) : 0 }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No regional data available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mb-4">
        <!-- Recent Leads -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Leads</h6>
                </div>
                <div class="card-body">
                    @if($recentLeads->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Campaign</th>
                                        <th>Created</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentLeads as $lead)
                                        <tr>
                                            <td>
                                                <a href="{{ route('leads.show', $lead) }}" class="text-decoration-none">
                                                    {{ $lead->full_name }}
                                                </a>
                                            </td>
                                            <td>{{ $lead->campagna ?: 'N/A' }}</td>
                                            <td>{{ $lead->data_creazione ? $lead->data_creazione->format('d/m/Y') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge {{ $lead->status_badge_class }}">
                                                    {{ $lead->attivo ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No recent leads available.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Calls -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Calls</h6>
                </div>
                <div class="card-body">
                    @if($recentCalls->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Operator</th>
                                        <th>Last Call</th>
                                        <th>Calls</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentCalls as $lead)
                                        <tr>
                                            <td>
                                                <a href="{{ route('leads.show', $lead) }}" class="text-decoration-none">
                                                    {{ $lead->full_name }}
                                                </a>
                                            </td>
                                            <td>{{ $lead->ultimo_operatore ?: 'N/A' }}</td>
                                            <td>{{ $lead->ultima_chiamata ? $lead->ultima_chiamata->format('d/m/Y H:i') : 'N/A' }}</td>
                                            <td>
                                                <span class="badge badge-info">{{ $lead->chiamate ?: 0 }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">No recent calls available.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Trends Chart
    const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
    const monthlyTrendsChart = new Chart(monthlyTrendsCtx, {
        type: 'line',
        data: {
            labels: @json(collect($monthlyTrends)->pluck('month')),
            datasets: [{
                label: 'Leads',
                data: @json(collect($monthlyTrends)->pluck('leads')),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }, {
                label: 'Calls',
                data: @json(collect($monthlyTrends)->pluck('calls')),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                }
            }
        }
    });
});
</script>
@endpush
@endsection
