@extends('table.layouts')

@section('content')

<div class= "row justify-content-center mt-3">
    <div class="col-md-8">

        <div class= "card">
            <div class="card-header">
                <div class ="float-start">
                    <h3>Crear</h3>
                </div>
                <div class="float-end">
                    <a href="{{route('table.index')}}" class="btn btn-primary float-end">&larr; Volver</a>
                </div>
            </div>
        <div class="card-body">
            <form action="{{route('table.store')}}" method="POST">
                @csrf
                <div class="row">
                    <label class="col-sm-2 col-form-label">id</label>
                    <div class="col-sm-6">
                        <input type="string" class="form-control" name="id_proxmox" placeholder="id">
                    </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">type</label>
                    <div class="col-sm-6">
                        <select class="form-control" name="status" placeholder="type">
                            <option selected value="qemu">qemu</option>
                            <option value="storage">storage</option>
                            <option value="node">node</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">status</label>
                    <div class="col-sm-6">
                        <select class="form-control" name="status" placeholder="status">

                            <option selected value="running">running</option>
                            <option value="eliminado">eliminado</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">maxdisk</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="maxdisk" placeholder="maxdisk">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">disk</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="disk" placeholder="disk">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">node</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="node" placeholder="node"onkeypress="return (even.charCode >=65 && event.charCode <=90 || event.charCode >= 97 && event.charCode <= 122)">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">uptime</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="uptime" placeholder="uptime">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">mem</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="mem" placeholder="mem">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">maxmem</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="maxmem" placeholder="maxmem">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">maxcpu</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="maxcpu" placeholder="maxcpu">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">Porcentaje CPU</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="cpu" placeholder="cpu">
                    </div>
                </div>
                <div class="row">
                    <label class="col-sm-2 col-form-label">level</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="level" placeholder="level">
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
@endsection
                