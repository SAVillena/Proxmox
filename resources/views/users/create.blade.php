@extends('layouts.app')

@section('content')
    <div class = "container">
       
        <h1>Crear Usuario</h1>
        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Whoops!</strong> Hubo un problema con la creación del usuario.<br><br>
                <ul>
                   @foreach ($errors->all()  as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul> 
            </div>
        @endif
        <form class= "row" action="{{ route('users.store') }}" method="POST">
            @csrf
           <div class="form-floating mb-3">
                <input type="text" class="form-control" name="username" id="floatingInput" placeholder="Username">
                <label for="floatingInput">Usuario</label> 
            </div>
            <div class="form-floating mb-3">
                <input type="text" class="form-control" name="name" id="floatingInput" placeholder="Name">
                <label for="floatingInput">Nombre Completo</label>
            </div>
            <div class="form-floating mb-3">
                <input type="email" class="form-control" name="email" id="floatingInput" placeholder="Email">
                <label for="floatingInput">Email</label>
            </div>
            <div class="form-floating mb-3">
                <input type="password" class="form-control" name="password" id="floatingInput" placeholder="Password">
                <label for="floatingInput">Contraseña</label>
            </div>
            <div class="form-floating mb-3">
                <select class="form-select" name="role" id="floatingSelect" aria-label="Floating label select example">
                    <option selected>Selecciona un Rol</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="btn btn-primary">Crear Usuario</button>
            </div>
        </form>
    </div>
@endsection
