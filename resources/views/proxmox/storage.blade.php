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
                            @if(!$storage->cluster)
                                <p>Node: {{ $storage->node_id }}</p>
                            @else
                                <p>Cluster: {{ $storage->cluster }}</p>
                            @endif     
                            <p>Uso: {{ $storage->used * 100 }}%</p>
                            <p>Utilizado: {{ $storage->disk >= 1099511627776 ? round($storage->disk / 1099511627776, 2) . ' TB' : round($storage->disk / 1073741824, 2) . ' GB' }}</p>
                            <p>Tamaño Vol: {{ $storage->maxdisk >= 1099511627776 ? round($storage->maxdisk / 1099511627776, 2) . ' TB' : round($storage->maxdisk / 1073741824, 2) . ' GB' }}</p>
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
                    <p> No se consideran los nodos sin cluster</p>
                    <p> Uso: {{round($totalUsedDisk/$totalMaxDisk,2) *100}} %</p>
                    <p> Utilizado:  {{ $totalUsedDisk >= 1099511627776 ? round($totalUsedDisk / 1099511627776, 2) . ' TB' : round($totalUsedDisk / 1073741824, 2) . ' GB' }}</p>
                    <p> Tamaño Vol: {{ $totalMaxDisk >= 1099511627776 ? round($totalMaxDisk / 1099511627776, 2) . ' TB' : round($totalMaxDisk / 1073741824, 2) . ' GB' }} </p>
                </div>
                <div class="card-body">
                    <canvas id="totalStorageChart" width="200" height="200"></canvas>
                    
                </div>
            </div>
            </div>
        </div>

        
           

        <h3 class ="text-center py-3"><strong>Tabla de datos</strong></h3>
        {{-- Buscador de Storage --}}
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
                                <div class="progress" style="width: 100px;">
                                    <div class="progress-bar 
                                {{ $storage->used * 100 <= 50 ? 'bg-success' : ($storage->used * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                        role="progressbar" style="width: {{ $storage->used * 100 }}%"
                                        aria-valuenow="{{ $storage->used * 100 }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $storage->used * 100 }}%
                                    </div>
                                </div>

                            </td>
                            @if ($storage->disk >= 1099511627776)
                                <td>{{ round($storage->disk / 1099511627776, 2) }} TB</td>
                            @else
                                <td>{{ round($storage->disk / 1073741824, 2) }} GB</td>
                            @endif
                            @if ($storage->maxdisk >= 1099511627776)
                                <td>{{ round($storage->maxdisk / 1099511627776, 2) }} TB</td>
                            @else
                                <td>{{ round($storage->maxdisk / 1073741824, 2) }} GB</td>
                            @endif
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
            var maxDisk = {{ $storage->maxdisk }};
            var usedDisk = {{ $storage->disk }};
            var freeDisk = maxDisk - usedDisk;

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
                                var percentage = (value / total * 100).toFixed(2) + '%';
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
            totalMaxDisk += {{ $storage->maxdisk }};
            totalUsedDisk += {{ $storage->disk }};
            @endif
        @endforeach

        // Crea el gráfico con los totales
        var ctxTotal = document.getElementById('totalStorageChart').getContext('2d');
        new Chart(ctxTotal, {
            type: 'pie',
            data: {
                labels: ['Espacio Usado Total', 'Espacio Libre Total'],
                datasets: [{
                    label: 'Total Disk Usage',
                    data: [totalUsedDisk, totalMaxDisk - totalUsedDisk],
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
                                var percentage = (value / total * 100).toFixed(2) + '%';
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
