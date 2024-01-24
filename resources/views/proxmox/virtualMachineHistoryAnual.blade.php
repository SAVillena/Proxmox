

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
            <h1>Historico de Maquinas Virtuales</h1>
            <p>Último Registro - Total de máquinas: {{ $last->first()->cluster_qemus  ?? 'N/A' }}</p>
            <p>Último Registro - CPU: {{ $last->first()->cluster_cpu ?? 'N/A' }}</p>
            <p>Último Registro - Memoria: {{ $last ? number_format($last->first()->cluster_memory / (1024 ** 3), 2) : 'N/A' }} GB</p>
            <p>Último Registro - Disco: {{ $last ? number_format($last->first()->cluster_disk / (1024 ** 3), 2) : 'N/A' }} GB</p>
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
    
    @foreach ($grouped as $year => $items)
        <div class="card py-3">
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
                                <td>{{ round($item->cluster_memory / (1024 ** 3), 2)}} GB</td>
                                <td>{{ round($item->cluster_disk / (1024 ** 3), 2)}} GB</td>
                                <td>{{ $item->date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach
    
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
                    backgroundColor: ['rgba(250, 100, 130, 0.2)'],
                    borderColor: ['rgba(250, 100, 130, 1)'],
                    borderWidth: 3
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    },
                    x: {
                        ticks: { color: 'white' },
                        grid: { color: 'rgba(255, 255, 255, 0.1)' }
                    }
                },
                plugins: {
                    legend: { labels: { color: 'white' } }
                }
            }
        });
    </script>
@endsection

