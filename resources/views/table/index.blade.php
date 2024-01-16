@extends('table.layouts')

@section('content')

<div class= "row justify-content-center mt-3">
    <div class="col-md-12">

        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{$message}}</p>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <h3 class ="text-center">Tabla de prueba</h3>
            </div>
            <div class="card-body">
                <a href="{{route('table.create')}}" class="btn btn-primary">Crear nuevo registro</a>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th scope ="col">id</th>
                                <th scope ="col">type</th>
                                <th scope ="col">status</th>
                                <th scope ="col">maxdisk</th>
                                <th scope ="col">disk</th>
                                <th scope ="col">node</th>
                                <th scope ="col">uptime</th>
                                <th scope ="col">mem</th>
                                <th scope ="col">maxmem</th>
                                <th scope ="col">maxcpu</th>
                                <th scope ="col">cpu</th>
                                <th scope ="col">level</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($tablas as $table)
                                <tr>
                                    <td>{{$table->id_proxmox}}</td>
                                    <td>{{$table->type}}</td>
                                    <td>{{$table->status}}</td>
                                    <td>{{$table->maxdisk}}</td>
                                    <td>{{$table->disk}}</td>
                                    <td>{{$table->node}}</td>
                                    <td>{{$table->uptime}}</td>
                                    <td>{{$table->mem}}</td>
                                    <td>{{$table->maxmem}}</td>
                                    <td>{{$table->maxcpu}}</td>
                                    <td>{{$table->cpu}}</td>
                                    <td>{{$table->level}}</td>

                                    <td>
                                        <form action="{{route('table.destroy', $table->id_proxmox)}}" method="POST">
                                            <a href="{{route('table.show', $table->id_proxmox)}}" class="btn btn-info">Mostrar</a>
                                            <a href="{{route('table.edit', $table->id_proxmox)}}" class="btn btn-primary">Editar</a>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Borrar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{-- {{ $table->links()}} --}}
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
