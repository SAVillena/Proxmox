@extends('layouts.app')

@section('content')
    <div class="container">
        
    <div>
        <h1>Historico de Maquinas Virtuales</h1>
        {{-- {{dd($VMHistoryLast)}} --}}
        @foreach ($VMHistoryLast as $item)
        <h2> Ultimo Registro cluster {{$item->cluster_name}}</h2>
        <h5> Numero de maquinas virtuales: {{$item->cluster_qemus}}</h5>
        <h5> Numero de cpu: {{$item->cluster_cpu}}</h5>
        <h5> Numero de memory: {{$item->cluster_memory}}</h5>

        @endforeach
        <p> Ultimo Registro: {{ $total_machines }}</p>

        <h5>Crecimiento de maquinas virtuales por año</h5>
        <p> Ultimo Registro: {{ $cpu_growht }}</p>

        <h5> Crecimiento de RAM  por año</h5>
        <p> Ultimo Registro: {{ $memory_growht }}</p>
        </div>

@php
    $AnualData = $VMHistory->sortBy('date')->groupBy(function ($date) {
        // Agrupar por año 
        return Carbon\Carbon::parse($date->date)->format('Y');
    });
    $representativeAnualValues = $AnualData->map(function($subGroup){
        // Seleccionar el último valor del año
        return $subGroup->last()->cluster_qemus;
    });
@endphp

    {{-- boton que si se presiona muestra el anual o mes --}}
    <div class="btn-group btn-group-lg" role="group" aria-label="Large button group">
        <a href="{{ url('/proxmox/history') }}" class="btn btn-success mb-3">Mensual</a>
        <a href="{{ url('/proxmox/history/anual') }}" class="btn btn-success mb-3">Anual</a>
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
                labels: @json($representativeAnualValues->keys()),
                datasets: [{
                    label: 'Numero de VM',
                    data:@json($representativeAnualValues->values()),
                    backgroundColor: [
                        'rgba(250, 100, 130, 0.2)',
                    ],
                    borderColor: [
                        'rgba(200, 100, 130, 1)',
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
                
            }
        });
    </script>
    
@endsection
