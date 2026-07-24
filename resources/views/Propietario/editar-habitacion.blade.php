<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Habitación</title>
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
                <h2><i class="fas fa-edit"></i> Editar Habitación</h2>
                <a href="{{ route('admin.habitaciones') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
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

                <form action="{{ route('admin.update-habitacion', $habitacion->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Propiedad</label>
                        <select name="propiedad_id" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);" required>
                            @foreach($propiedades as $propiedad)
                                <option value="{{ $propiedad->id }}" @selected((int) old('propiedad_id', $habitacion->propiedad_id) === $propiedad->id)>{{ $propiedad->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-hashtag"></i> Número de Habitación</label>
                            <input type="text" name="numero" value="{{ old('numero', $habitacion->numero) }}" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-dollar-sign"></i> Precio Mensual</label>
                            <input type="number" step="0.01" min="0" name="precio" value="{{ old('precio', $habitacion->precio) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-layer-group"></i> Estado</label>
                        <select name="estado" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                            <option value="disponible" @selected(old('estado', $habitacion->estado) === 'disponible')>Disponible</option>
                            <option value="ocupada" @selected(old('estado', $habitacion->estado) === 'ocupada')>Ocupada</option>
                            <option value="mantenimiento" @selected(old('estado', $habitacion->estado) === 'mantenimiento')>Mantenimiento</option>
                        </select>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-glossy">Guardar Cambios</button>
                    </div>
                </form>
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
