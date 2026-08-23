@extends('layouts.panel')

@section('title', 'Sección Principal')

@section('content')
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
                            $colores = ['pendiente' => '#ef4444', 'notificado' => '#f59e0b', 'confirmado' => 'var(--accent-hover)'];
                        @endphp
                        <span style="background: {{ $colores[$pago->estado_pago] ?? '#6b7280' }}; color: white; font-weight: 600; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem;">
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
@endsection
