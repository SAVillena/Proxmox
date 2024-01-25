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
                        <img src="{{ asset('holdco.png') }}" alt="" width="210" height="70">
                    </a>
                </div>
                @can('view cluster')
                    <!-- Botones centrados -->
                    <div class="collapse navbar-collapse justify-content-center" id="navbarSupportedContent">
                        <ul class="navbar-nav">
                            <li class="nav-item"><a href="{{ url('/proxmox') }}" class="nav-link">Datos</a></li>
                            <li class="nav-item"><a href="{{ url('/proxmox/node') }}" class="nav-link">Nodo</a></li>
                            <li class="nav-item"><a href="{{ url('/proxmox/qemu') }}" class="nav-link">VM</a></li>
                            <li class="nav-item"><a href="{{ url('/proxmox/storage') }}" class="nav-link">Storage</a></li>
                            <li class="nav-item"><a href="{{ url('/proxmox/history') }}" class="nav-link">Historico</a></li>
                            <li class="nav-item"><a href="{{ url('/proxmox/QemuDeleted') }}" class="nav-link">VM
                                    Eliminadas</a></li>
                            @can('manage users')
                                <li class="nav-item"><a href="{{ url('/users') }}" class="nav-link">Usuarios</a></li>
                            @endcan
                        </ul>
                    </div>

                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    Cerrar Sesión
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                        <!-- Botones a la derecha -->
                        @can('manage cluster')
                        <ul class="navbar-nav ml-auto">
                            <div class="btn-group btn-group-lg" role="group" aria-label="Large button group">
                                {{-- boton para ir a /proxmox/fetch --}}
                                <a href="{{ url('/proxmox/fetch') }}" class="btn btn-success mb-3">Actualizar</a>

                                {{-- boton para ir a /proxmox/cluster/create --}}
                                <a href="{{ url('/proxmox/cluster/create') }}" class="btn btn-success mb-3">Crear
                                    Cluster</a>
                            </div>
                        </ul>
                        @endcan
                    @endcan
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
