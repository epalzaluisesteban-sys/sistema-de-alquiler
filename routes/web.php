<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PropietarioController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\LoginController;

// 1. Ruta principal: Muestra el Login directamente al entrar al sitio
// 1. Ruta principal (Raíz): Al entrar a la dirección de la consola, mostrará el Login.
Route::get('/', function () {
    if (Auth::check()) {
        $role = strtolower(trim(Auth::user()->role ?? ''));

        return in_array($role, ['admin', 'encargado'])
            ? redirect()->route('admin.dashboard')
            : redirect()->route('tenant.dashboard');
    }
    return view('auth.login');
})->name('login');

// Ruta para procesar el inicio de sesión (el botón "Entrar")
// Ruta para procesar el envío del formulario (POST)
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Agrupamos las rutas para que requieran haber iniciado sesión ('auth')
Route::middleware(['auth'])->group(function () {

    // Ruta de logout accesible para cualquier usuario autenticado
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // -------------------------------------------------------------
    // RUTAS DEL ADMINISTRADOR (Propietario y Encargado)
    // Protegidas por el middleware 'role:admin,encargado'
    // -------------------------------------------------------------
    Route::middleware(['role:admin,encargado'])->prefix('admin')->group(function () {

        // Carga la vista del dashboard del propietario / encargado
        Route::get('/dashboard', [PropietarioController::class, 'index'])->name('admin.dashboard');

        // Inquilinos
        Route::get('/inquilinos', [PropietarioController::class, 'inquilinos'])->name('admin.inquilinos');
        Route::get('/nuevo-inquilino', [PropietarioController::class, 'nuevoInquilino'])->name('admin.nuevo-inquilino');
        Route::post('/nuevo-inquilino', [PropietarioController::class, 'storeInquilino'])->name('admin.store-inquilino');
        Route::get('/inquilino/{id}/editar', [PropietarioController::class, 'editInquilino'])->name('admin.edit-inquilino');
        Route::put('/inquilino/{id}', [PropietarioController::class, 'updateInquilino'])->name('admin.update-inquilino');
        Route::delete('/inquilino/{id}', [PropietarioController::class, 'destroyInquilino'])->name('admin.destroy-inquilino');

        // Pagos
        Route::get('/pagos', [PropietarioController::class, 'pagos'])->name('admin.pagos');
        Route::get('/pagos/nuevo', [PropietarioController::class, 'nuevoPago'])->name('admin.nuevo-pago');
        Route::post('/pagos', [PropietarioController::class, 'storePago'])->name('admin.store-pago');
        Route::get('/pagos/{id}/editar', [PropietarioController::class, 'editPago'])->name('admin.edit-pago');
        Route::put('/pagos/{id}', [PropietarioController::class, 'updatePago'])->name('admin.update-pago');
        Route::patch('/pagos/{id}/estado', [PropietarioController::class, 'quickUpdateEstadoPago'])->name('admin.pagos.quick-estado');
        Route::delete('/pagos/{id}', [PropietarioController::class, 'destroyPago'])->name('admin.destroy-pago');

        // Notificaciones
        Route::get('/notificaciones', [PropietarioController::class, 'notificaciones'])->name('admin.notificaciones');

        // Gestión de Encargados, Propiedades y Habitaciones: exclusiva del Propietario (dueño)
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/encargados', [PropietarioController::class, 'encargados'])->name('admin.encargados');
            Route::get('/nuevo-encargado', [PropietarioController::class, 'nuevoEncargado'])->name('admin.nuevo-encargado');
            Route::post('/nuevo-encargado', [PropietarioController::class, 'storeEncargado'])->name('admin.store-encargado');
            Route::get('/encargado/{id}/editar', [PropietarioController::class, 'editEncargado'])->name('admin.edit-encargado');
            Route::put('/encargado/{id}', [PropietarioController::class, 'updateEncargado'])->name('admin.update-encargado');
            Route::delete('/encargado/{id}', [PropietarioController::class, 'destroyEncargado'])->name('admin.destroy-encargado');

            // Propiedades
            Route::get('/propiedades', [PropietarioController::class, 'propiedades'])->name('admin.propiedades');
            Route::get('/propiedades/nueva', [PropietarioController::class, 'nuevaPropiedad'])->name('admin.nueva-propiedad');
            Route::post('/propiedades', [PropietarioController::class, 'storePropiedad'])->name('admin.store-propiedad');
            Route::get('/propiedades/{id}/editar', [PropietarioController::class, 'editPropiedad'])->name('admin.edit-propiedad');
            Route::put('/propiedades/{id}', [PropietarioController::class, 'updatePropiedad'])->name('admin.update-propiedad');
            Route::delete('/propiedades/{id}', [PropietarioController::class, 'destroyPropiedad'])->name('admin.destroy-propiedad');

            // Habitaciones
            Route::get('/habitaciones', [PropietarioController::class, 'habitaciones'])->name('admin.habitaciones');
            Route::get('/habitaciones/nueva', [PropietarioController::class, 'nuevaHabitacion'])->name('admin.nueva-habitacion');
            Route::post('/habitaciones', [PropietarioController::class, 'storeHabitacion'])->name('admin.store-habitacion');
            Route::get('/habitaciones/{id}/editar', [PropietarioController::class, 'editHabitacion'])->name('admin.edit-habitacion');
            Route::put('/habitaciones/{id}', [PropietarioController::class, 'updateHabitacion'])->name('admin.update-habitacion');
            Route::delete('/habitaciones/{id}', [PropietarioController::class, 'destroyHabitacion'])->name('admin.destroy-habitacion');
        });
    });

    // -------------------------------------------------------------
    // RUTAS DEL INQUILINO / RESIDENTE
    // Protegidas por el middleware 'role:tenant'
    // -------------------------------------------------------------
    Route::middleware(['role:tenant'])->prefix('residente')->group(function () {

        // Carga la vista del dashboard del inquilino
        Route::get('/dashboard', [TenantController::class, 'index'])->name('tenant.dashboard');

        Route::get('/residencias', [TenantController::class, 'residencias'])->name('tenant.residencias');
    });

});
