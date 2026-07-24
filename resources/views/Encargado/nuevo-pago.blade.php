<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Pago</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="hamburger" onclick="toggleSidebar()">
        <div></div><div></div><div></div>
    </div>
    <div class="dashboard-layout">
        @include('partials.panel-propietario')

        <main class="main-content glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-plus-circle"></i> Registrar Pago</h2>
                <a href="{{ route('admin.pagos') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>

            <div class="glass-panel" style="padding: 30px; max-width: 600px; margin: 0 auto;">
                @if ($errors->any())
                    <div style="background: rgba(239, 68, 68, 0.3); padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                        <ul style="margin: 0; color: #ff9b9b;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($asignaciones->isEmpty())
                    <p style="text-align: center; opacity: 0.8;">No hay inquilinos con una residencia activa asignada todavía.</p>
                @else
                <form action="{{ route('admin.store-pago') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Inquilino / Residencia</label>
                        <select name="asignacion_id" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);" required>
                            <option value="">-- Seleccione --</option>
                            @foreach($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}" @selected((int) old('asignacion_id') === $asignacion->id)>
                                    {{ $asignacion->usuario?->name }} —
                                    @if($asignacion->habitacion)
                                        Hab. {{ $asignacion->habitacion->numero }} ({{ $asignacion->habitacion->propiedad?->nombre }})
                                    @elseif($asignacion->propiedad)
                                        {{ $asignacion->propiedad->nombre }} (Casa)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-dollar-sign"></i> Monto</label>
                            <input type="number" step="0.01" min="0" name="monto" value="{{ old('monto') }}" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-calendar"></i> Fecha</label>
                            <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i> Estado del Pago</label>
                        <select name="estado_pago" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                            <option value="pendiente" @selected(old('estado_pago') === 'pendiente')>Pendiente</option>
                            <option value="notificado" @selected(old('estado_pago') === 'notificado')>Notificado por el inquilino</option>
                            <option value="confirmado" @selected(old('estado_pago') === 'confirmado')>Confirmado</option>
                        </select>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-glossy">Guardar Pago</button>
                    </div>
                </form>
                @endif
            </div>
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
