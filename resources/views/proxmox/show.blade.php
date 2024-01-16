{{-- resources/views/proxmox/show.blade.php --}}

@extends('layouts.app')

@section('content')
    <div class ="container">
        <h1>Proxmox Data</h1>
        <h2>Cluster Data</h2>
        {{-- Mostrar datos de Cluster --}}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Cantidad de nodos</th>
                    <th scope="col">id de proxmox</th>
                    <th scope="col">Última actualización</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clusters as $cluster)
                    <tr>
                        <td>{{ $cluster->name }}</td>
                        <td>{{ $cluster->type }}</td>
                        <td>{{ $cluster->node_count }}</td>
                        <td>{{ $cluster->id_proxmox }}</td>
                        <td>{{ \Carbon\Carbon::parse($cluster->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <form action="{{ route('proxmox.cluster.destroy', $cluster->name) }}" method="POST">
                                <a class="btn btn-secondary btn-sm " href="/proxmox/cluster/{{ $cluster->name }}">Mostrar</a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm ">Borrar</button>
                            </form>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>


        <h2>Node Data</h2>

        {{-- Mostrar datos de Node --}}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Nombre del cluster</th>
                    <th scope="col">id de proxmox</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Storage usado</th>
                    <th scope="col">Storage maximo</th>
                    <th scope="col">ip</th>
                    <th scope="col">Nombre del nodo</th>
                    <th scope="col">Tiempo activo</th>
                    <th scope="col">RAM usado</th>
                    <th scope="col">RAM maximo</th>
                    <th scope="col">Cores</th>
                    <th scope="col">% de uso</th>
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
                            <form action="{{ route('proxmox.cluster.node.destroy', $node->node) }}" method="POST">
                                <a class="btn btn-secondary btn-sm" href="/proxmox/node/{{ $node->node }}">Mostrar</a>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Borrar</button>
                            </form>

                    </tr>
                @endforeach
            </tbody>
        </table>


        <h2>Qemu Data</h2>
        {{-- boton para exportar a excel --}}
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('proxmox.export') }}" class="btn btn-success">Exportar a Excel</a>
        </div>
        {{-- Mostrar datos de Qemu --}}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">Nodo</th>
                    <th scope="col">id de la VM</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cores</th>
                    <th scope="col">% de cpu</th>
                    <th scope="col"></th>
                    <th scope="col">Disco asignado</th>
                    <th scope="col">RAM usado</th>
                    <th scope="col">RAM maximo</th>
                    <th scope="col">Tiempo activo</th>
                    <th scope="col">Tamaño asignado</th>
                    <th scope="col">Nombre del storage</th>
                    <th scope="col">Última actualización</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($qemus as $qemu)
                    <tr>
                        <td>{{ $qemu->node_id }}</td>
                        <td>{{ $qemu->id_proxmox }}</td>
                        <td>{{ $qemu->name }}</td>
                        <td>{{ $qemu->type }}</td>
                        <td>{{ $qemu->status }}</td>
                        <td>{{ $qemu->maxcpu }}</td>
                        <td>{{ round($qemu->cpu,4) *100 }}%</td>
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar 
                                            {{ $qemu->cpu * 100 <= 50 ? 'bg-success' : ($qemu->cpu * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ $qemu->cpu * 100 }}%"
                                    aria-valuenow="{{ $qemu->cpu * 100 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $qemu->cpu * 100 }}%
                                </div>
                            </div>
                        @if ($qemu->maxdisk >= 1099511627776)
                            <td>{{ round($qemu->maxdisk / 1099511627776, 2) }} TB</td>
                        @else
                            <td>{{ round($qemu->maxdisk / 1073741824, 2) }} GB</td>
                        @endif

                        <td>{{ round($qemu->mem / 1073741824, 2) }} GB</td>
                        <td>{{ round($qemu->maxmem / 1073741824, 2) }} GB</td>
                        <td>{{ $qemu->netin }}</td>
                        <td>{{ $qemu->size }}</td>
                        <td>{{ $qemu->storageName }}</td>
                        <td>{{ \Carbon\Carbon::parse($qemu->updated_at)->format('d/m/Y H:i') }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Storage Data</h2>
        {{-- Mostrar datos de Storage --}}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">id de proxmox</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Almacenamiento usado</th>
                    <th scope="col">Almacenamiento maximo</th>
                    <th scope="col">% de uso</th>
                    <th scope="col">uso</th>
                    <th scope="col">Nodo al que pertenece</th>
                    <th scope="col">Nombre del Storage</th>
                    <th scope="col">Contenido</th>
                    <th scope="col">plugintype</th>
                    <th scope="col">Última actualización</th>

                </tr>
            </thead>
            <tbody>
                @foreach ($storages as $storage)
                    <tr>
                        <td>{{ $storage->id_proxmox }}</td>
                        <td>{{ $storage->type }}</td>
                        <td>{{ $storage->status }}</td>
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
                        <td>
                            <div class="progress" style="width: 100px;">
                                <div class="progress-bar 
                                            {{ $storage->used * 100 <= 50 ? 'bg-success' : ($storage->used * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ $storage->used * 100 }}%"
                                    aria-valuenow="{{ $storage->used * 100 }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $storage->used * 100 }}%
                                </div>
                            </div>

                            <td>{{ round($storage->used, 4) * 100 }}%</td>


                        </td>
                        <td>{{ $storage->node['node'] }}</td>
                        <td>{{ $storage->storage }}</td>
                        <td>{{ $storage->content }}</td>
                        <td>{{ $storage->plugintype }}</td>
                        <td>{{ \Carbon\Carbon::parse($storage->updated_at)->format('d/m/Y H:i') }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
