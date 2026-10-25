@extends('layouts.panel')

@php($esEncargado = $inquilino->rol === 'encargado')
@php($tieneAsignacionActiva = $asignaciones->contains('estado_asignacion', 'activo'))

@section('title', 'Historial de ' . ($esEncargado ? 'Encargado' : 'Inquilino'))

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-clock-rotate-left"></i> Historial de {{ $inquilino->name }}</h2>
        <a href="{{ route($esEncargado ? 'admin.encargados' : 'admin.inquilinos') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="glass-panel" style="padding: 20px; margin-bottom: 20px;">
        <p><i class="fas fa-id-card"></i> Cédula: {{ $inquilino->cedula ?? '—' }}</p>
        <p><i class="fas fa-phone"></i> Teléfono: {{ $inquilino->telefono ?? '—' }}</p>
        <p>
            <i class="fas fa-circle" style="font-size: 10px; color: {{ $tieneAsignacionActiva ? '#4ADE80' : '#ff6b6b' }};"></i>
            {{ $tieneAsignacionActiva ? 'Actualmente alquilado' : 'Ya no está alquilado' }}
        </p>
    </div>

    @forelse($asignaciones as $asignacion)
        <div class="glass-panel" style="padding: 20px; margin-bottom: 20px;">
            <h3 style="margin-top: 0;">
                @if($asignacion->habitacion)
                    Hab. {{ $asignacion->habitacion->numero }} — {{ $asignacion->habitacion->propiedad?->nombre ?? 'Sin propiedad' }}
                @elseif($asignacion->propiedad)
                    {{ $asignacion->propiedad->nombre }} (Casa completa)
                @else
                    Residencia sin datos
                @endif
            </h3>
            <p style="opacity: 0.85;">
                {{ $asignacion->fecha_inicio?->format('d/m/Y') }}
                —
                {{ $asignacion->fecha_fin?->format('d/m/Y') ?? 'Activa' }}
                <span style="margin-left: 10px; text-transform: capitalize;">({{ $asignacion->estado_asignacion }})</span>
            </p>

            @if($asignacion->pagos->isEmpty())
                <p style="opacity: 0.7; margin-top: 10px;">Sin pagos registrados para esta residencia.</p>
            @else
                <table style="width: 100%; border-collapse: collapse; color: #D1D5DB; margin-top: 10px;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                            <th>Fecha</th>
                            <th>Monto</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($asignacion->pagos as $pago)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                                <td>{{ $pago->fecha?->format('d/m/Y') }}</td>
                                <td>${{ number_format($pago->monto, 2) }}</td>
                                <td style="text-transform: capitalize;">{{ $pago->estado_pago }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <div class="glass-panel" style="padding: 20px;">
            <p style="text-align: center; opacity: 0.8;">Este inquilino no tiene residencias registradas todavía.</p>
        </div>
    @endforelse
@endsection
