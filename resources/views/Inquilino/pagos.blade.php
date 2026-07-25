<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="tenant-container glass-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h2><i class="fas fa-history"></i> Historial de Pagos</h2>
            <a href="{{ route('tenant.dashboard') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                        <th style="padding: 12px;">Alquiler</th>
                        <th style="padding: 12px;">Monto</th>
                        <th style="padding: 12px;">Fecha</th>
                        <th style="padding: 12px;">Estado</th>
                        <th style="padding: 12px;">Comprobante</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pagos as $pago)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td style="padding: 12px;">
                            @if($pago->asignacion?->habitacion)
                                Hab. {{ $pago->asignacion->habitacion->numero }} — {{ $pago->asignacion->habitacion->propiedad?->nombre ?? 'Sin propiedad' }}
                            @elseif($pago->asignacion?->propiedad)
                                {{ $pago->asignacion->propiedad->nombre }} (Casa)
                            @else
                                —
                            @endif
                        </td>
                        <td style="padding: 12px;">${{ number_format($pago->monto, 2) }}</td>
                        <td style="padding: 12px;">{{ $pago->fecha?->format('d/m/Y') }}</td>
                        <td style="padding: 12px;">
                            @php
                                $colores = ['pendiente' => '#ef4444', 'notificado' => '#f59e0b', 'confirmado' => '#22c55e'];
                                $etiquetas = ['pendiente' => 'Pendiente', 'notificado' => 'Notificado', 'confirmado' => 'Confirmado'];
                            @endphp
                            <span style="background: {{ $colores[$pago->estado_pago] ?? '#6b7280' }}; color: white; padding: 4px 10px; border-radius: 5px; font-size: 0.8rem;">
                                {{ $etiquetas[$pago->estado_pago] ?? ucfirst($pago->estado_pago) }}
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            @if($pago->ruta_comprobante)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($pago->ruta_comprobante) }}" target="_blank" style="color: #86EFAC;"><i class="fas fa-file-alt"></i> Ver</a>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($pagos->isEmpty())
                <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No tienes pagos registrados todavía.</p>
            @endif
        </div>

        @if($pagos->hasPages())
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; flex-wrap: wrap; gap: 10px;">
                <p style="margin: 0; opacity: 0.8; font-size: 0.9rem;">
                    Mostrando {{ $pagos->firstItem() }}–{{ $pagos->lastItem() }} de {{ $pagos->total() }} pagos
                </p>
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    @if($pagos->onFirstPage())
                        <span style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.05); opacity: 0.4;"><i class="fas fa-chevron-left"></i></span>
                    @else
                        <a href="{{ $pagos->previousPageUrl() }}" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.1); color: #D1D5DB; text-decoration: none;"><i class="fas fa-chevron-left"></i></a>
                    @endif

                    @for($page = 1; $page <= $pagos->lastPage(); $page++)
                        @if($page === $pagos->currentPage())
                            <span style="padding: 8px 12px; border-radius: 6px; background: #14532d; color: white;">{{ $page }}</span>
                        @else
                            <a href="{{ $pagos->url($page) }}" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.1); color: #D1D5DB; text-decoration: none;">{{ $page }}</a>
                        @endif
                    @endfor

                    @if($pagos->hasMorePages())
                        <a href="{{ $pagos->nextPageUrl() }}" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.1); color: #D1D5DB; text-decoration: none;"><i class="fas fa-chevron-right"></i></a>
                    @else
                        <span style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.05); opacity: 0.4;"><i class="fas fa-chevron-right"></i></span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</body>
</html>
