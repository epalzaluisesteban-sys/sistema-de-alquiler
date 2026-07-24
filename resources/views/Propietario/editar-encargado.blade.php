<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Encargado</title>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-user-edit"></i> Editar Encargado</h2>
                <a href="{{ route('admin.encargados') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
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

                <form action="{{ route('admin.update-encargado', $encargado->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-id-card"></i> Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $encargado->nombre) }}" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-id-card"></i> Apellido</label>
                            <input type="text" name="apellido" value="{{ old('apellido', $encargado->apellido) }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-address-card"></i> Cédula</label>
                        <input type="text" name="cedula" value="{{ old('cedula', $encargado->cedula) }}" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $encargado->telefono) }}" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Nueva Contraseña <span style="font-weight: normal; opacity: 0.7;">(dejar vacío para no cambiarla)</span></label>
                        <input type="password" name="contrasena">
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
