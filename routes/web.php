<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloWorld;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TablaController;
use App\Http\Controllers\ProxmoxController;
use App\Http\Controllers\QemuDeletedController;
use App\Http\Controllers\VirtualMachineHistoryController;
use App\Models\QemuDeleted;
// use auth
use Illuminate\Support\Facades\Auth;




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



Route::get('/login', function () { return view('auth.login'); })->name('viewLogin');

Route::post('/login/auth', [LoginController::class, 'loginUser'])->name('login');

Route::get('/', [ProxmoxController::class, 'home'])->name('proxmox.home') ->middleware('auth:api');

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
Route::get('/proxmox/node/search', [ProxmoxController::class, 'searchNode'])->name('proxmox.searchNode');
Route::get('/proxmox/node/{node}', [ProxmoxController::class, 'showByIdNode'])->name('proxmox.cluster.node.show');

Route::get('/proxmox/export', [ProxmoxController::class, 'exportQemuCSV'])->name('proxmox.export');
//buscarQemu
Route::get('/proxmox/qemu/search', [ProxmoxController::class, 'searchQemu'])->name('proxmox.searchQemu');
Route::get('/proxmox/storage/search', [ProxmoxController::class, 'searchStorage'])->name('proxmox.searchStorage');



Route::delete('/proxmox/qemu', [ProxmoxController::class, 'destroyQemu'])->name('proxmox.qemu.destroy');
Route::get('/proxmox/QemuDeleted', [QemuDeletedController::class, 'index'])->name('proxmox.qemuDeleted');

Route::get('/proxmox/history', [VirtualMachineHistoryController::class, 'indexMonthly'])->name('proxmox.history'); 
Route::get('/proxmox/historyAnual', [VirtualMachineHistoryController::class, 'indexAnual'])->name('proxmox.historyAnual'); 

Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');



