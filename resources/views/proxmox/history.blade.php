@extends('layouts.app')

@section('content')
    <div class="container">

        <div>
            <h1>Historico de Maquinas Virtuales</h1>
            <p> Ultimo Registro: {{ $VMHistory->last()->total_machines }}</p>
        </div>

        @php
            $monthlyData = $VMHistory->sortBy('date')->groupBy(function ($date) {
                // Agrupar por año y mes
                return Carbon\Carbon::parse($date->date)->format('Y-m');
            });

            $representativeMonthlyValues = $monthlyData->map(function ($subGroup) {
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
            <canvas id="qemusChart" style="background-color: #000000"></canvas>
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
                    label: 'Numero de vm',
                    data: @json($representativeMonthlyValues->values()),
                    backgroundColor: [
                        'rgba(250, 100, 130, 0.2)',
                    ],
                    borderColor: [
                        'rgba(250, 100, 130, 1)',
                    ],
                    borderWidth: 3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: 'white' // Cambia el color de los ticks (marcas) del eje Y a blanco
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)' // Cambia el color de las líneas de la cuadrícula del eje Y
                        }
                    },
                    x: {
                        ticks: {
                            color: 'white' // Cambia el color de los ticks del eje X a blanco
                        },
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)' // Cambia el color de las líneas de la cuadrícula del eje X
                        }
                    }
                },
                plugins: {
                    legend: {
                        labels: {
                            color: 'white' // Cambia el color del texto de la leyenda a blanco
                        }
                    }
                },
                // ... otras opciones ...
            }
        });
    </script>
@endsection
