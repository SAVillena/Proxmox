@extends('layouts.app')

@section('content')
    @php
        use Carbon\Carbon;

        $AnualData = $histories->sortBy('date')->groupBy(function ($item) {
            return Carbon::parse($item->date)->format('Y');
        });
        $last = $AnualData->last();

        $representativeAnualValues = $AnualData->map(function ($subGroup) {
            return $subGroup->last()->cluster_qemus;
        });
    @endphp

<div class="container">
    <div>
        <h1>Historial de Maquinas Virtuales</h1>
        <p>Último Registro - Total de máquinas: {{ $last->first()->cluster_qemus ?? 'N/A' }}</p>
        <p>Último Registro - vCPU: {{ $last->first()->cluster_cpu ?? 'N/A' }}</p>
        <p>Último Registro - Memoria:
            {{ $AnualData->last() ? number_format($last->first()->cluster_memory / 1024 ** 3, 2) : 'N/A' }}
            GB</p>
        <p>Último Registro - Disco:
            @if ($AnualData->last())
                @php
                    $diskInGB = $last->first()->cluster_disk / 1024 ** 3; // Convertir a GB
                @endphp
                @if ($diskInGB >= 1024)
                    {{ number_format($diskInGB / 1024, 2) }} TB
                @else
                    {{ number_format($diskInGB, 2) }} GB
                @endif
            @else
                N/A
            @endif
        </p>
        <div class="card w-100 bg-dark text-white py-3">
            <div class = "card-header">
                <h3>Crecimiento respecto al año anterior</h3>
            </div>
            <div class="card-body">
                <p>VMs: {{ $growth['qemus'] }}</p>
                <p>vCPU: {{ $growth['cpus'] }}</p>
                <p>RAM: {{ $growth['memorys'] }}</p>
                <p>Disco: {{ $growth['disks'] }}</p>
            </div>
        </div>

        {{-- Registro por cluster --}}




        <div class="btn-group btn-group-lg" role="group" aria-label="Large button group">
            <a href="{{ url('/proxmox/history') }}" class="btn btn-success mb-3">Mensual</a>
            <a href="{{ url('/proxmox/historyAnual') }}" class="btn btn-success mb-3">Anual</a>
        </div>

        <div style="height: 300px">

            <canvas id="qemusChart" style="background-color: #000000"></canvas>
        </div>

        @php
            \Carbon\Carbon::setLocale('es');
            // Agrupar por año
            $grouped = $VMHistory->sortByDesc('date')->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->date)->format('Y');
            });

            // Filtrar para obtener solo los registros del último mes de cada año
            $grouped = $grouped->map(function ($yearItems) {
                $lastMonth = $yearItems->first()->date;
                return $yearItems->filter(function ($item) use ($lastMonth) {
                    return $item->date === $lastMonth;
                });
            });
        @endphp
        <div class="container py-3">
            @foreach ($grouped as $year => $items)
                <div class="card w-100 bg-dark text-white py-3">
                    <div class="card-header">{{ $year }}</div>
                    <div class="card-body">
                        <table class="table table-dark table-hover table-bordered ">
                            <thead>
                                <tr>
                                    <th scope="col">Cluster</th>
                                    <th scope="col">VMs</th>
                                    <th scope="col">CPU</th>
                                    <th scope="col">Memoria</th>
                                    <th scope="col">Disco</th>
                                    <th scope="col">Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td>{{ $item->cluster_name }}</td>
                                        <td>{{ $item->cluster_qemus }}</td>
                                        <td>{{ $item->cluster_cpu }}</td>
                                        <td>{{ round($item->cluster_memory / 1024 ** 3, 2) }} GB</td>
                                        <td>{{ round($item->cluster_disk / 1024 ** 3, 2) }} GB</td>
                                        <td>{{ $item->date }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            var ctx = document.getElementById('qemusChart').getContext('2d');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($representativeAnualValues->keys()),
                    datasets: [{
                        label: 'Número de VMs',
                        data: @json($representativeAnualValues->values()),
                        backgroundColor: ['rgba(4, 132, 76, 0.6)'],
                        borderColor: ['rgba(4, 132, 76, 1)'],
                        borderWidth: 3
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: 'white'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        }
                    }
                }
            });
        </script>
    @endsection
