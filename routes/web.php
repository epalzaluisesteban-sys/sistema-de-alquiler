<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
// Controladores del panel admin (compartidos por Propietario y Encargado)
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EncargadoController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\InquilinoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PropiedadController;
use App\Http\Controllers\ReporteController;
// Controlador del panel del Inquilino
use App\Http\Controllers\TenantController;
// Controlador de autenticación (login/logout), común a todos los roles
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

// Ruta para procesar el envío del formulario de login (POST): valida cédula +
// contraseña vía LoginController::login y redirige según el rol mapeado.
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Agrupamos todas las rutas de aquí en adelante bajo el middleware 'auth':
// cualquier visitante sin sesión iniciada es redirigido al login.
Route::middleware(['auth'])->group(function () {

    // Cierra la sesión; accesible para cualquier usuario autenticado sin
    // importar su rol.
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // -------------------------------------------------------------
    // RUTAS DEL ADMINISTRADOR (Propietario y Encargado)
    // Protegidas por el middleware 'role:admin,encargado': ambos roles
    // comparten estas rutas y los controladores deciden qué vista renderizar
    // (Propietario/* o Encargado/*) según el rol mapeado del usuario.
    // -------------------------------------------------------------
    Route::middleware(['role:admin,encargado'])->prefix('admin')->group(function () {

        // Dashboard: resumen general para el propietario o encargado logueado.
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

        // Inquilinos: listado, alta, edición y baja de inquilinos.
        Route::get('/inquilinos', [InquilinoController::class, 'inquilinos'])->name('admin.inquilinos');
        Route::get('/nuevo-inquilino', [InquilinoController::class, 'nuevoInquilino'])->name('admin.nuevo-inquilino');
        Route::post('/nuevo-inquilino', [InquilinoController::class, 'storeInquilino'])->name('admin.store-inquilino');
        Route::get('/inquilino/{id}/editar', [InquilinoController::class, 'editInquilino'])->name('admin.edit-inquilino');
        Route::put('/inquilino/{id}', [InquilinoController::class, 'updateInquilino'])->name('admin.update-inquilino');
        Route::delete('/inquilino/{id}', [InquilinoController::class, 'destroyInquilino'])->name('admin.destroy-inquilino');

        // Pagos: listado, alta manual, edición, cambio rápido de estado y baja.
        Route::get('/pagos', [PagoController::class, 'pagos'])->name('admin.pagos');
        Route::get('/pagos/nuevo', [PagoController::class, 'nuevoPago'])->name('admin.nuevo-pago');
        Route::post('/pagos', [PagoController::class, 'storePago'])->name('admin.store-pago');
        Route::get('/pagos/{id}/editar', [PagoController::class, 'editPago'])->name('admin.edit-pago');
        Route::put('/pagos/{id}', [PagoController::class, 'updatePago'])->name('admin.update-pago');
        Route::patch('/pagos/{id}/estado', [PagoController::class, 'quickUpdateEstadoPago'])->name('admin.pagos.quick-estado');
        Route::delete('/pagos/{id}', [PagoController::class, 'destroyPago'])->name('admin.destroy-pago');

        // Notificaciones: bandeja de avisos (pagos notificados, reportes) y
        // acción rápida para cambiar el estado de un reporte desde ahí.
        Route::get('/notificaciones', [DashboardController::class, 'notificaciones'])->name('admin.notificaciones');
        Route::patch('/reportes/{id}/estado', [ReporteController::class, 'quickUpdateEstadoReporte'])->name('admin.reportes.quick-estado');

        // Gestión de Encargados, Propiedades y Habitaciones: exclusiva del
        // Propietario (dueño). Middleware anidado 'role:admin' porque, a
        // diferencia del resto de este grupo, el Encargado no puede entrar aquí.
        Route::middleware(['role:admin'])->group(function () {
            // Encargados: alta, edición y baja de cuentas de encargado.
            Route::get('/encargados', [EncargadoController::class, 'encargados'])->name('admin.encargados');
            Route::get('/nuevo-encargado', [EncargadoController::class, 'nuevoEncargado'])->name('admin.nuevo-encargado');
            Route::post('/nuevo-encargado', [EncargadoController::class, 'storeEncargado'])->name('admin.store-encargado');
            Route::get('/encargado/{id}/editar', [EncargadoController::class, 'editEncargado'])->name('admin.edit-encargado');
            Route::put('/encargado/{id}', [EncargadoController::class, 'updateEncargado'])->name('admin.update-encargado');
            Route::delete('/encargado/{id}', [EncargadoController::class, 'destroyEncargado'])->name('admin.destroy-encargado');

            // Propiedades: alta, edición y baja de propiedades del dueño.
            Route::get('/propiedades', [PropiedadController::class, 'propiedades'])->name('admin.propiedades');
            Route::get('/propiedades/nueva', [PropiedadController::class, 'nuevaPropiedad'])->name('admin.nueva-propiedad');
            Route::post('/propiedades', [PropiedadController::class, 'storePropiedad'])->name('admin.store-propiedad');
            Route::get('/propiedades/{id}/editar', [PropiedadController::class, 'editPropiedad'])->name('admin.edit-propiedad');
            Route::put('/propiedades/{id}', [PropiedadController::class, 'updatePropiedad'])->name('admin.update-propiedad');
            Route::delete('/propiedades/{id}', [PropiedadController::class, 'destroyPropiedad'])->name('admin.destroy-propiedad');

            // Habitaciones: alta, edición y baja de habitaciones dentro de una propiedad.
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
    // Protegidas por el middleware 'role:tenant'. Todas viven bajo el
    // prefijo /residente y las atiende TenantController, que renderiza
    // las vistas de resources/views/Inquilino/*.
    // -------------------------------------------------------------
    Route::middleware(['role:tenant'])->prefix('residente')->group(function () {

        // Dashboard del inquilino: resumen de su alquiler activo.
        Route::get('/dashboard', [TenantController::class, 'index'])->name('tenant.dashboard');

        // Residencias del inquilino (propiedad/habitación asignada).
        Route::get('/residencias', [TenantController::class, 'residencias'])->name('tenant.residencias');

        // Historial de pagos ya notificados/registrados.
        Route::get('/pagos', [TenantController::class, 'pagos'])->name('tenant.pagos');

        // Notificar un pago nuevo: formulario (GET) y envío (POST) con
        // monto, fecha y comprobante opcional.
        Route::get('/pagos/notificar', [TenantController::class, 'notificarPago'])->name('tenant.notificar-pago');
        Route::post('/pagos/notificar', [TenantController::class, 'storeNotificarPago'])->name('tenant.store-notificar-pago');

        // Reportes de mantenimiento: listado (GET) y creación (POST) de un
        // nuevo reporte de problema en la habitación/propiedad.
        Route::get('/reportes', [TenantController::class, 'reportes'])->name('tenant.reportes');
        Route::post('/reportes', [TenantController::class, 'storeReporte'])->name('tenant.store-reporte');
    });

});
