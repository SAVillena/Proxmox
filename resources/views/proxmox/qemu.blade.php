@extends('layouts.app')

@section('content')
<div class= "container">
    {{-- buscador por nombre, con $qemu->name --}}
     <form action="{{ route('proxmox.searchQemu') }}" method="GET">
        <div class="input-group mb-3">
            <input type="text" class="form-control" placeholder="Buscar por nombre" name="search">
            <button class="btn btn-outline-secondary" type="submit">Buscar</button>
        </div>
    </form> 
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
</div>

@endsection