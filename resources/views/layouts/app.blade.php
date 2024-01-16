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
                <a class="navbar-brand" href="{{ url('/home') }}">
                    {{ config('app.name', 'Proxmox') }}
                </a>
                
                
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                
                
                {{-- Texto para ir a /proxmox/node --}}
                <a href="{{ url('/proxmox') }}" class="navbar-brand">Data</a>
                <a href="{{ url('/proxmox/node') }}" class="navbar-brand">Nodo</a>
                <a href="{{ url('/proxmox/qemu') }}" class="navbar-brand">Qemu</a>
                <a href="{{ url('/proxmox/storage') }}" class="navbar-brand">Storage</a>


                {{-- introducir imagen en el centro --}}
               
                {{-- introducir imagen que esta en carpeta public--}}
                <a class="navbar-brand ms-auto">
                    <img src="{{ asset('holdco.jpeg') }}" alt="" width="300" height="100">
                </a>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <ul class="navbar-nav me-auto">
                        <div class="btn-group btn-group-lg" role="group" aria-label="Large button group">
                            {{-- boton para ir a /proxmox/fetch --}}
                            <a href="{{ url('/proxmox/fetch') }}" class="btn btn-success mb-3">Actualizar</a>
                            
                            {{-- boton para ir a /proxmox/cluster/create --}}
                            <a href="{{ url('/proxmox/cluster/create') }}" class="btn btn-success mb-3">Crear
                                Cluster</a>
                            </div>
                            
                        </ul>
                        
                    </ul>
                </div>
            </div>
        </nav>
        
        <main class="py-4">
            @yield('content')
            @yield('script')
        </main>
    </div>
</body>

</html>
