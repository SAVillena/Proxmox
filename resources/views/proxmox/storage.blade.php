@extends('layouts.app')

@section('content')
    <div class = "container">
        <h2>Storage Data</h2>
        {{-- Mostrar datos de Storage --}}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">id proxmox</th>
                    <th scope="col">type</th>
                    <th scope="col">status</th>
                    <th scope="col">disk</th>
                    <th scope="col">maxdisk</th>
                    <th scope="col">node</th>
                    <th scope="col">uso</th>
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
                        <td>{{ $storage->node_id }}</td>
                        <td>{{ round($storage->used, 2) * 100 }}%</td>
                        <td>{{ $storage->updated_at }}</td>
                        {{-- <td>
                            <form action="{{ route('table.destroy', $storage->id_proxmox) }}" method="POST">
                                <a href="{{ route('table.show', $storage->id_proxmox) }}" class="btn btn-info">Mostrar</a>

                            </form>
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
