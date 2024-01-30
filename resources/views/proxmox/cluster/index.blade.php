@extends('layouts.app')

@section('content')
    <div class="justify-content-start px-3 py-3">
        <h1><strong>Proxmox Data</strong></h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <h2 class="text-center py-3">Cluster Data</h2>
        {{-- Mostrar datos de Cluster --}}
        <table class="table table-dark table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Cantidad de nodos</th>
                    <th scope="col">Nodos</th>
                    <th scope="col">Última actualización</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clusters as $cluster)
                    <tr>
                        <td>{{ $cluster->name }}</td>
                        <td>{{ $cluster->type }}</td>
                        <td class= "text-center">{{ $cluster->node_count }}</td>
                        <td>{{ $cluster->nodes }}</td>
                        <td>{{ \Carbon\Carbon::parse($cluster->updated_at)->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="d-flex justify-content-center">
                                <form action="{{ route('proxmox.cluster.destroy', $cluster->name) }}" method="POST">
                                    <a class="btn btn-secondary btn-sm "
                                        href="/proxmox/cluster/{{ $cluster->name }}">Mostrar</a>
                                    @csrf
                                    @method('DELETE')
                                    @can('manage cluster')
                                        <button type="submit" class="btn btn-danger btn-sm "onclick="return confirm('¿Estás seguro de querer borrar este cluster?');">Borrar</button>
                                    @endcan
                                </form>
                            </div>
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection