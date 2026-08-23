<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mi Alquiler')</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <a href="{{ route('tenant.ajustes') }}" class="settings-gear glass-panel" title="Ajustes">
        <i class="fas fa-gear"></i>
    </a>
    <div class="tenant-container glass-panel">
        @if(session('success'))
            <div id="alerta-mensaje" class="glass-panel alerta-flotante" style="padding: 20px; background: rgba(22, 163, 74, 0.3); border-color: #22c55e; transition: opacity 0.6s ease;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div id="alerta-error" class="glass-panel alerta-flotante" style="padding: 20px; background: rgba(239, 68, 68, 0.3); border-color: #ef4444; transition: opacity 0.6s ease;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </div>
    <script>
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
