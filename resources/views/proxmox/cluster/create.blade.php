@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center text-dark mb-0">Agregar Cluster/Nodo de Proxmox</h3>
                    </div>
                    <div class="card-body">
                        <!-- Mensajes de error de validación -->
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('proxmox.cluster.store') }}" method="POST">
                            @csrf

                            <div class="form-floating mb-3">
                                <input type="text" 
                                       class="form-control @error('ip') is-invalid @enderror" 
                                       id="ip" 
                                       placeholder="192.168.1.100" 
                                       name="ip" 
                                       value="{{ old('ip') }}"
                                       required>
                                <label for="ip">Dirección IP del Cluster</label>
                                @error('ip')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="text" 
                                       class="form-control @error('username') is-invalid @enderror" 
                                       id="username" 
                                       placeholder="root" 
                                       name="username" 
                                       value="{{ old('username') }}"
                                       required>
                                <label for="username">Usuario</label>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-floating mb-3">
                                <input type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       id="password" 
                                       placeholder="Contraseña" 
                                       name="password"
                                       required>
                                <label for="password">Contraseña</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('proxmox.home') }}" class="btn btn-secondary me-md-2">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </a>
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-plus"></i> Agregar Cluster/Nodo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
