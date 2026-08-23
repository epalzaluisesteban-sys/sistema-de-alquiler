@extends('layouts.panel')

@section('title', 'Gestión de Encargados')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-user-shield"></i> Listado de Encargados</h2>
        <a href="{{ route('admin.nuevo-encargado') }}" class="btn-glossy" style="width: auto; text-decoration: none;">+ Nuevo Encargado</a>
    </div>

    <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                    <th style="padding: 12px;">Nombre</th>
                    <th style="padding: 12px;">Cédula</th>
                    <th style="padding: 12px;">Teléfono</th>
                    <th style="padding: 12px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($encargados as $encargado)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <td style="padding: 12px;">{{ $encargado->name }}</td>
                    <td style="padding: 12px;">{{ $encargado->cedula ?? '—' }}</td>
                    <td style="padding: 12px;">{{ $encargado->telefono ?? '—' }}</td>
                    <td style="padding: 12px; text-align: center; display: flex; justify-content: center; gap: 15px;">
                        <a href="{{ route('admin.edit-encargado', $encargado->id) }}" style="color: var(--accent-light); text-decoration: none;" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.destroy-encargado', $encargado->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este encargado?');" style="display: inline;">
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
        @if($encargados->isEmpty())
            <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No hay encargados registrados actualmente.</p>
        @endif
    </div>
@endsection
