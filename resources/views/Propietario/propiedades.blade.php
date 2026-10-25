@extends('layouts.panel')

@section('title', 'Gestión de Propiedades')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-building"></i> Listado de Propiedades</h2>
        <a href="{{ route('admin.nueva-propiedad') }}" class="btn-glossy" style="width: auto; text-decoration: none;">+ Nueva Propiedad</a>
    </div>

    <h3 style="margin-bottom: 10px;"><i class="fas fa-door-open"></i> Casa de Alquiler</h3>
    <div class="glass-panel" style="padding: 20px; overflow-x: auto; margin-bottom: 30px;">
        <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Habitaciones</th>
                    <th>Ocupación</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($propiedadesConHabitaciones as $propiedad)
                    @php
                        $colorOcupacion = $propiedad->habitaciones_count === 0
                            ? '#6b7280'
                            : ($propiedad->habitaciones_ocupadas_count === 0
                                ? 'var(--accent-hover)'
                                : ($propiedad->habitaciones_ocupadas_count === $propiedad->habitaciones_count
                                    ? '#ef4444'
                                    : '#f59e0b'));
                    @endphp
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <td>
                        <a href="{{ route('admin.habitaciones', ['propiedad_id' => $propiedad->id]) }}" style="color: inherit; text-decoration: none;">{{ $propiedad->nombre }}</a>
                    </td>
                    <td>{{ $propiedad->descripcion ?? '—' }}</td>
                    <td>{{ $propiedad->habitaciones_count }}</td>
                    <td>
                        <a href="{{ route('admin.habitaciones', ['propiedad_id' => $propiedad->id]) }}" style="text-decoration: none;">
                            <span style="background: {{ $colorOcupacion }}; color: white; font-weight: 600; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem;">
                                {{ $propiedad->habitaciones_ocupadas_count }}/{{ $propiedad->habitaciones_count }} ocupadas
                            </span>
                        </a>
                    </td>
                    <td style="text-align: center; display: flex; justify-content: center; gap: 15px;">
                        <a href="{{ route('admin.edit-propiedad', $propiedad->id) }}" style="color: var(--accent-light); text-decoration: none;" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.destroy-propiedad', $propiedad->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta propiedad? También se eliminarán sus habitaciones, inquilinos asignados y pagos asociados.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ff6b6b; cursor: pointer;">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($propiedadesConHabitaciones->isEmpty())
            <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No hay propiedades con habitaciones registradas actualmente.</p>
        @endif
    </div>

    <h3 style="margin-bottom: 10px;"><i class="fas fa-key"></i> Casa Entera</h3>
    <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio Acordado</th>
                    <th>Estado</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($propiedadesCompletas as $propiedad)
                    @php
                        $asignacionActiva = $propiedad->asignaciones->first();
                        $destino = $propiedad->estado === 'disponible'
                            ? route('admin.nuevo-inquilino', ['propiedad_id' => $propiedad->id])
                            : ($propiedad->estado === 'ocupada' && $asignacionActiva
                                ? route('admin.historial-inquilino', $asignacionActiva->usuario_id)
                                : null);
                    @endphp
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <td>
                        @if($destino)
                            <a href="{{ $destino }}" style="color: inherit; text-decoration: none;">{{ $propiedad->nombre }}</a>
                        @else
                            {{ $propiedad->nombre }}
                        @endif
                    </td>
                    <td>{{ $propiedad->descripcion ?? '—' }}</td>
                    <td>{{ $propiedad->precio_acordado !== null ? '$' . number_format($propiedad->precio_acordado, 2) : '—' }}</td>
                    <td>
                        <span style="background: {{ $propiedad->estado === 'disponible' ? 'var(--accent-hover)' : ($propiedad->estado === 'ocupada' ? '#ef4444' : '#f59e0b') }}; color: white; font-weight: 600; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem;">
                            {{ ucfirst($propiedad->estado) }}
                        </span>
                    </td>
                    <td style="text-align: center; display: flex; justify-content: center; gap: 15px;">
                        <a href="{{ route('admin.edit-propiedad', $propiedad->id) }}" style="color: var(--accent-light); text-decoration: none;" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.destroy-propiedad', $propiedad->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta propiedad? También se eliminarán sus inquilinos asignados y pagos asociados.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #ff6b6b; cursor: pointer;">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($propiedadesCompletas->isEmpty())
            <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No hay propiedades de alquiler completo registradas actualmente.</p>
        @endif
    </div>
@endsection
