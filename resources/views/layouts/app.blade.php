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
<style>
    body {
        background-color: #343a40;
    }

    h1 {
        color: #fff;
    }

    h2 {
        color: #fff;
    }

    h3 {
        color: #fff;
    }

    h4 {
        color: #fff;
    }

    h5 {
        color: #fff;
    }

    p {
        color: #fff;
    }
</style>

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
                        <ul class="navbar-nav px-3">
                            <li class="nav-item px-1"><a href="{{ url('/proxmox') }}" class="btn btn-success">Datos</a></li>
                            <li class="nav-item px-1"><a href="{{ url('/proxmox/cluster') }}"
                                    class="btn btn-success">Cluster</a>
                            <li class="nav-item px-1"><a href="{{ url('/proxmox/node') }}" class=" btn btn-success">Nodo</a>
                            </li>
                            <li class="nav-item px-1"><a href="{{ url('/proxmox/qemu') }}" class=" btn btn-success">VM</a>
                            </li>
                            <li class="nav-item px-1"><a href="{{ url('/proxmox/storage') }}"
                                    class=" btn btn-success">Storage</a></li>
                            <li class="nav-item px-1"><a href="{{ url('/proxmox/history') }}"
                                    class=" btn btn-success">Historico</a></li>
                            <li class="nav-item px-1"><a href="{{ url('/proxmox/QemuDeleted') }}"
                                    class=" btn btn-success">Eliminado</a></li>
                            {{-- aqui --}}
                            <div class="btn-group">
                                @can('manage cluster')
                                    <button type="button" class="btn btn-danger dropdown-toggle" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        Administrar
                                    </button>
                                    <ul class="dropdown-menu">
                                        @can('manage users')
                                            <li><a class="dropdown-item" href="{{ url('/users') }}">Usuarios</a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                        @endcan
                                        @can('manage cluster')
                                            <li><a class="dropdown-item" href="{{ url('/proxmox/fetch') }}">Actualizar</a></li>
                                            <li><a class="dropdown-item" href="{{ url('/proxmox/cluster/create') }}">Crear cluster</a></li>
                                        @endcan
                                    </ul>
                                @endcan
                            </div>


                        </ul>
                    </div>

                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        <!-- Botones a la derecha -->
                       {{--  @can('manage cluster')
                            <ul class="navbar-nav ml-auto">
                                <div class="btn-group btn-group-md" role="group" aria-label="Large button group">
                                    <a href="{{ url('/proxmox/fetch') }}" class="btn btn-success mb-3">Actualizar</a>

                                    <a href="{{ url('/proxmox/cluster/create') }}" class="btn btn-success mb-3">Crear
                                        Cluster</a>
                                </div>
                            </ul>
                        @endcan --}}
                            {{-- aca --}}
                            @guest
                                @if (Route::has('login'))
                                    <li class="nav-item px-1">
                                        <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                                    </li>
                                @endif

                                @if (Route::has('register'))
                                    <li class="nav-item px-1">
                                        <a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a>
                                    </li>
                                @endif
                            @else
                                <li class="nav-item px-1 dropdown">
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
