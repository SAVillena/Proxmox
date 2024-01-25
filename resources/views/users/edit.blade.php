@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark text-white"> <!-- Agrega la clase 'bg-dark' para el fondo oscuro -->
                <div class="card-header">{{ __('Editar Usuario') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('users.update', $user->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group row py-1">
                            <label for="username" class="col-md-4 col-form-label text-md-right">{{ __('Usuario') }}</label>

                            <div class="col-md-6">
                                <input id="username" type="text" placeholder="Dejar en blanco para no cambiar el usuario" class="form-control @error('username') is-invalid @enderror bg-dark text-white" name="username" value="{{ old('username', $user->username) }}" autocomplete="username" autofocus>

                                @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>El usuario debe tener al menos 3 caracteres y ser único.</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row py-1">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Nombre') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control @error('name') is-invalid @enderror bg-dark text-white" name="name" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row py-1">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('Correo Electrónico') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror bg-dark text-white" name="email" value="{{ old('email', $user->email) }}" required autocomplete="email">

                                @error('email')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Contraseña (opcional)') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" placeholder="Dejar en blanco para no cambiar la contraseña" class="form-control @error('password') is-invalid @enderror bg-dark text-white" name="password" autocomplete="new-password">
                               
                                @error('password')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>La contraseña debe tener al menos 8 caracteres y coincidir con la confirmación.</strong>
                                    </span>
                                @enderror
                            </div>

                        <div class="form-group row mb-0 py-3">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Guardar Cambios') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
