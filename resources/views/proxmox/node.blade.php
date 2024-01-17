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
                    <th scope="col">Nombre Cluster</th>
                    <th scope="col">id de proxmox</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Storage usado</th>
                    <th scope="col">Storage maximo</th>
                    <th scope="col">IP</th>
                    <th scope="col">Nombre del nodo</th>
                    <th scope="col">Tiempo activo</th>
                    <th scope="col">RAM usado</th>
                    <th scope="col">RAM maximo</th>
                    <th scope="col">Cores</th>
                    <th scope="col">Uso CPU</th>
                    <th scope="col">% de Storage</th>
                    <th scope="col">Última actualización</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nodes as $node)
                    <tr>

                        <td>{{ $node->cluster_name }}</td>
                        <td>{{ $node->id_proxmox }}</td>
                        <td>{{ $node->type }}</td>
                        <td>{{ $node->status }}</td>
                        {{-- mostrar el uso de almacenamiento pero en gigas o teras segun corresponda, considera que esta en bytes --}}
                        @if ($storageLocal[$node->id_proxmox] >= 1099511627776)
                            <td>{{ round($storageLocal[$node->id_proxmox] / 1099511627776, 2) }} TB</td>
                        @else
                            <td>{{ round($storageLocal[$node->id_proxmox] / 1073741824, 2) }} GB</td>
                        @endif

                        @if ($storageLocalMax[$node->id_proxmox] >= 1099511627776)
                            <td>{{ round($storageLocalMax[$node->id_proxmox] / 1099511627776, 2) }} TB</td>
                        @else
                            <td>{{ round($storageLocalMax[$node->id_proxmox] / 1073741824, 2) }} GB</td>
                        @endif

                        <td>{{ $node->ip }}</td>
                        <td>{{ $node->node }}</td>
                        <td>{{ $node->uptime }}</td>
                        <td>{{ round($node->mem / 1073741824, 2) }} GB</td>
                        <td>{{ round($node->maxmem / 1073741824, 2) }} GB</td>
                        <td>{{ $node->maxcpu }}</td>
                        <td>{{ $node->cpu * 100 }}%</td>
                        <td>{{ round($storageLocal[$node->id_proxmox] / $storageLocalMax[$node->id_proxmox], 4) * 100 }}%
                        </td>
                        <td>{{ \Carbon\Carbon::parse($node->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-1">
                                <a class="btn btn-secondary btn-sm" href="/proxmox/node/{{ $node->node }}">Mostrar</a>
                                <form action="{{ route('proxmox.cluster.node.destroy', $node->node) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                                </form>
                            </div>
                            
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
