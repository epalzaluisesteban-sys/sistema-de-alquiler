@extends('layouts.panel')

@section('title', 'Papelera de Pagos')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-trash-restore"></i> Papelera de Pagos</h2>
        <a href="{{ route('admin.pagos') }}" class="btn-glossy" style="width: auto; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver a Pagos</a>
    </div>

    <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                    <th>Inquilino</th>
                    <th>Residencia</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Confirmado por</th>
                    <th>Eliminado</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <td>{{ $pago->asignacion?->usuario?->name ?? '—' }}</td>
                    <td>
                        @if($pago->asignacion?->habitacion)
                            Hab. {{ $pago->asignacion->habitacion->numero }} — {{ $pago->asignacion->habitacion->propiedad?->nombre ?? 'Sin propiedad' }}
                        @elseif($pago->asignacion?->propiedad)
                            {{ $pago->asignacion->propiedad->nombre }} (Casa)
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $pago->monto_formateado }}</td>
                    <td>{{ $pago->fecha?->format('d/m/Y') }}</td>
                    <td>{{ ucfirst($pago->estado_pago) }}</td>
                    <td>
                        @if($pago->confirmadoPor)
                            {{ $pago->confirmadoPor->name }}<br>
                            <small style="opacity: 0.7;">{{ $pago->confirmado_en?->format('d/m/Y H:i') }}</small>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $pago->deleted_at?->format('d/m/Y H:i') }}</td>
                    <td style="text-align: center; display: flex; justify-content: center; gap: 15px;">
                        <form action="{{ route('admin.restore-pago', $pago->id) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" title="Restaurar" style="background: none; border: none; color: var(--accent-light); cursor: pointer;">
                                <i class="fas fa-undo"></i>
                            </button>
                        </form>
                        <form action="{{ route('admin.force-destroy-pago', $pago->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este pago definitivamente? Esta acción no se puede deshacer.');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="Eliminar definitivamente" style="background: none; border: none; color: #ff6b6b; cursor: pointer;">
                                <i class="fas fa-times-circle"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if($pagos->isEmpty())
            <p style="text-align: center; opacity: 0.8; margin-top: 20px;">La papelera está vacía.</p>
        @endif
    </div>

    @if($pagos->hasPages())
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 10px;">
            <p style="margin: 0; opacity: 0.8; font-size: 0.9rem;">
                Mostrando {{ $pagos->firstItem() }}–{{ $pagos->lastItem() }} de {{ $pagos->total() }} pagos
            </p>
            <div style="display: flex; gap: 6px;">
                @if(! $pagos->onFirstPage())
                    <a href="{{ $pagos->previousPageUrl() }}" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.1); color: #D1D5DB; text-decoration: none;"><i class="fas fa-chevron-left"></i></a>
                @endif
                @if($pagos->hasMorePages())
                    <a href="{{ $pagos->nextPageUrl() }}" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.1); color: #D1D5DB; text-decoration: none;"><i class="fas fa-chevron-right"></i></a>
                @endif
            </div>
        </div>
    @endif
@endsection
