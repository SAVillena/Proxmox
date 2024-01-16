<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\tabla;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\StoreTableRequest;
use App\Http\Requests\UpdateTableRequest;


class TablaController extends Controller
{
    //
    public function index()
    {
        $tablas = tabla::all();
        return view('table.index', ['tablas' => $tablas]);
    }

    
    public function create()
    {
        return view('table.create');
    }
    
    public function show($id_proxmox)
    {
        $tabla = tabla::find($id_proxmox);
        return view('table.show', compact('tabla'));
    }

/*     public function store(Request $request)
    {
        $tabla = new tabla();
        $tabla->id_proxmox = $request->id_proxmox;
        $tabla->type = $request->type;
        $tabla->status = $request->status;
        $tabla->maxdisk = $request->maxdisk;
        $tabla->disk = $request->disk;
        $tabla->node = $request->node;
        $tabla->uptime = $request->uptime;
        $tabla->cgroup_mode = $request->cgroup_mode;
        $tabla->mem = $request->mem;
        $tabla->maxmem = $request->maxmem;
        $tabla->maxcpu = $request->maxcpu;
        $tabla->cpu = $request->cpu;
        $tabla->level = $request->level;
        $tabla->save();
        return redirect()->route('table.index');
    } */


     public function store(StoreTableRequest $request) : RedirectResponse
    {
        tabla::create($request ->all());
        return redirect()->route('table.index')->withSuccess('Fue añadido a la tabla correctamente');

    } 

    /* public function store($request) : RedirectResponse
    {
        $tabla = new tabla();
        $tabla->id_proxmox = $request->id_proxmox;
        $tabla->type = $request->type;
        $tabla->status = $request->status;
        $tabla->maxdisk = $request->maxdisk;
        $tabla->disk = $request->disk;
        $tabla->node = $request->node;
        $tabla->uptime = $request->uptime;
        $tabla->mem = $request->mem;
        $tabla->maxmem = $request->maxmem;
        $tabla->maxcpu = $request->maxcpu;
        $tabla->cpu = $request->cpu;
        $tabla->level = $request->level;
        $tabla->save();
        return redirect()->route('table.index')->withSuccess('Fue añadido a la tabla correctamente');

    }
 */
    public function edit($id)
    {
        $tabla = tabla::find($id);
        return view('table.edit', ['tabla' => $tabla]);
    }

    public function update(UpdateTableRequest $request, tabla $tabla) : RedirectResponse
    {
        // $tabla->update($request->all());
        $tabla->update([
            'id_proxmox' => $request->id_proxmox,
            'type' => $request->type,
            'status' => $request->status,
            'maxdisk' => $request->maxdisk,
            'disk' => $request->disk,
            'node' => $request->node,
            'uptime' => $request->uptime,
  /*           'cgroup_mode' => $request->cgroup_mode, */
            'mem' => $request->mem,
            'maxmem' => $request->maxmem,
            'maxcpu' => $request->maxcpu,
            'cpu' => $request->cpu,
            'level' => $request->level,
        ]);
        return redirect()->route('table.index')->withSuccess('Fue actualizado correctamente');
    }

    public function destroy($id)
    {
        $tabla = tabla::find($id);
        $tabla->delete();
        return redirect()->route('table.index')->withSuccess('Fue eliminado correctamente');
    }


}
