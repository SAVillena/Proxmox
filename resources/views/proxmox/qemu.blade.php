@extends('layouts.app')

@section('content')
    <div class= "justify-content-start px-3">
        {{-- buscador por nombre, con $qemu->name --}}
        <h2 class="text-center"><strong>Maquinas Virtuales</strong></h2>
        <div class="d-flex px-3">
            {{-- boton para exportar a excel --}}

            <div class="d-flex justify-content-start mb-3">
                <a href="{{ route('proxmox.exportQemu') }}" class="btn btn-success">Exportar a Excel</a>
            </div>
            {{-- crear boton de eliminar qemus en estado eliminado --}}
        </div>
        {{-- buscador por nombre, con $qemu->name --}}

        <form action="{{ route('proxmox.searchQemu') }}" method="GET">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Buscar por nombre o ID" name="search">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
            </div>
        </form>


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
                        <td>{{ $qemu->node_id }}</td>
                        <td>{{ $qemu->vmid }}</td>
                        <td>{{ $qemu->name }}</td>
                        <td>{{ $qemu->status }}</td>
                        <td>{{ $qemu->maxcpu }}</td>
                        <td>{{ round($qemu->maxmem / 1073741824, 2) }} GB</td>

                        @if ($qemu->size >= 1099511627776)
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
    </div>
@endsection
