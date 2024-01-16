@extends('table.layouts')

@section('content')

<div class= "row justify-content-center mt-3">
    <div class="col-md-8">

        <div class= "card">
            <div class="card-header">
                <div class ="float-start">
                    Informacion
                </div>
            <div class="float-end">
                <a href="{{route('table.index')}}" class="btn btn-primary float-end">&larr; Volver</a>
            </div>
        <div class="card-body">
            {{-- mostrarlo como texto --}}
            <div class="row">
                <label for="id_proxmox" class="col-md-4 col-form-label text-md-end text-start"><strong>id:</strong></label>
                        <div class="col-md-6" style="line-height: 35px;">
                            {{ $tabla->id_proxmox }}
                        </div>
            </div>
            {{-- mostrarlo como input --}}
            <div class="row">
                <label class="col-sm-2 col-form-label">type</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->type}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">status</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->status}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">maxdisk</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->maxdisk}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">disk</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->disk}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">node</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->node}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">uptime</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->uptime}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">mem</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->mem}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">maxmem</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->maxmem}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">maxcpu</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->maxcpu}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">cpu</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->cpu}}" readonly>
                </div>
            </div>
            <div class="row">
                <label class="col-sm-2 col-form-label">level</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" value="{{$tabla->level}}" readonly>
                </div>
            </div>
            
        </div>
    </div>
</div>
@endsection



        

        