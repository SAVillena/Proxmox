@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>

                <div class="card-body">
                    {{-- boton para ir a la tabla --}}
                    <a href="{{route('table.index')}}" class="btn btn-primary float-end">Tabla</a>
                    
                    {{-- Ver el boton solo si es admin --}}
                    {{-- @if (Auth::user()->role == 'admin')
                        <a href="{{route('user.index')}}" class="btn btn-primary float-end">Usuarios</a>
                    @endif --}}
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
