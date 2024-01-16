<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloWorld;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TablaController;
use App\Http\Controllers\ProxmoxController;
// use auth
use Illuminate\Support\Facades\Auth;

use App\Models\tabla as ModelsTabla;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/



Auth::routes(['register' => false]);

Route::get('/proxmox/fetch', [ProxmoxController::class, 'getData']);
Route::get('/proxmox', [ProxmoxController::class, 'index'])->name('proxmox.index');
Route::get('/proxmox/node', [ProxmoxController::class, 'node'])->name('proxmox.node');

Route::get('/proxmox/qemu', [ProxmoxController::class, 'qemu'])->name('proxmox.qemu');
Route::get('/proxmox/storage', [ProxmoxController::class, 'storage'])->name('proxmox.storage');
// Route::delete('/proxmox/{name}', [ProxmoxController::class, 'delete'])->name('proxmox.destroy');
// crear ruta de crear cluster
Route::get('/proxmox/cluster/create', [ProxmoxController::class, 'createCluster'])->name('proxmox.cluster.create');
Route::post('/proxmox/cluster/', [ProxmoxController::class, 'storeCluster'])->name('proxmox.cluster.store');

Route::get('/proxmox/cluster/{name}', [ProxmoxController::class, 'showByIdCluster'])->name('proxmox.cluster.show');
Route::delete('/proxmox/cluster/{name}', [ProxmoxController::class, 'destroyCluster'])->name('proxmox.cluster.destroy');

Route::delete('/proxmox/node/{node}', [ProxmoxController::class, 'destroyNode'])->name('proxmox.cluster.node.destroy');
Route::get('/proxmox/node/{node}', [ProxmoxController::class, 'showByIdNode'])->name('proxmox.cluster.node.show');

Route::get('/proxmox/export', [ProxmoxController::class, 'exportQemuCSV'])->name('proxmox.export');
//buscarQemu
Route::get('/proxmox/qemu/search', [ProxmoxController::class, 'searchQemu'])->name('proxmox.searchQemu');



Route::get('/home', [App\Http\Controllers\ProxmoxController::class, 'home'])->name('proxmox.home');


Route::get('/', function () {
    return view('welcome');
});

// Route::resource('table', TablaController::class);



