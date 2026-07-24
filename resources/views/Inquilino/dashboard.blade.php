<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Residencia</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="tenant-container glass-panel">
        @if(session('success'))
            <div id="alerta-mensaje" class="glass-panel" style="padding: 15px; margin-bottom: 20px; background: rgba(22, 163, 74, 0.3); border-color: #22c55e; transition: opacity 0.6s ease;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
            <h2><i class="fas fa-user-circle"></i> Hola, {{ $usuario->name ?? 'Inquilino' }}</h2>
            <a href="javascript:void(0)" style="color: #ff6b6b; text-decoration: none; font-weight: 600;" onclick="document.getElementById('logout-form').submit();">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                @csrf
            </form>
        </div>
        
        <p>{{ optional(auth()->user())->habitacion ?? 'Habitación 04' }} | {{ optional(auth()->user())->residencia ?? 'Casa Principal' }}</p>
        <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">

       <!-- <h3>Estado de Cuenta: <span style="color: #4ade80;">Al Día</span></h3>-->

        <div class="action-cards">
            <div class="action-card glass-panel">
                <i class="fas fa-history fa-3x" style="margin-bottom: 15px;"></i>
                <h4>Historial de Pagos</h4>
                <!--<p style="font-size: 0.8rem;">Revisa todos tus meses cancelados.</p>-->
                <button type="button" class="btn-glossy" style="padding: 8px;">Ver Historial</button>
            </div>

            <div class="action-card glass-panel">
                <i class="fas fa-upload fa-3x" style="margin-bottom: 15px;"></i>
                <h4>Notificar Pago</h4>
                <!-- <p style="font-size: 0.8rem;">Sube tu referencia o captura de transferencia.</p> -->
                <button type="button" class="btn-glossy" style="padding: 8px;">Subir Recibo</button>
            </div>

            <div class="action-card glass-panel">
                <i class="fas fa-tools fa-3x" style="margin-bottom: 15px;"></i>
                <h4>Reportar Problema</h4>
               <!-- <p style="font-size: 0.8rem;">Informa sobre daños en la residencia.</p> -->
                <button type="button" class="btn-glossy" style="padding: 8px;">Crear Reporte</button>
            </div>
        </div>
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
</body>
</html>