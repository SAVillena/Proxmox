@extends('layouts.app')

@section('content')
    <div class= "container">
        <h1>Create Cluster</h1>

        <form class= "row" action="/proxmox/cluster" method="POST">
            @csrf

            <div class="container">
                <div class="form-floating mb-3">
                    <input type="ip" class="form-control" id="floatingInput" placeholder="IP" name="ip">
                    <label for="floatingInput">IP</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="username" class="form-control" id="floatingInput" placeholder="Username" name="username">
                    <label for="floatingInput">Usuario</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="floatingInput" placeholder="Password" name="password">
                    <label for="floatingInput">Contraseña</label>
                </div>
            </div>

            <div>
                <button class="btn btn-primary mb-3" type="submit">Agregar Cluster</button>
            </div>
        </form>

    </div>
@endsection
