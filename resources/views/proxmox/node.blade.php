@extends('layouts.app')
@section('content')
    <div class = "container">

        <h2>Node Data</h2>
        {{-- Mostrar datos de Node --}}
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">cluster name</th>
                    <th scope="col">id proxmox</th>
                    <th scope="col">type</th>
                    <th scope="col">status</th>
                    <th scope="col">disk</th>
                    <th scope="col">maxdisk</th>
                    <th scope="col">node</th>
                    <th scope="col">uptime</th>
                    <th scope="col">mem</th>
                    <th scope="col">maxmem</th>
                    <th scope="col">cpu</th>
                    <th scope="col">maxcpu</th>
                    <th scope="col">updated_at</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($nodes as $node)
                    <tr>
                        <td>{{ $node->cluster_name }}</td>
                        <td>{{ $node->id_proxmox }}</td>
                        <td>{{ $node->type }}</td>
                        <td>{{ $node->status }}</td>
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
                        <td>{{ $node->node }}</td>
                        <td>{{ $node->uptime }}</td>
                        <td>{{ $node->mem }}</td>
                        <td>{{ $node->maxmem }}</td>
                        <td>{{ $node->cpu }}</td>
                        <td>{{ $node->maxcpu }}</td>
                        <td>{{ $node->updated_at }}</td>
                       {{--  <td>
                            <a href="{{ route('table.show', $node->id_proxmox) }}" class="btn btn-info">Mostrar</a>
                        </td> --}}
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
