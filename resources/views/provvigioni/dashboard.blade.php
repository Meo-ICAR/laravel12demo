@extends('layouts.admin')

@section('title', 'Provvigioni Dashboard')

@section('content_header')
    <h1>Provvigioni Dashboard</h1>
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
                            <span class="info-box-text">Total Provvigioni</span>
                            <span class="info-box-number">{{ number_format($totalProvvigioni) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-euro-sign"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Importo</span>
                            <span class="info-box-number">€ {{ number_format($totalImporto, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Provvigioni by Stato</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="statoChart" height="200"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-info">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Top 5 Denominazione Riferimento by Importo</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="denomChart" height="200"></canvas>
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
    // Bar Chart: Provvigioni by Stato
    var ctxStato = document.getElementById('statoChart').getContext('2d');
    new Chart(ctxStato, {
        type: 'bar',
        data: {
            labels: {!! json_encode($totalByStato->pluck('stato')) !!},
            datasets: [{
                label: 'Count',
                data: {!! json_encode($totalByStato->pluck('count')) !!},
                backgroundColor: 'rgba(54, 162, 235, 0.7)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } }
        }
    });

    // Pie Chart: Top Denominazione by Importo
    var ctxDenom = document.getElementById('denomChart').getContext('2d');
    new Chart(ctxDenom, {
        type: 'pie',
        data: {
            labels: {!! json_encode($topDenominazioni->pluck('denominazione_riferimento')) !!},
            datasets: [{
                data: {!! json_encode($topDenominazioni->pluck('total_importo')) !!},
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
