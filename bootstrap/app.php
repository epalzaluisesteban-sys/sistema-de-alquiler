<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // AQUÍ ES DONDE REGISTRAS EL ALIAS
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);

        // Confía en el proxy de ngrok (y similares) para que Laravel detecte
        // el esquema real (https) a partir del header X-Forwarded-Proto y no
        // genere URLs http:// para asset()/url() que el navegador bloquea
        // por contenido mixto cuando el sitio se sirve por https vía túnel.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();