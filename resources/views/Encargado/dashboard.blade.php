<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sección Principal</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="hamburger" onclick="toggleSidebar()">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div class="dashboard-layout">
        @include('partials.panel-propietario')

        <main class="main-content glass-panel">
            @if(session('success'))
                <div id="alerta-mensaje" class="glass-panel" style="padding: 15px; margin-bottom: 20px; background: rgba(22, 163, 74, 0.3); border-color: #22c55e; transition: opacity 0.6s ease;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <h2>Resumen General</h2>
            <div class="stats-grid">
                <div class="stat-box glass-panel">
                    <h3>{{ $inquilinosActivos }}</h3>
                    <p>Inquilinos Activos</p>
                </div>
                <div class="stat-box glass-panel">
                    <h3>{{ $pagosPendientes }}</h3>
                    <p>Pagos Pendientes</p>
                </div>
                <div class="stat-box glass-panel">
                    <h3>${{ number_format($ingresosMes, 2) }}</h3>
                    <p>Ingresos del Mes</p>
                </div>
            </div>

            <h3>Últimos Movimientos</h3>
            <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                            <th style="padding: 12px;">Inquilino</th>
                            <th style="padding: 12px;">Residencia</th>
                            <th style="padding: 12px;">Monto</th>
                            <th style="padding: 12px;">Fecha</th>
                            <th style="padding: 12px;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ultimosMovimientos as $pago)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 12px;">{{ $pago->asignacion?->usuario?->name ?? '—' }}</td>
                            <td style="padding: 12px;">
                                @if($pago->asignacion?->habitacion)
                                    Hab. {{ $pago->asignacion->habitacion->numero }} — {{ $pago->asignacion->habitacion->propiedad?->nombre ?? 'Sin propiedad' }}
                                @elseif($pago->asignacion?->propiedad)
                                    {{ $pago->asignacion->propiedad->nombre }} (Casa)
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 12px;">${{ number_format($pago->monto, 2) }}</td>
                            <td style="padding: 12px;">{{ $pago->fecha?->format('d/m/Y') }}</td>
                            <td style="padding: 12px;">
                                @php
                                    $colores = ['pendiente' => '#ef4444', 'notificado' => '#f59e0b', 'confirmado' => '#22c55e'];
                                @endphp
                                <span style="background: {{ $colores[$pago->estado_pago] ?? '#6b7280' }}; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem;">
                                    {{ ucfirst($pago->estado_pago) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($ultimosMovimientos->isEmpty())
                    <p style="text-align: center; opacity: 0.8; margin-top: 20px;">Todavía no se han registrado pagos.</p>
                @endif
            </div>
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
</body>
</html>
