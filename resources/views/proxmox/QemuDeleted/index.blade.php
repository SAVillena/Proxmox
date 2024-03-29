@extends('layouts.app')

@section('content')
    <div class= "justify-content-start px-3">
        {{-- buscador por nombre, con $QemuDeletedDeleted->name --}}
        <h2 class="text-center"><strong>VM Eliminadas</strong></h2>
        <div class="d-flex px-3">

        </div>

        @if ($QemuDeleteds->count() == 0)
            <div class="alert alert-warning" role="alert">
                No hay VMs eliminadas
            </div>
        @else
            @can('manage cluster')
                <form class= "px-3"action="{{ route('proxmox.qemu.destroy') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger mb-3" type="submit">Eliminar VMs</button>

                </form>
            @endcan
            {{-- Mostrar datos de QemuDeletedDeleted --}}
            <table class="table table-dark table-hover table-bordered">
                <thead>
                    <tr>
                        <th scope="col">Nodo</th>
                        <th scope="col">ID</th>
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
                    @foreach ($QemuDeleteds as $QemuDeleted)
                        <tr>
                            
                            <td>{{ $QemuDeleted->node_id }}</td>
                            <td>{{ $QemuDeleted->id_proxmox }}</td>
                            <td>{{ $QemuDeleted->name }}</td>
                            <td>{{ $QemuDeleted->status }}</td>
                            <td>{{ $QemuDeleted->maxcpu }}</td>
                            <td>{{ round($QemuDeleted->maxmem / 1073741824, 2) }} GB</td>

                            @if ($QemuDeleted->size >= 1099511627776)
                                <td>{{ round($QemuDeleted->size / 1099511627776, 2) }} TB</td>
                            @else
                                <td>{{ round($QemuDeleted->size / 1073741824, 2) }} GB</td>
                            @endif

                            <td>
                                <div class="progress" style="width: 100px;"
                                    title="{{ round($QemuDeleted->mem / $QemuDeleted->maxmem, 4) * 100 }}%">
                                    <div class="progress-bar text-dark fw-bolder 
                                                {{ ($QemuDeleted->mem / $QemuDeleted->maxmem) * 100 <= 50 ? 'bg-success' : (($QemuDeleted->mem / $QemuDeleted->maxmem) * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                        role="progressbar" style="width: {{ ($QemuDeleted->mem / $QemuDeleted->maxmem) * 100 }}%"
                                        aria-valuenow="{{ ($QemuDeleted->mem / $QemuDeleted->maxmem) * 100 }}" aria-valuemin="0"
                                        aria-valuemax="100">
                                        {{ round($QemuDeleted->mem / $QemuDeleted->maxmem, 4) * 100 }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="progress" style="width: 100px;">
                                    <div class="progress-bar text-dark fw-bolder 
                                                {{ $QemuDeleted->cpu * 100 <= 50 ? 'bg-success' : ($QemuDeleted->cpu * 100 <= 75 ? 'bg-warning' : 'bg-danger') }}"
                                        role="progressbar" style="width: {{ $QemuDeleted->cpu * 100 }}%"
                                        aria-valuenow="{{ $QemuDeleted->cpu * 100 }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $QemuDeleted->cpu * 100 }}%
                                    </div>
                                </div>
                            <td>{{ $QemuDeleted->storageName }}</td>
                            <td>{{ \Carbon\Carbon::parse($QemuDeleted->updated_at)->format('d/m/Y H:i') }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
@endsection
