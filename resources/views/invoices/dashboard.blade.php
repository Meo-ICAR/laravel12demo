@extends('layouts.admin')

@section('title', 'Invoices Dashboard')

@section('content_header')
    <h1>Invoices Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="card mb-3">
        <div class="card-header">
            <h3 class="card-title">Overview</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Invoices</span>
                            <span class="info-box-number">{{ number_format($totalInvoices) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-euro-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Amount</span>
                            <span class="info-box-number">€ {{ number_format($totalAmount, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Invoices by Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top 5 Fornitori by Amount</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="fornitoriChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bar Chart: Invoices by Status
    var ctxStatus = document.getElementById('statusChart').getContext('2d');
    new Chart(ctxStatus, {
        type: 'bar',
        data: {
            labels: {!! json_encode($totalByStatus->pluck('status')) !!},
            datasets: [{
                label: 'Count',
                data: {!! json_encode($totalByStatus->pluck('count')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    // Pie Chart: Top Fornitori by Amount
    var ctxFornitori = document.getElementById('fornitoriChart').getContext('2d');
    new Chart(ctxFornitori, {
        type: 'pie',
        data: {
            labels: {!! json_encode($topFornitori->pluck('fornitore')) !!},
            datasets: [{
                data: {!! json_encode($topFornitori->pluck('total_amount')) !!},
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true
        }
    });
});
</script>
@stop
