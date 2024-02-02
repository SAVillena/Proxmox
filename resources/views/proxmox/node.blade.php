@extends('layouts.app')
@section('content')
    <div class="justify-content-start px-3">

        <h2 class="text-center py-3"><strong>Node Data</strong></h2>

        <form action="{{ route('proxmox.searchNode') }}" method="GET">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Buscar por nombre" name="search">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
            </div>

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
                                        role="progressbar"
                                        style="width: {{ round($node->mem / $node->maxmem, 2) * 100 }}%"
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
                                    <a class="btn btn-secondary btn-sm"
                                        href="/proxmox/node/{{ $node->node }}">Mostrar</a>
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
    </div>
@endsection
