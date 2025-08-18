@extends('layouts.app')

@section('content')
    <div class = "justify-content-start px-3">
        <h2 class ="text-center py-3"><strong>Storage Data</strong></h2>
        <h3 class ="text-center py-3"><strong>Grafico de uso</strong></h3>
        {{-- realizar un grafico de torta con porcentaje de uso de cada storage --}}
        <div class="row">
            @foreach ($filteredStorages as $storage)
                    
                <div class="col-lg-6 py-3">
                    <div class="card w-100 bg-dark text-white py">
                        <div class="card-header">
                            <h3>Storage: {{ $storage->storage }}</h3>
                            <p>Uso: {{ $storage->used ? round($storage->used * 100, 1) : 0 }}%</p>
                            <p>Utilizado: 
                                @if ($storage->disk)
                                    {{ $storage->disk >= 1099511627776 ? round($storage->disk / 1099511627776, 2) . ' TB' : round($storage->disk / 1073741824, 2) . ' GB' }}
                                @else
                                    N/A
                                @endif
                            </p>
                            <p>Tamaño Vol: 
                                @if ($storage->maxdisk)
                                    {{ $storage->maxdisk >= 1099511627776 ? round($storage->maxdisk / 1099511627776, 2) . ' TB' : round($storage->maxdisk / 1073741824, 2) . ' GB' }}
                                @else
                                    N/A
                                @endif
                            </p>
                            @if(!$storage->cluster)
                                <p>Node: {{ $storage->node_id }}</p>
                            @else
                                <p>Cluster: {{ $storage->cluster }}</p>
                            @endif     
                        </div>
                        <div class="card-body">
                            <canvas id="storageChart{{ $loop->index }}" width="200" height="200"></canvas>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="col-lg-6 py-3">

            <div class="card w-100 bg-dark text-white py">
                <div class="card-header">
                    <h3>Total almacenamiento</h3>
                    <p> Uso: {{ $totalMaxDisk > 0 ? round($totalUsedDisk/$totalMaxDisk,2) * 100 : 0 }} %</p>
                    <p> Utilizado:  
                        @if ($totalUsedDisk)
                            {{ $totalUsedDisk >= 1099511627776 ? round($totalUsedDisk / 1099511627776, 2) . ' TB' : round($totalUsedDisk / 1073741824, 2) . ' GB' }}
                        @else
                            N/A
                        @endif
                    </p>
                    <p> Tamaño Vol: 
                        @if ($totalMaxDisk)
                            {{ $totalMaxDisk >= 1099511627776 ? round($totalMaxDisk / 1099511627776, 2) . ' TB' : round($totalMaxDisk / 1073741824, 2) . ' GB' }}
                        @else
                            N/A
                        @endif
                    </p>
                    <p> No se consideran los nodos sin cluster</p>
                </div>
                <div class="card-body">
                    <canvas id="totalStorageChart" width="200" height="200"></canvas>
                    
                </div>
            </div>
            </div>
        </div>

        
           

        <h3 class ="text-center py-3"><strong>Tabla de datos</strong></h3>
        {{-- Buscador de Storage --}}
        {{-- exportar a excel --}}
        <div class="d-flex justify-content-start mb-3">
            <a href="{{ route('proxmox.exportStorage') }}" class="btn btn-success">Exportar a Excel</a>
        </div>
        <form action="{{ route('proxmox.searchStorage') }}" method="GET">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Buscar por nombre" name="search">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
            </div>
            {{-- Mostrar datos de Storage --}}
            <table class="table table-dark table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Cluster</th>
                        <th scope="col">Storage</th>
                        <th scope="col">Carga</th>
                        <th scope="col">Uso</th>
                        <th scope="col">Total</th>
                        <th scope="col">Contenido</th>
                        <th scope="col">Tipo</th>
                        <th scope="col">Última actualización</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($filteredStorages as $storage)
                        <tr>
                            @if($storage->cluster)
                                <td>{{ $storage->cluster }}</td>
                            @else
                                <td> {{$storage->node_id}} </td>
                            @endif
                            <td>{{ $storage->storage }}</td>
                            <td>
                                @php
                                    $usagePercent = $storage->used ? ($storage->used * 100) : 0;
                                @endphp
                                <div class="progress" style="width: 100px;">
                                    <div class="progress-bar text-dark fw-bolder 
                                {{ $usagePercent <= 50 ? 'bg-success' : ($usagePercent <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                        role="progressbar" style="width: {{ $usagePercent }}%"
                                        aria-valuenow="{{ $usagePercent }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ round($usagePercent, 1) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if ($storage->disk)
                                    @if ($storage->disk >= 1099511627776)
                                        {{ round($storage->disk / 1099511627776, 2) }} TB
                                    @else
                                        {{ round($storage->disk / 1073741824, 2) }} GB
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if ($storage->maxdisk)
                                    @if ($storage->maxdisk >= 1099511627776)
                                        {{ round($storage->maxdisk / 1099511627776, 2) }} TB
                                    @else
                                        {{ round($storage->maxdisk / 1073741824, 2) }} GB
                                    @endif
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>{{ $storage->content }}</td>
                            <td>{{ $storage->plugintype }}</td>
                            <td>{{ \Carbon\Carbon::parse($storage->updated_at)->format('d/m/Y H:i') }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        @foreach ($filteredStorages as $storage)
            var ctx = document.getElementById('storageChart{{ $loop->index }}').getContext('2d');
            var maxDisk = {{ $storage->maxdisk ?? 0 }};
            var usedDisk = {{ $storage->disk ?? 0 }};
            var freeDisk = maxDisk > 0 ? (maxDisk - usedDisk) : 0;

            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Espacio Usado', 'Espacio Libre'],
                    datasets: [{
                        label: 'Disk Usage',
                        data: [usedDisk, freeDisk],
                        backgroundColor: [
                            'rgb(255, 99, 132,0.8)',
                            'rgb(54, 162, 235,0.8)',
                            'rgb(255, 205, 86)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            labels: {
                                color: 'white'
                            }
                        },
                        tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label;
                                var value = context.parsed;
                                var total = context.chart._metasets[context.datasetIndex].total;
                                var percentage = total > 0 ? (value / total * 100).toFixed(2) + '%' : '0%';
                                return label + ': ' + percentage;
                            }
                        }
                    }
                    },
                    responsive: true,
                    maintainAspectRatio: false,
                }
            });
        @endforeach
    </script>

    <script>
        // Calcula el total de maxdisk y disk
        var totalMaxDisk = 0;
        var totalUsedDisk = 0;
        @foreach ($filteredStorages as $storage)
            @if($storage->cluster)
            totalMaxDisk += {{ $storage->maxdisk ?? 0 }};
            totalUsedDisk += {{ $storage->disk ?? 0 }};
            @endif
        @endforeach

        // Crea el gráfico con los totales
        var ctxTotal = document.getElementById('totalStorageChart').getContext('2d');
        var freeSpace = totalMaxDisk > 0 ? (totalMaxDisk - totalUsedDisk) : 0;
        new Chart(ctxTotal, {
            type: 'pie',
            data: {
                labels: ['Espacio Usado Total', 'Espacio Libre Total'],
                datasets: [{
                    label: 'Total Disk Usage',
                    data: [totalUsedDisk, freeSpace],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: {
                        labels: {
                            color: 'white'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var label = context.label;
                                var value = context.parsed;
                                var total = context.chart._metasets[context.datasetIndex].total;
                                var percentage = total > 0 ? (value / total * 100).toFixed(2) + '%' : '0%';
                                return label + ': ' + percentage;
                            }
                        }
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
            }
        });
    </script>
@endsection
