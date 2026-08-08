<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Componentes reutilizables de UI (ej. <x-tooltip>) viven en
        // resources/views/Screens/Components/<Nombre>/index.blade.php,
        // reflejando la carpeta screens/components/ del frontend en React.
        Blade::anonymousComponentPath(resource_path('views/Screens/Components'));
    }
}
