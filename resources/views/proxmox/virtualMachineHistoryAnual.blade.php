

@extends('layouts.app')

@section('content')
    <div class="container">
        <div>
            <h1>Historico de Maquinas Virtuales</h1>
            <p>Último Registro - Total de máquinas: {{ $total_qemus ?? 'N/A' }}</p>
            <p>Último Registro - CPU: {{ $total_cpus ?? 'N/A' }}</p>
            <p>Último Registro - Memoria: {{ $total_disks ? number_format($total_memorys / (1024 ** 3), 2) : 'N/A' }} GB</p>
            <p>Último Registro - Disco: {{ $total_disks ? number_format($total_disks / (1024 ** 3), 2) : 'N/A' }} GB</p>
        </div>

        {{-- Registro por cluster --}}
        @foreach ($histories as $item)
        <h2> Ultimo Registro del {{$item->cluster_name}}</h2>
        <h5> Maquinas virtuales: {{$item->cluster_qemus}}</h5>
        <h5> CPU: {{$item->cluster_cpu}}</h5>
        <h5> Memory: {{$item->cluster_memory}}</h5>
        <h5> Disk: {{$item->cluster_disk}}</h5>
        <h5> Date: {{$item->date}}</h5>

        @endforeach
    

        @php
            use Carbon\Carbon;

            $AnualData = $histories->sortBy('date')->groupBy(function ($item) {
                return Carbon::parse($item->date)->format('Y');
            });

            $representativeAnualValues = $AnualData->map(function ($subGroup) {
                return $subGroup->last()->cluster_qemus;
            });
        @endphp

        <div class="btn-group btn-group-lg" role="group" aria-label="Large button group">
            <a href="{{ url('/proxmox/history') }}" class="btn btn-success mb-3">Mensual</a>
            <a href="{{ url('/proxmox/historyAnual') }}" class="btn btn-success mb-3">Anual</a>
        </div>

        <div style="height: 300px">
            
            <canvas id="qemusChart" style="background-color: #000000"></canvas>
        </div>
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

