@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Detalles de {{ $cluster->name }}</h1>
        <!-- Agrega aquí más detalles del cluster -->

        <h2>Nodos</h2>
        <table class="table table-hover table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nombre del nodo</th>
                    <th>IP del nodo</th>
                    <th>Estado del nodo</th>
                    <th>Uso de CPU del nodo</th>
                    <th>Uso de memoria del nodo</th>
                    <th>Uso de almacenamiento del nodo</th>
                    <th>Maximo de almacenamiento del nodo</th>
                    <th>Porcentaje de uso</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                @foreach ($nodes as $node)
                    <tr>
                        <td>{{ $node->node }}</td>
                        <td>{{ $node->ip }}</td>
                        <td>{{ $node->status }}</td>
                        <td>{{ $node->cpu }}</td>
                        <td>{{ $node->mem }}</td>
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
                        <td>{{ round($storageLocal[$node->id_proxmox] / $storageLocalMax[$node->id_proxmox], 4) * 100 }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Maquinas Virtuales</h2>

        
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>ID de la maquina virtual</th>
                    <th>Nombre de la maquina virtual</th>
                    <th>Estado de la maquina virtual</th>
                    <th>Uso de CPU de la maquina virtual</th>
                    <th>Cores CPU de la maquina virtual</th>
                    <th>Uso de memoria de la maquina virtual</th>
                    <th>Maximo de memoria de la maquina virtual</th>
                    <th>Uso de almacenamiento de la maquina virtual</th>
                    <th>Maximo de almacenamiento de la maquina virtual</th>
                    <th>Storage Vinculado a la maquina virtual</th>
                    <th>Porcentaje de uso</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                @foreach ($qemus as $qemu)
                    <tr>
                        <td>{{ $qemu->id_proxmox }}</td>
                        <td>{{ $qemu->name }}</td>
                        <td>{{ $qemu->status }}</td>
                        <td>{{ $qemu->cpu }}</td>
                        <td>{{ $qemu->maxcpu }}</td>
                        <td>{{ $qemu->mem }}</td>
                        <td>{{ $qemu->maxmem }}</td>
                        <td>{{ $qemu->disk }}</td>
                        <td>{{ $qemu->maxdisk }}</td>
                        <td>{{ $qemu->storageName }}</td>
                        <td>{{ round($qemu->disk / $qemu->maxdisk, 2) * 100 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h2>Almacenamiento</h2>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Nodo asociado</th>
                    <th>Nombre del almacenamiento</th>
                    <th>Tipo de almacenamiento</th>
                    <th>Estado del almacenamiento</th>
                    <th>Uso de almacenamiento</th>
                    <th>Maximo de almacenamiento</th>
                    <th>Porcentaje de uso</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                @foreach ($storages as $storage)
                    <tr>
                        <td>{{ $storage->node['node'] }}</td>

                        <td>{{ $storage->storage }}</td>
                        <td>{{ $storage->content }}</td>
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
                        <td>{{ round($storage->used, 4) * 100 }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
