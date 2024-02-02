@extends('layouts.app')

@section('content')
    <div class="justify-content-start px-3 py-3">
        <h1 class = "text-center">Detalles de {{ $cluster->name }}</h1>
        <div class="d-flex justify-content-start mb-3">
            <a href="{{ route('proxmox.cluster') }}" class="btn btn-success"> &#8592; Volver</a>
        </div>

        {{-- crear card con informacion del cluster --}}
        <div class="row">
            <div class="col-md-6 mb-3 d-flex align-items-stretch">
                <div class="card w-100 bg-dark text-white">
                    <div class="card-header">
                        <h3>Resumen de Cluster

                    </div>
                    <div class="card-body">
                        <h5 class="card-title">Nombre del cluster: {{ $cluster->name }}</h5>
                        <h5 class="card-text">Cantidad de nodos: {{ $cluster->node_count }}</h5>
                        <h5 class="card-text">Cantidad de VMs: {{ $totalQemu }}</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3 d-flex align-items-stretch">
                <div class="card w-100 bg-dark text-white">
                    <div class="card-header">
                        <h3>RAM</h3>
                    </div>
                    <div class="card-body">
                        <div class="justify-content-center py-3">
                            <h5 class="card-text">Total RAM: {{ round($totalRAM / 1073741824, 2) }} GB</h5>
                            <h5 class="card-text">Total RAM asignada: {{ round($totalRAMQemu / 1073741824, 2) }} GB</h5>

                            <div class="progress "style="height: 30px" style="width: 100px;">
                                <div class="progress-bar text-dark fw-bolder text-dark fw-bolder
                            {{ round($totalRAMQemu / $totalRAM, 2) * 100 <= 50 ? 'bg-success' : (round($totalRAMQemu / $totalRAM, 2) * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ round($totalRAMQemu / $totalRAM, 2) * 100 }}%"
                                    aria-valuenow="{{ round($totalRAMQemu / $totalRAM, 2) * 100 }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ round($totalRAMQemu / $totalRAM, 2) * 100 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3 d-flex align-items-stretch">
                <div class="card w-100 bg-dark text-white">
                    <div class="card-header">
                        <h3>CPU</h3>
                    </div>
                    <div class="card-body">
                        <div class= "justify-content-center py-3">
                            <h5 class="card-text">Total CPU-Cores: {{ $totalCPU }}</h5>
                            <h5 class="card-text">Total vCPU-Cores asignadas: {{ $totalCPUQemu }}</h5>

                            <div class="progress" style="height: 30px" style="width: 100px;">
                                <div class="progress-bar text-dark fw-bolder 
                            {{ round($totalCPUQemu / $totalCPU, 2) * 100 <= 50 ? 'bg-success' : (round($totalCPUQemu / $totalCPU, 2) * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ round($totalCPUQemu / $totalCPU, 2) * 100 }}%"
                                    aria-valuenow="{{ round($totalCPUQemu / $totalCPU, 2) * 100 }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ round($totalCPUQemu / $totalCPU, 2) * 100 }}%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3 d-flex align-items-stretch">

                <div class="card w-100 bg-dark text-white">
                    <div class="card-header">
                        <h3>Disco</h3>
                    </div>
                    <div class="card-body">
                        <div class = "justify-content-center py-3">
                            <h5 class="card-text">Total disco en cluster: {{ round($totalMaxDisk / 1099511627776, 2) }}
                                TB
                            </h5>
                            <h5 class="card-text">Total disco asignado: {{ round($totalDiskQemu / 1099511627776, 2) }}
                                TB
                            </h5>

                            <div class="progress" style="height: 30px" style="width: 100px;">
                                <div class="progress-bar text-dark fw-bolder 
                            {{ round($totalDiskQemu / $totalMaxDisk, 2) * 100 <= 50 ? 'bg-success' : (round($totalDiskQemu / $totalMaxDisk, 2) * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ round($totalDiskQemu / $totalMaxDisk, 2) * 100 }}%"
                                    aria-valuenow="{{ round($totalDiskQemu / $totalMaxDisk, 2) * 100 }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ round($totalDiskQemu / $totalMaxDisk, 2) * 100 }}%
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>


        <!-- Agrega aquí más detalles del cluster -->

        <h2 class = "text-center py-3">Nodos</h2>
        <table class="table table-dark table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col">Cluster</th>
                    <th scope="col">Nodo</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cores</th>
                    <th scope="col">RAM</th>
                    <th scope="col">Uso disco</th>
                    <th scope="col">Disco</th>
                    <th scope="col">IP</th>
                    <th scope="col">Uptime</th>
                    <th scope="col">RAM usado</th>
                    <th scope="col">Carga CPU</th>

                    <th scope="col">Última actualización</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nodes as $node)
                    <tr>
                        @if ($node->cluster_name == null)
                            <td>Sin cluster</td>
                        @else
                            <td>{{ $node->cluster_name }}</td>
                        @endif
                        <td>{{ $node->node }}</td>
                        <td>{{ $node->status }}</td>
                        <td>{{ $node->maxcpu }}</td>
                        <td>{{ round($node->maxmem / 1073741824, 2) }} GB</td>
                        {{-- mostrar el uso de almacenamiento pero en gigas o teras segun corresponda, considera que esta en bytes --}}
                        @if ($node->disk >= 1099511627776)
                            <td>{{ round($node->disk / 1099511627776, 2) }} TB</td>
                        @else
                            <td>{{ round($node->disk / 1073741824, 2) }} GB</td>
                        @endif

                        @if ($node->maxdisk >= 1099511627776)
                            <td>{{ round($node->maxdisk / 1099511627776, 2) }} TB</td>
                        @else
                            <td>{{ round($node->maxdisk / 1073741824, 2) }} GB</td>
                        @endif

                        <td>{{ $node->ip }}</td>
                        <td>{{ floor($node->uptime / 86400) }} días</td>
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar text-dark fw-bolder 
                                        {{ round($node->mem / $node->maxmem, 2) * 100 <= 50 ? 'bg-success' : (($node->mem / $node->maxmem) * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ round($node->mem / $node->maxmem, 2) * 100 }}%"
                                    aria-valuenow="{{ round($node->mem / $node->maxmem, 2) * 100 }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ round($node->mem / $node->maxmem, 2) * 100 }}%
                                </div>
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar text-dark fw-bolder 
                                        {{ $node->cpu * 100 <= 50 ? 'bg-success' : ($node->cpu * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ $node->cpu * 100 }}%"
                                    aria-valuenow="{{ $node->cpu * 100 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $node->cpu * 100 }}%
                                </div>
                            </div>
                        </td>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($node->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <a class="btn btn-secondary btn-sm" href="/proxmox/node/{{ $node->node }}">Mostrar</a>
                                <form action="{{ route('proxmox.cluster.node.destroy', $node->node) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    @can('manage cluster')
                                        <button type="submit"
                                            class="btn btn-danger btn-sm"onclick="return confirm('¿Estás seguro de querer borrar este cluster?');">Borrar</button>
                                    @endcan
                                </form>
                            </div>

                    </tr>
                @endforeach
            </tbody>
        </table>


        <h2 class="text-center py-3">Maquinas Virtuales</h2>
        {{-- boton de exportar a excel donde se envia el nombre del cluster --}}
        <div class="d-flex px-3">
            <div class="d-flex justify-content-start mb-3">
                <a href="{{ route('proxmox.qemuByCluster.export', $cluster->name) }}" class="btn btn-success">Exportar a Excel</a>
            </div>
        </div>
        
        {{-- Mostrar datos de Qemu --}}
        <table class="table table-dark table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col">Cluster</th>
                    <th scope="col">Nodo</th>
                    <th scope="col">VM ID</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Estado</th>
                    <th scope="col">vCPU</th>
                    <th scope="col">RAM</th>
                    <th scope="col">Disco </th>
                    <th scope="col">RAM Usado</th>
                    <th scope="col">Carga de Cpu</th>
                    <th scope="col">Storage</th>
                    <th scope="col">Última actualización</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($qemus as $qemu)
                    <tr>
                        @if ($qemu->cluster_name == null)
                            <td>Sin cluster</td>
                        @else
                            <td>{{ $qemu->cluster_name }}</td>
                        @endif
                        <td>{{ $qemu->node->node }}</td>
                        <td>{{ $qemu->vmid }}</td>
                        <td>{{ $qemu->name }}</td>
                        <td>{{ $qemu->status }}</td>
                        <td>{{ $qemu->maxcpu }}</td>
                        <td>{{ round($qemu->maxmem / 1073741824, 2) }} GB</td>

                        @if ($qemu->maxdisk >= 1099511627776)
                            <td>{{ round($qemu->size / 1099511627776, 2) }} TB</td>
                        @else
                            <td>{{ round($qemu->size / 1073741824, 2) }} GB</td>
                        @endif

                        <td>
                            <div class="progress" style="width: 100px;"
                                title="{{ round($qemu->mem / $qemu->maxmem, 4) * 100 }}%">
                                <div class="progress-bar text-dark fw-bolder 
                                            {{ ($qemu->mem / $qemu->maxmem) * 100 <= 50 ? 'bg-success' : (($qemu->mem / $qemu->maxmem) * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ ($qemu->mem / $qemu->maxmem) * 100 }}%"
                                    aria-valuenow="{{ ($qemu->mem / $qemu->maxmem) * 100 }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ round($qemu->mem / $qemu->maxmem, 4) * 100 }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar text-dark fw-bolder 
                                            {{ $qemu->cpu * 100 <= 50 ? 'bg-success' : ($qemu->cpu * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ $qemu->cpu * 100 }}%"
                                    aria-valuenow="{{ $qemu->cpu * 100 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $qemu->cpu * 100 }}%
                                </div>
                            </div>
                        <td>{{ $qemu->storageName }}</td>
                        <td>{{ \Carbon\Carbon::parse($qemu->updated_at)->format('d/m/Y H:i') }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
        <h2 class = "text-center py-3">Almacenamiento</h2>
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
                @foreach ($storages as $storage)
                    <tr>
                        @if ($storage->cluster)
                            <td>{{ $storage->cluster }}</td>
                        @else
                            <td> {{ $storage->node_id }} </td>
                        @endif
                        <td>{{ $storage->storage }}</td>
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar text-dark fw-bolder 
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
@endsection
