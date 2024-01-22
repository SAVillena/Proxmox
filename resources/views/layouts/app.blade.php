<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Data') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    @yield('css')
    
    
    
    
    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    
</head>

<body>
    <div id="app">
        <nav class="navbar navbar-expand-md navbar-dark bg-dark shadow-sm">
            <div class="container">
                <!-- Logo a la izquierda -->
                <div class="navbar-header">
                    <a class="navbar-brand" href="{{ url('/') }}">
                        <img src="{{ asset('holdco.jpeg') }}" alt="" width="300" height="100">
                    </a>
                </div>
        
                <!-- Botones centrados -->
                <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a href="{{ url('/proxmox') }}" class="nav-link">Datos</a></li>
                        <li class="nav-item"><a href="{{ url('/proxmox/node') }}" class="nav-link">Nodo</a></li>
                        <li class="nav-item"><a href="{{ url('/proxmox/qemu') }}" class="nav-link">VM</a></li>
                        <li class="nav-item"><a href="{{ url('/proxmox/storage') }}" class="nav-link">Storage</a></li>
                        <li class="nav-item"><a href="{{ url('/proxmox/history') }}" class="nav-link">Historico</a></li>
                        <li class="nav-item"><a href="{{ url('/proxmox/QemuDeleted') }}" class="nav-link">VM Eliminadas</a></li>
                    </ul>
                </div>
        
                <!-- Botones a la derecha -->
                <ul class="navbar-nav ml-auto">
                    <div class="btn-group btn-group-lg" role="group" aria-label="Large button group">
                        {{-- boton para ir a /proxmox/fetch --}}
                        <a href="{{ url('/proxmox/fetch') }}" class="btn btn-success mb-3">Actualizar</a>
                        
                        {{-- boton para ir a /proxmox/cluster/create --}}
                        <a href="{{ url('/proxmox/cluster/create') }}" class="btn btn-success mb-3">Crear
                            Cluster</a>
                        </div>
                        
                    </ul>
            </div>
        </nav>
        
        
        <main class="py-4">
            @yield('content')
            @yield('script')
        </main>
    </div>
</body>

</html>
