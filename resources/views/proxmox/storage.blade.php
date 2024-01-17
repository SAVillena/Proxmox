@extends('layouts.app')

@section('content')
    <div class = "justify-content-start px-3">
        <h2 class ="text-center py-3"><strong>Storage Data</strong></h2>
        {{-- Mostrar datos de Storage --}}
        <table class="table table-dark table-hover table-bordered">
            <thead>
                <tr>
                    <th scope="col">id de proxmox</th>
                    <th scope="col">Estado</th>
                    <th scope="col">Almacenamiento usado</th>
                    <th scope="col">Almacenamiento maximo</th>
                    <th scope="col">Uso</th>
                    <th scope="col">Almacenamiento</th>
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
                        <td>{{ $storage->node_id }}</td>
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
