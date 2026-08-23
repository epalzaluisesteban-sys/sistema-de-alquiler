# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 12 app for residential property management ("Gestión Residencial") — tracks propiedades (properties), habitaciones (rooms), usuarios (owners/managers/tenants), asignaciones (tenancies), and pagos (payments). UI text, routes, and DB columns are in Spanish.

## Commands

Composer / PHP:
```
composer install
php artisan migrate              # applies against DB_* in .env (MySQL in local dev; see note below)
php artisan serve
composer dev                     # runs serve + queue:listen + pail (logs) + vite concurrently
```

Frontend:
```
npm install
npm run dev                      # vite dev server
npm run build
```

Tests (uses sqlite `:memory:`, not the dev MySQL DB — see `phpunit.xml`):
```
composer test                    # config:clear + php artisan test
php artisan test                             # all tests
php artisan test --filter=test_login_works_with_cedula   # single test
php artisan test tests/Feature/AuthCedulaTest.php         # single file
```

There is no linter/formatter configured beyond `laravel/pint` (dev dependency); run `vendor/bin/pint` if asked to format.

## Architecture

### Custom auth model — not the stock Laravel `User`

Auth does **not** use the default `users` table/model. The auth model is `App\Models\Usuario` (table `usuarios`), configured as the `users` provider's model in `config/auth.php`. Key non-standard bits on `Usuario` ([app/Models/Usuario.php](app/Models/Usuario.php)):

- Login identifier is `cedula` (national ID), not email. Login goes through `LoginController::login` calling `Auth::attempt(['cedula' => ..., 'password' => ...])`, which matches the credentials array directly against columns — it does **not** need `getAuthIdentifierName()` overridden. `Usuario` deliberately does **not** override `getAuthIdentifierName()`/`getAuthIdentifier()`: they were once overridden to return `'cedula'`, which made Laravel try to store the cedula string (e.g. `V-12345678`) in `sessions.user_id` (an integer FK column) and `Auth::id()`, breaking as soon as a cedula contained non-digit characters. Keep these on the default (`id`, the real PK).
- The real password column is `contrasena`; `getAuthPassword()` returns it. A `password` virtual attribute (mutator/accessor) exists for compatibility — **always assign via `'password' => ...'` when creating/updating a Usuario so the value gets hashed**; assigning `'contrasena'` directly bypasses hashing and stores plaintext.
- `name`/`nombre`+`apellido` similarly have virtual-attribute glue (`getNameAttribute`/`setNameAttribute` split/join `name` into `nombre`+`apellido`).
- The raw DB column is `rol` (enum: `propietario`, `encargado`, `inquilino`). The `role` virtual attribute maps DB values to app-facing role strings: `propietario → admin`, `encargado → encargado`, `inquilino → tenant`. Middleware, `LoginController`, and route redirects all check against the *mapped* values (`admin`/`encargado`/`tenant`), not the raw `rol` column.

### Roles and access control

Three roles: **Propietario** (owner, mapped role `admin`), **Encargado** (manager, mapped role `encargado`), **Inquilino** (tenant, mapped role `tenant`).

- `App\Http\Middleware\CheckRole` (alias `role`, registered in [bootstrap/app.php](bootstrap/app.php)) takes a variadic list of allowed roles, e.g. `role:admin,encargado`. It compares against the mapped `role` attribute, not `rol`.
- In [routes/web.php](routes/web.php), the `admin` route group (prefix `/admin`) is shared by Propietario and Encargado (`role:admin,encargado`); a nested inner group restricted to `role:admin` guards Encargado-management routes (`admin.nuevo-admin`, `admin.store-admin`, `admin.destroy-admin`) since only the owner may create/remove managers. The `residente`-prefixed group (`role:tenant`) is Inquilino-only.
- The `admin.*` route group is backed by domain-specific controllers — `DashboardController`, `InquilinoController`, `PropiedadController`, `HabitacionController`, `EncargadoController`, `PagoController` — shared by **both** Propietario and Encargado. Since Propietario and Encargado see identical markup for dashboard/inquilinos/pagos/notificaciones, those controllers render a single shared view under `resources/views/panel/*` for both roles (no per-role branching). The sidebar itself (`resources/views/partials/panel-propietario.blade.php`) still toggles a couple of owner-only links internally based on `auth()->user()->role`. Encargado-management and Propiedad/Habitacion CRUD views (owner-only, `role:admin`) live under `resources/views/Propietario/*`.
- `App\Http\Controllers\TenantController` backs the `tenant.*` routes and renders `resources/views/Inquilino/*`.
- `App\Http\Controllers\LoginController::login` authenticates via `Auth::attempt(['cedula' => ..., 'password' => ...])` and redirects based on the mapped role: `admin`/`encargado` → `admin.dashboard`, `tenant` → `tenant.dashboard`. Any other/missing role logs the user back out.

### Domain model relationships

`Usuario` (owner/manager/tenant, discriminated by `rol`) → `Propiedad` (1:N, owner's properties) → `Habitacion` (1:N, rooms in a property) → `Asignacion` (1:N, a tenancy linking a `Usuario` (tenant) to a `Habitacion`) → `Pago` (1:N payments) and `Reporte` (1:N maintenance reports), both scoped to an `Asignacion`.

Note: the `App\Models\Propiedad` class lives in `app/Models/Propiedad.php` — the filename must match the class name exactly (PSR-4); a prior mismatch (`Propiedades.php` containing `class Propiedad`) silently broke autoloading of that model.

### Known incomplete/dead routes

`routes/web.php` references `PropertyController`, `HouseController`, `RoomController`, and `PaymentController` for an `admin/propiedades` module (`Route::resource(...)`) — none of these controllers, nor any corresponding blade views, exist yet. No current view links to these routes, so they don't break normal navigation, but they do make `php artisan route:list` throw a `ReflectionException`. Building this module out means creating the controllers, `House`/`Room`/`Payment`-equivalent models or reusing `Habitacion`/`Pago`, and views under `resources/views`.

### Views

Two shared layouts hold the HTML shell (doctype/head/scripts) so it isn't repeated per page:

- `resources/views/layouts/panel.blade.php` — used by the admin/encargado panel. Includes the sidebar partial (`partials/panel-propietario`) and the success-flash alert; pages `@extend` it and fill `@section('content')` (and optionally `@section('title')` / `@push('scripts')`).
- `resources/views/layouts/tenant.blade.php` — used by the Inquilino panel. Includes the success/error flash alerts; pages `@extend` it the same way.

View locations:
- `resources/views/panel/*.blade.php` — dashboard/inquilinos/pagos/notificaciones/editar-inquilino/editar-pago/historial-inquilino/nuevo-inquilino/nuevo-pago, rendered identically for Propietario and Encargado.
- `resources/views/Propietario/*.blade.php` — owner-only CRUD for propiedades, habitaciones, and encargados (routes gated `role:admin`).
- `resources/views/Inquilino/*.blade.php` — tenant views, on the `tenant` layout.
- `resources/views/auth/login.blade.php` — standalone, not on either layout.

When adding a new admin-panel page shared by Propietario and Encargado, put it under `resources/views/panel/` and extend `layouts.panel`; don't reintroduce per-role view pairs.


# Idioma
- Responde siempre en español.
- Explica los cambios de código, errores del terminal y sugerencias estrictamente en español.
