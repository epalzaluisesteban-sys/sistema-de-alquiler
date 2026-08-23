@extends('layouts.tenant')

@section('title', 'Mi Alquiler')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
        <h2><i class="fas fa-home"></i> Mi Alquiler</h2>
        <a href="{{ route('tenant.dashboard') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    @if($asignacion)
        <div class="glass-panel" style="padding: 20px;">
            @if($asignacion->habitacion)
                <p><strong>Tipo:</strong> Habitación</p>
                <p><strong>Habitación:</strong> {{ $asignacion->habitacion->numero }}</p>
                <p><strong>Propiedad:</strong> {{ $asignacion->habitacion->propiedad?->nombre ?? '—' }}</p>
            @elseif($asignacion->propiedad)
                <p><strong>Tipo:</strong> Casa completa</p>
                <p><strong>Propiedad:</strong> {{ $asignacion->propiedad->nombre }}</p>
            @endif
            @if($asignacion->precio)
                <p><strong>Precio:</strong> ${{ number_format($asignacion->precio, 2) }}</p>
            @endif
            <p><strong>Fecha de inicio:</strong> {{ $asignacion->fecha_inicio?->format('d/m/Y') }}</p>
            @if($asignacion->fecha_fin)
                <p><strong>Fecha de fin:</strong> {{ $asignacion->fecha_fin->format('d/m/Y') }}</p>
            @endif
            <p><strong>Estado:</strong> {{ ucfirst($asignacion->estado_asignacion) }}</p>
        </div>
    @else
        <p style="text-align: center; opacity: 0.8;">Aún no tienes un alquiler asignado.</p>
    @endif
@endsection
