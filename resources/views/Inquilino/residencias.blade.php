<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Alquiler</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="tenant-container glass-panel">
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
                    <p><strong>Precio:</strong> ${{ number_format($asignacion->habitacion->precio, 2) }}</p>
                @elseif($asignacion->propiedad)
                    <p><strong>Tipo:</strong> Casa completa</p>
                    <p><strong>Propiedad:</strong> {{ $asignacion->propiedad->nombre }}</p>
                @endif
                <p><strong>Fecha de inicio:</strong> {{ $asignacion->fecha_inicio?->format('d/m/Y') }}</p>
                <p><strong>Estado:</strong> {{ ucfirst($asignacion->estado_asignacion) }}</p>
            </div>
        @else
            <p style="text-align: center; opacity: 0.8;">Aún no tienes un alquiler asignado.</p>
        @endif
    </div>
</body>
</html>
