<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HelloWorld;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TablaController

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/a', function() {
    return('a');
});

Route::get('/helloworld', 'App\Http\Controllers\HelloWorld@sayHello');

Route::get('/tabla', function() {
    $tablas = ModelsTabla::where('id', '>', 0)->get();
    dd($tablas);
});

Route::get('/table', 'App\Http\Controllers\Tabla@index')->name('table.index');
/* Route::get('/table/create', 'App\Http\Controllers\Tabla@create')->name('table.create');
Route::post('/table', 'App\Http\Controllers\Tabla@store')->name('table.store');
Route::get('/table/{id}', 'App\Http\Controllers\Tabla@show')->name('table.show');
 */