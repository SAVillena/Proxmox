@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Usuarios</h1>


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

        <!-- Mostrar lista de usuarios -->
        <table class="table table-dark table-hover table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <div class="d-flex align-items-center">
                                <a href="{{ route('users.show', $user->id) }}" class="btn btn-primary me-2 flex-grow-1">Ver</a>
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary me-2 flex-grow-1">Editar</a>
                                <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">Eliminar</button>
                                </form>
                            </div>
                        </td>
                        
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Botón para crear un nuevo usuario -->
        <a href="{{ route('users.create') }}" class="btn btn-success">Crear Usuario</a>
    </div>
@endsection
