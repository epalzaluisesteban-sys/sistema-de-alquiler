<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inquilino</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    @php
        $tipoActual = $asignacionActiva?->habitacion_id ? 'Habitación' : ($asignacionActiva?->propiedad_id ? 'Casa' : '');
    @endphp
    <div class="hamburger" onclick="toggleSidebar()">
        <div></div>
        <div></div>
        <div></div>
    </div>
    <div class="dashboard-layout">
        @include('partials.panel-propietario')

        <main class="main-content glass-panel">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-user-edit"></i> Editar Inquilino</h2>
                <a href="{{ route('admin.inquilinos') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
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

                <form action="{{ route('admin.update-inquilino', $inquilino->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-row">
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-id-card"></i> Nombre</label>
                            <input type="text" name="nombre" value="{{ old('nombre', $inquilino->nombre) }}" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label><i class="fas fa-id-card"></i> Apellido</label>
                            <input type="text" name="apellido" value="{{ old('apellido', $inquilino->apellido) }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-id-card"></i> Cédula</label>
                        <input type="text" name="cedula" value="{{ old('cedula', $inquilino->cedula) }}" required>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-key"></i> Tipo de Alquiler</label>
                        <select id="rental-type" name="rental_type" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                            <option value="">-- Sin cambios / sin asignar --</option>
                            <option value="Casa" @selected(old('rental_type', $tipoActual) === 'Casa')>Propiedad completa (Casa)</option>
                            <option value="Habitación" @selected(old('rental_type', $tipoActual) === 'Habitación')>Habitación</option>
                        </select>
                    </div>

                    <div class="form-group" id="house-field" style="display: none;">
                        <label><i class="fas fa-building"></i> Propiedad</label>
                        <select id="house-select" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                            <option value="">Seleccione una propiedad</option>
                            @foreach($properties as $property)
                                <option value="{{ $property->id }}" @selected($asignacionActiva?->propiedad_id === $property->id)>{{ $property->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group" id="room-field" style="display: none;">
                        <label><i class="fas fa-door-open"></i> Habitación</label>
                        <select id="room-select" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                            <option value="">Seleccione una habitación</option>
                            @foreach($rooms as $room)
                                <option value="{{ $room->id }}" @selected($asignacionActiva?->habitacion_id === $room->id)>Habitación {{ $room->numero }} — {{ $room->propiedad?->nombre ?? 'Sin propiedad' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Teléfono de Contacto</label>
                        <input type="text" name="telefono" value="{{ old('telefono', $inquilino->telefono) }}">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-lock"></i> Nueva Contraseña <span style="font-weight: normal; opacity: 0.7;">(dejar vacío para no cambiarla)</span></label>
                        <input type="password" name="password">
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

        document.addEventListener('DOMContentLoaded', function () {
            const rental = document.getElementById('rental-type');
            const houseField = document.getElementById('house-field');
            const roomField = document.getElementById('room-field');
            const houseSelect = document.getElementById('house-select');
            const roomSelect = document.getElementById('room-select');

            function updateResidenciaField() {
                const esCasa = rental.value === 'Casa';
                const esHabitacion = rental.value === 'Habitación';

                houseField.style.display = esCasa ? '' : 'none';
                roomField.style.display = esHabitacion ? '' : 'none';

                houseSelect.name = esCasa ? 'property_id' : '';
                roomSelect.name = esHabitacion ? 'property_id' : '';
                if (!esCasa) houseSelect.removeAttribute('name');
                if (!esHabitacion) roomSelect.removeAttribute('name');
            }

            rental.addEventListener('change', updateResidenciaField);
            updateResidenciaField();
        });
    </script>
</body>
</html>
