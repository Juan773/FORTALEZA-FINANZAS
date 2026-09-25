<?php

use App\Http\Controllers\Auth\LoginController;
use App\Livewire\Auditoria\Index as AuditoriaIndex;
use App\Livewire\Balances\Index as BalancesIndex;
use App\Livewire\Caja\ListaRecibos;
use App\Livewire\Caja\NuevoRecibo;
use App\Livewire\CargarDeuda\Index as CargarDeudaIndex;
use App\Livewire\Compras\Index as ComprasIndex;
use App\Livewire\Conceptos\Index as ConceptosIndex;
use App\Livewire\EstadoCuenta\Index as EstadoCuentaIndex;
use App\Livewire\Home\Index as HomeIndex;
use App\Livewire\Perfiles\Index as PerfilesIndex;
use App\Livewire\Proveedores\Index as ProveedoresIndex;
use App\Livewire\Socios\Index as SociosIndex;
use App\Livewire\Usuarios\Index as UsuariosIndex;
use App\Http\Controllers\Pdf\EstadoCuentaPdfController;
use App\Http\Controllers\Pdf\ReciboPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware(['guest', 'throttle:5,1']);
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');

Route::get('/home', HomeIndex::class)->middleware('auth');

Route::get('/socios', SociosIndex::class)->middleware('auth');
Route::get('/conceptos', ConceptosIndex::class)->middleware('auth');
Route::get('/proveedores', ProveedoresIndex::class)->middleware('auth');
Route::get('/compras', ComprasIndex::class)->middleware('auth');
Route::get('/estado-cuenta', EstadoCuentaIndex::class)->middleware('auth');
Route::get('/balances', BalancesIndex::class)->middleware('auth');
Route::get('/caja', ListaRecibos::class)->middleware('auth');
Route::get('/caja/nuevo', NuevoRecibo::class)->middleware('auth');
Route::get('/cargar-deuda', CargarDeudaIndex::class)->middleware('auth');
Route::get('/caja/{ccCaja}/pdf', ReciboPdfController::class)->middleware('auth');
Route::get('/estado-cuenta/pdf', EstadoCuentaPdfController::class)->middleware('auth');
Route::get('/usuarios', UsuariosIndex::class)->middleware(['auth', 'administrador']);
Route::get('/perfiles', PerfilesIndex::class)->middleware(['auth', 'administrador']);
Route::get('/auditoria', AuditoriaIndex::class)->middleware(['auth', 'administrador']);
