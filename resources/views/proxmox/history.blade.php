@extends('layouts.app')

@section('content')
    <div class="container">
        
    <div>
        <h1>Historico de qemus</h1>
        <p> Ultimo Registro: {{ $VMHistory->last()->total_machines }}</p>
    </div>

    @php
    $monthlyData = $VMHistory->sortBy('date')->groupBy(function($date) {
        // Agrupar por año y mes
        return Carbon\Carbon::parse($date->date)->format('Y-m');
    });
    
    $representativeMonthlyValues = $monthlyData->map(function($subGroup){
        // Seleccionar el último valor de cada mes
        return $subGroup->last()->total_machines;
    });

    @endphp

    {{-- boton que si se presiona muestra el anual o mes --}}
    <div class="btn-group btn-group-lg" role="group" aria-label="Large button group">
        <a href="{{ url('/proxmox/history') }}" class="btn btn-success mb-3">Mensual</a>
        <a href="{{ url('/proxmox/historyAnual') }}" class="btn btn-success mb-3">Anual</a>
    </div>
    <div>
        <canvas id="qemusChart"></canvas>
    </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        var ctx = document.getElementById('qemusChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($representativeMonthlyValues->keys()),
                datasets: [{
                    label: 'Numero de qemus',
                    data:@json($representativeMonthlyValues->values()),
                    backgroundColor: [
                        'rgba(250, 100, 130, 0.2)',
                    ],
                    borderColor: [
                        'rgba(250, 100, 130, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            } 
        });
    </script>
    
@endsection
