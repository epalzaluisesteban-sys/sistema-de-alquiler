@extends('layouts.panel')

@section('title', 'Editar Pago')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-edit"></i> Editar Pago</h2>
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

        <form action="{{ route('admin.update-pago', $pago->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label><i class="fas fa-user"></i> Inquilino / Residencia</label>
                <select name="asignacion_id" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);" required>
                    @foreach($asignaciones as $asignacion)
                        <option value="{{ $asignacion->id }}" @selected((int) old('asignacion_id', $pago->asignacion_id) === $asignacion->id)>
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
                    <input type="number" step="0.01" min="0" name="monto" value="{{ old('monto', $pago->monto) }}" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-calendar"></i> Fecha</label>
                    <input type="date" name="fecha" value="{{ old('fecha', $pago->fecha?->toDateString()) }}" max="{{ now()->toDateString() }}" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-layer-group"></i> Estado del Pago</label>
                    <select name="estado_pago" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                        <option value="pendiente" @selected(old('estado_pago', $pago->estado_pago) === 'pendiente')>Pendiente</option>
                        <option value="notificado" @selected(old('estado_pago', $pago->estado_pago) === 'notificado')>Notificado por el inquilino</option>
                        <option value="confirmado" @selected(old('estado_pago', $pago->estado_pago) === 'confirmado')>Confirmado</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-money-bill-wave"></i> Tipo de Pago</label>
                    <select name="tipo_pago" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);" required>
                        <option value="">-- Seleccione --</option>
                        <option value="divisas" @selected(old('tipo_pago', $pago->tipo_pago) === 'divisas')>Divisas</option>
                        <option value="pago_movil" @selected(old('tipo_pago', $pago->tipo_pago) === 'pago_movil')>Pago Móvil</option>
                    </select>
                </div>
            </div>

            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn-glossy">Guardar Cambios</button>
            </div>
        </form>
    </div>
@endsection
