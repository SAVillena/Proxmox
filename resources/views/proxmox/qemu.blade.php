@extends('layouts.app')

@section('content')
    <div class= "justify-content-start px-3">
        {{-- buscador por nombre, con $qemu->name --}}
        <h2 class="text-center"><strong>Qemu Data</strong></h2>
        {{-- boton para exportar a excel --}}
        <div class="d-flex justify-content-start mb-3">
            <a href="{{ route('proxmox.export') }}" class="btn btn-success">Exportar a Excel</a>
        </div>
        <form action="{{ route('proxmox.searchQemu') }}" method="GET">
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Buscar por nombre" name="search">
                <button class="btn btn-outline-secondary" type="submit">Buscar</button>
            </div>
        </form>
        {{-- Mostrar datos de Qemu --}}
        <table class="table table-dark table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col">Nodo</th>
                    <th scope="col">id de la VM</th>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Cores</th>
                    <th scope="col">CPU</th>
                    <th scope="col">Carga de Cpu</th>
                    <th scope="col">Disco asignado</th>
                    <th scope="col">RAM Usado</th>
                    <th scope="col">RAM Maximo</th>
                    <th scope="col">Tiempo activo</th>
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

                        <td>
                            <div class="progress" style="width: 100px;" title="{{ round($qemu->mem / $qemu->maxmem, 4) * 100 }}%">
                                <div class="progress-bar 
                                            {{ $qemu->mem / $qemu->maxmem * 100 <= 50 ? 'bg-success' : ($qemu->mem / $qemu->maxmem * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                    role="progressbar" style="width: {{ $qemu->mem / $qemu->maxmem * 100 }}%"
                                    aria-valuenow="{{ $qemu->mem / $qemu->maxmem * 100 }}" aria-valuemin="0"
                                    aria-valuemax="100">
                                    {{ round($qemu->mem / $qemu->maxmem, 4) * 100 }}%
                                </div>
                            </div>
                        <td>{{ round($qemu->maxmem / 1073741824, 2) }} GB</td>
                        <td>{{ $qemu->netin }}</td>
                        <td>{{ $qemu->storageName }}</td>
                        <td>{{ \Carbon\Carbon::parse($qemu->updated_at)->format('d/m/Y H:i') }}</td>

                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
@endsection
