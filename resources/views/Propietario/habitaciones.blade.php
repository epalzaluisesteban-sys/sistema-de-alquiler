@extends('layouts.panel')

@section('title', 'Gestión de Habitaciones')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-door-open"></i> Listado de Habitaciones</h2>
    </div>

    <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                    <th style="padding: 12px;">Propiedad</th>
                    <th style="padding: 12px;">Número</th>
                    <th style="padding: 12px;">Precio</th>
                    <th style="padding: 12px;">Estado</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $coloresEstado = ['disponible' => 'var(--accent-hover)', 'ocupada' => '#ef4444', 'mantenimiento' => '#f59e0b'];
                @endphp
                @foreach($habitaciones as $habitacion)
                    @php
                        $asignacionActiva = $habitacion->asignaciones->first();
                        $destino = $habitacion->estado === 'disponible'
                            ? route('admin.nuevo-inquilino', ['habitacion_id' => $habitacion->id])
                            : ($habitacion->estado === 'ocupada' && $asignacionActiva
                                ? route('admin.historial-inquilino', $asignacionActiva->usuario_id)
                                : null);
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td style="padding: 12px;">
                            @if($destino)
                                <a href="{{ $destino }}" style="color: inherit; text-decoration: none;">{{ $habitacion->propiedad?->nombre ?? '—' }}</a>
                            @else
                                {{ $habitacion->propiedad?->nombre ?? '—' }}
                            @endif
                        </td>
                        <td style="padding: 12px;">
                            @if($destino)
                                <a href="{{ $destino }}" style="color: inherit; text-decoration: none;">Habitación {{ $habitacion->numero }}</a>
                            @else
                                Habitación {{ $habitacion->numero }}
                            @endif
                        </td>
                        <td style="padding: 12px;">${{ number_format($habitacion->precio_actual ?? 0, 2) }}</td>
                        <td style="padding: 12px;">
                            <form action="{{ route('admin.update-estado-habitacion', $habitacion->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <select name="estado" onchange="this.form.submit()" title="Cambiar estado" style="width: auto; background: {{ $coloresEstado[$habitacion->estado] ?? '#6b7280' }}; color: white; border: none; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem; cursor: pointer;">
                                    <option value="disponible" @selected($habitacion->estado === 'disponible')>Disponible</option>
                                    <option value="ocupada" @selected($habitacion->estado === 'ocupada')>Ocupada</option>
                                    <option value="mantenimiento" @selected($habitacion->estado === 'mantenimiento')>Mantenimiento</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($habitaciones->isEmpty())
            <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No hay habitaciones registradas actualmente.</p>
        @endif
    </div>
@endsection
