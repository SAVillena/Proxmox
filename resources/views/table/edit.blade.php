@extends('table.layouts')

@section('content')

<div class= "row justify-content-center mt-3">
    <div class="col-md-8">

        @if ($message = Session::get('success'))
            <div class="alert alert-success">
                <p>{{$message}}</p>
            </div>
        @endif
        <div class= "card">
            <div class="card-header">
                <div class ="float-start">
                    <h3>Editar</h3>
                </div>
                <div class="float-end">
                    <a href="{{route('table.index')}}" class="btn btn-primary float-end">&larr; Volver</a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{route('table.update', $tabla->id_proxmox)}}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <label class="col-sm-2 col-form-label">id proxmox</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="id_proxmox" value="{{$tabla->id_proxmox}}">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">id</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="id_proxmox" value="{{$tabla->id}}">
                        </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">type</label>
                        <div class="col-sm-6">
                            <select class="form-control" name="status" value="{{$tabla->type}}">
                                <option selected value="qemu">qemu</option>
                                <option value="storage">storage</option>
                                <option value="node">node</option>
                            </select>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">status</label>
                        <div class="col-sm-6">
                            <select class="form-control" name="status" value="{{$tabla->status}}">
    
                                <option selected value="running">running</option>
                                <option value="eliminado">eliminado</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">maxdisk</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="maxdisk" value="{{$tabla->maxdisk}}">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">disk</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="disk" value="{{$tabla->disk}}">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">node</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="node" value="{{$tabla->node}}" onkeypress="return (even.charCode >=65 && event.charCode <=90 || event.charCode >= 97 && event.charCode <= 122)">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">uptime</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="uptime" value="{{$tabla->uptime}}">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">mem</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="mem" value="{{$tabla->mem}}">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">maxmem</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="maxmem" value="{{$tabla->maxmem}}">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">maxcpu</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="maxcpu" value="{{$tabla->maxcpu}}">
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">Porcentaje CPU</label>
                        <div class="col-sm-6">
                            <input type="number" class="form-control" name="cpu" value="{{$tabla->cpu}}" max=1 min=0 step=0.1>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-2 col-form-label">level</label>
                        <div class="col-sm-6">
                            <input type="text" class="form-control" name="level" value="{{$tabla->level}}">
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-6">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
