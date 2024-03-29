@extends('layouts.app')

@section('content')
    <div class="justify-content-start px-3 py-3">
        <h2 class="text-center">Node Data</h2>

        {{-- Mostrar datos de Node --}}
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

                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2 class="text-center py-3">Maquinas Virtuales</h2>
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

        <h2 class="text-center py-3">Storage Data</h2>
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
