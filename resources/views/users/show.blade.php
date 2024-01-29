@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('success'))
                    <div class="alert alert-success bg-dark text-white"> <!-- Agrega las clases para el tema oscuro -->
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger bg-dark text-white"> <!-- Agrega las clases para el tema oscuro -->
                        {{ session('error') }}
                    </div>
                @endif
                <div class="card bg-dark text-white"> <!-- Agrega las clases para el tema oscuro -->
                    <div class="card-header">Detalles del Usuario</div>

                    <div class="card-body">
                        <div class="py-2">
                            <a href="{{ route('users.index') }}" class="btn btn-primary">&#8592; Volver</a>
                        </div>
                        <table class="table table-bordered table-dark">
                            <!-- Agrega la clase 'table-dark' para el tema oscuro -->
                            <tbody>
                                <tr>
                                    <th class="table-primary">Usuario</th>
                                    <td>{{ $user->username }}</td>
                                <tr>
                                    <th class="table-primary">Nombre</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th class="table-primary">Email</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <tr>
                                    <th class="table-primary">Rol</th>
                                    <td>{{ $user->roles[0]->name }}</td>
                                <tr>
                                    <th class="table-primary">Creado</th>
                                    <td>{{ $user->created_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
