<?php

namespace App\Http\Controllers;

use App\Models\QemuDeleted;
use Illuminate\Http\Request;

class QemuDeletedController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $QemuDeleteds = QemuDeleted::all();
        return view('proxmox.QemuDeleted.index', compact('QemuDeleteds'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(QemuDeleted $qemuDeleted)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(QemuDeleted $qemuDeleted)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, QemuDeleted $qemuDeleted)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(QemuDeleted $qemuDeleted)
    {
        //
    }
}
