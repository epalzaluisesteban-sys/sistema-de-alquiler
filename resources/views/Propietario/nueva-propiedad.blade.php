<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Propiedad</title>
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
                <h2><i class="fas fa-plus-circle"></i> Registrar Nueva Propiedad</h2>
                <a href="{{ route('admin.propiedades') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
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

                <form action="{{ route('admin.store-propiedad') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label><i class="fas fa-signature"></i> Nombre de la Propiedad</label>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej. Casa Norte" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-layer-group"></i> Estado</label>
                            <select name="estado" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                                <option value="disponible" @selected(old('estado') === 'disponible')>Disponible</option>
                                <option value="ocupada" @selected(old('estado') === 'ocupada')>Ocupada</option>
                                <option value="mantenimiento" @selected(old('estado') === 'mantenimiento')>Mantenimiento</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Descripción</label>
                        <textarea name="descripcion" rows="3" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);" placeholder="Detalles adicionales...">{{ old('descripcion') }}</textarea>
                    </div>

                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-glossy">Guardar Propiedad</button>
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
