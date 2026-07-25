<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones</title>
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
            <h2><i class="fas fa-bell"></i> Centro de Notificaciones</h2>

            @if($reportes->isEmpty() && $avisosPago->isEmpty())
                <div class="glass-panel" style="padding: 20px; margin-top: 20px;">
                    <div style="background: rgba(22, 163, 74, 0.2); padding: 15px; border-radius: 10px; border-left: 5px solid #22c55e;">
                        <p><strong>Aviso:</strong> No hay alertas nuevas de inquilinos.</p>
                    </div>
                </div>
            @endif

            @if($reportes->isNotEmpty())
                <h3 style="margin-top: 25px;"><i class="fas fa-tools"></i> Reportes de Avería</h3>
                <div class="glass-panel" style="padding: 20px; margin-top: 10px; display: flex; flex-direction: column; gap: 12px;">
                    @foreach($reportes as $reporte)
                        <div style="background: rgba(239, 68, 68, 0.15); padding: 15px; border-radius: 10px; border-left: 5px solid #ef4444;">
                            <p style="margin: 0 0 5px 0;">
                                <strong>{{ $reporte->asignacion?->usuario?->name ?? 'Inquilino' }}</strong>
                                —
                                @if($reporte->asignacion?->habitacion)
                                    Hab. {{ $reporte->asignacion->habitacion->numero }} ({{ $reporte->asignacion->habitacion->propiedad?->nombre }})
                                @elseif($reporte->asignacion?->propiedad)
                                    {{ $reporte->asignacion->propiedad->nombre }}
                                @endif
                                <span style="float: right; opacity: 0.8; font-size: 0.85rem;">{{ $reporte->fecha?->format('d/m/Y') }}</span>
                            </p>
                            <p style="margin: 0 0 5px 0;">{{ $reporte->descripcion }}</p>
                            @php
                                $coloresReporte = ['pendiente' => '#ef4444', 'en_revision' => '#f59e0b', 'resuelto' => '#22c55e'];
                            @endphp
                            <form action="{{ route('admin.reportes.quick-estado', $reporte->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="estado_reporte" onchange="this.form.submit()" title="Cambiar estado" style="width: auto; background: {{ $coloresReporte[$reporte->estado_reporte] ?? '#6b7280' }}; color: white; border: none; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem; cursor: pointer;">
                                    <option value="pendiente" @selected($reporte->estado_reporte === 'pendiente')>Pendiente</option>
                                    <option value="en_revision" @selected($reporte->estado_reporte === 'en_revision')>En revisión</option>
                                    <option value="resuelto" @selected($reporte->estado_reporte === 'resuelto')>Resuelto</option>
                                </select>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif

            @if($avisosPago->isNotEmpty())
                <h3 style="margin-top: 25px;"><i class="fas fa-money-bill-wave"></i> Avisos de Pago</h3>
                <div class="glass-panel" style="padding: 20px; margin-top: 10px; display: flex; flex-direction: column; gap: 12px;">
                    @foreach($avisosPago as $pago)
                        <div style="background: rgba(59, 130, 246, 0.15); padding: 15px; border-radius: 10px; border-left: 5px solid #3b82f6;">
                            <p style="margin: 0 0 5px 0;">
                                <strong>{{ $pago->asignacion?->usuario?->name ?? 'Inquilino' }}</strong>
                                —
                                @if($pago->asignacion?->habitacion)
                                    Hab. {{ $pago->asignacion->habitacion->numero }} ({{ $pago->asignacion->habitacion->propiedad?->nombre }})
                                @elseif($pago->asignacion?->propiedad)
                                    {{ $pago->asignacion->propiedad->nombre }}
                                @endif
                                <span style="float: right; opacity: 0.8; font-size: 0.85rem;">{{ $pago->fecha?->format('d/m/Y') }}</span>
                            </p>
                            <p style="margin: 0 0 5px 0;">Monto: ${{ number_format($pago->monto, 2) }}</p>
                            <span style="background: {{ $pago->estado_pago === 'notificado' ? '#f59e0b' : '#ef4444' }}; padding: 3px 8px; border-radius: 5px; font-size: 0.8rem;">
                                {{ $pago->estado_pago === 'notificado' ? 'Notificado por el inquilino' : 'Pendiente' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </main>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }
    </script>
</body>
</html>
