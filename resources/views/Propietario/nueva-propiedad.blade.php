@extends('layouts.panel')

@section('title', 'Registrar Propiedad')

@section('content')
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
                    <label><i class="fas fa-house-user"></i> Tipo de Propiedad</label>
                    <select name="tipo" id="tipo-propiedad" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);" required>
                        <option value="completa" @selected(old('tipo', 'completa') === 'completa')>Casa completa</option>
                        <option value="habitaciones" @selected(old('tipo') === 'habitaciones')>Con habitaciones</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-layer-group"></i> Estado</label>
                    <select name="estado" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                        <option value="disponible" @selected(old('estado') === 'disponible')>Disponible</option>
                        <option value="ocupada" @selected(old('estado') === 'ocupada')>Ocupada</option>
                        <option value="mantenimiento" @selected(old('estado') === 'mantenimiento')>Mantenimiento</option>
                    </select>
                </div>
            </div>

            <div class="form-row" id="cantidad-habitaciones-field" style="display: none;">
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-door-open"></i> Cantidad de Habitaciones</label>
                    <input type="number" min="1" step="1" name="cantidad_habitaciones" id="cantidad-habitaciones" value="{{ old('cantidad_habitaciones') }}">
                    <small style="color: rgba(255,255,255,0.6);">Se crearán automáticamente esa cantidad de habitaciones, con el precio definido en Ajustes.</small>
                </div>
            </div>

            <div class="form-row" id="precio-acordado-field">
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-dollar-sign"></i> Precio Acordado</label>
                    <input type="number" step="0.01" min="0" name="precio_acordado" value="{{ old('precio_acordado') }}" placeholder="Ej. 300">
                    <p style="opacity: 0.8; font-size: 0.9rem; margin: 5px 0 0;"><i class="fas fa-circle-info"></i> Solo aplica si esta propiedad se alquila completa (sin dividirse en habitaciones).</p>
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
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo-propiedad');
        const cantidadField = document.getElementById('cantidad-habitaciones-field');
        const cantidadInput = document.getElementById('cantidad-habitaciones');
        const precioField = document.getElementById('precio-acordado-field');

        function toggleCamposPorTipo() {
            const esHabitaciones = tipoSelect.value === 'habitaciones';
            cantidadField.style.display = esHabitaciones ? '' : 'none';
            precioField.style.display = esHabitaciones ? 'none' : '';
            cantidadInput.required = esHabitaciones;
        }

        tipoSelect.addEventListener('change', toggleCamposPorTipo);
        toggleCamposPorTipo();
    });
</script>
@endpush
