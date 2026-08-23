<!DOCTYPE html>
@php
    $tema_fuente_clave = auth()->user()->tema_fuente ?? 'segoe';
    $tema_fondo = \App\Support\Personalizacion::fondo(auth()->user()->tema_fondo ?? null);
    $tema_color = \App\Support\Personalizacion::color(auth()->user()->tema_color ?? null);
    $tema_fuente = \App\Support\Personalizacion::fuente($tema_fuente_clave);
    $tema_boton = \App\Support\Personalizacion::boton(auth()->user()->tema_boton ?? null);
@endphp
<html lang="es" style="--accent: {{ $tema_color['base'] }}; --accent-dark: {{ $tema_color['dark'] }}; --accent-hover: {{ $tema_color['hover'] }}; --accent-hover-dark: {{ $tema_color['hoverDark'] }}; --accent-rgb: {{ $tema_color['rgb'] }}; --glass-rgb: {{ $tema_color['glassRgb'] }}; --accent-light: {{ $tema_color['light'] }}; --font-family: {{ $tema_fuente['css'] }}; --bg: {{ $tema_fondo['css'] }};">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sección Principal')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @if($tema_fuente_clave !== 'segoe')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Merriweather:wght@400;700&family=Nunito:wght@400;600;700&family=Fira+Code:wght@400;600&family=Roboto:wght@400;600;700&family=Lato:wght@400;600;700&family=Oswald:wght@400;600;700&family=Playfair+Display:wght@400;700&family=Montserrat:wght@400;600;700&family=Comic+Neue:wght@400;700&display=swap" rel="stylesheet">
    @endif
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
    @stack('head')
</head>
<body data-btn-style="{{ $tema_boton }}">
    <div class="hamburger" onclick="toggleSidebar()">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div class="dashboard-layout">
        @include('partials.panel-propietario')

        <main class="main-content glass-panel">
            @if(session('success'))
                <div id="alerta-mensaje" class="glass-panel alerta-flotante" style="padding: 20px; background: rgba(var(--accent-rgb), 0.3); border-color: var(--accent-hover); transition: opacity 0.6s ease;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        const alertaMensaje = document.getElementById('alerta-mensaje');
        if (alertaMensaje) {
            setTimeout(() => {
                alertaMensaje.style.opacity = '0';
                setTimeout(() => alertaMensaje.remove(), 600);
            }, 3000);
        }
    </script>
    @stack('scripts')
</body>
</html>
