<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncargadoController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\InquilinoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PropiedadController;
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
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Inquilinos
        Route::get('/inquilinos', [InquilinoController::class, 'inquilinos'])->name('admin.inquilinos');
        Route::get('/nuevo-inquilino', [InquilinoController::class, 'nuevoInquilino'])->name('admin.nuevo-inquilino');
        Route::post('/nuevo-inquilino', [InquilinoController::class, 'storeInquilino'])->name('admin.store-inquilino');
        Route::get('/inquilino/{id}/editar', [InquilinoController::class, 'editInquilino'])->name('admin.edit-inquilino');
        Route::put('/inquilino/{id}', [InquilinoController::class, 'updateInquilino'])->name('admin.update-inquilino');
        Route::delete('/inquilino/{id}', [InquilinoController::class, 'destroyInquilino'])->name('admin.destroy-inquilino');

        // Pagos
        Route::get('/pagos', [PagoController::class, 'pagos'])->name('admin.pagos');
        Route::get('/pagos/nuevo', [PagoController::class, 'nuevoPago'])->name('admin.nuevo-pago');
        Route::post('/pagos', [PagoController::class, 'storePago'])->name('admin.store-pago');
        Route::get('/pagos/{id}/editar', [PagoController::class, 'editPago'])->name('admin.edit-pago');
        Route::put('/pagos/{id}', [PagoController::class, 'updatePago'])->name('admin.update-pago');
        Route::patch('/pagos/{id}/estado', [PagoController::class, 'quickUpdateEstadoPago'])->name('admin.pagos.quick-estado');
        Route::delete('/pagos/{id}', [PagoController::class, 'destroyPago'])->name('admin.destroy-pago');

        // Notificaciones
        Route::get('/notificaciones', [DashboardController::class, 'notificaciones'])->name('admin.notificaciones');

        // Gestión de Encargados, Propiedades y Habitaciones: exclusiva del Propietario (dueño)
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/encargados', [EncargadoController::class, 'encargados'])->name('admin.encargados');
            Route::get('/nuevo-encargado', [EncargadoController::class, 'nuevoEncargado'])->name('admin.nuevo-encargado');
            Route::post('/nuevo-encargado', [EncargadoController::class, 'storeEncargado'])->name('admin.store-encargado');
            Route::get('/encargado/{id}/editar', [EncargadoController::class, 'editEncargado'])->name('admin.edit-encargado');
            Route::put('/encargado/{id}', [EncargadoController::class, 'updateEncargado'])->name('admin.update-encargado');
            Route::delete('/encargado/{id}', [EncargadoController::class, 'destroyEncargado'])->name('admin.destroy-encargado');

            // Propiedades
            Route::get('/propiedades', [PropiedadController::class, 'propiedades'])->name('admin.propiedades');
            Route::get('/propiedades/nueva', [PropiedadController::class, 'nuevaPropiedad'])->name('admin.nueva-propiedad');
            Route::post('/propiedades', [PropiedadController::class, 'storePropiedad'])->name('admin.store-propiedad');
            Route::get('/propiedades/{id}/editar', [PropiedadController::class, 'editPropiedad'])->name('admin.edit-propiedad');
            Route::put('/propiedades/{id}', [PropiedadController::class, 'updatePropiedad'])->name('admin.update-propiedad');
            Route::delete('/propiedades/{id}', [PropiedadController::class, 'destroyPropiedad'])->name('admin.destroy-propiedad');

            // Habitaciones
            Route::get('/habitaciones', [HabitacionController::class, 'habitaciones'])->name('admin.habitaciones');
            Route::get('/habitaciones/nueva', [HabitacionController::class, 'nuevaHabitacion'])->name('admin.nueva-habitacion');
            Route::post('/habitaciones', [HabitacionController::class, 'storeHabitacion'])->name('admin.store-habitacion');
            Route::get('/habitaciones/{id}/editar', [HabitacionController::class, 'editHabitacion'])->name('admin.edit-habitacion');
            Route::put('/habitaciones/{id}', [HabitacionController::class, 'updateHabitacion'])->name('admin.update-habitacion');
            Route::delete('/habitaciones/{id}', [HabitacionController::class, 'destroyHabitacion'])->name('admin.destroy-habitacion');
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
