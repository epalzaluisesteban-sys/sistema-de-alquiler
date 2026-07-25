<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control de Pagos</title>
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
            @if(session('success'))
                <div id="alerta-mensaje" class="glass-panel" style="padding: 15px; margin-bottom: 20px; background: rgba(22, 163, 74, 0.3); border-color: #22c55e; transition: opacity 0.6s ease;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2><i class="fas fa-file-invoice-dollar"></i> Control de Pagos Mensuales</h2>
                <a href="{{ route('admin.nuevo-pago') }}" class="btn-glossy" style="width: auto; text-decoration: none;">+ Registrar Pago</a>
            </div>

            <form method="GET" action="{{ route('admin.pagos') }}" class="glass-panel" style="padding: 15px 20px; margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
                <div class="form-group" style="flex: 2 1 220px; margin-bottom: 0;">
                    <label><i class="fas fa-search"></i> Buscar (inquilino, propiedad, cédula)</label>
                    <input type="text" name="q" value="{{ $q }}" placeholder="Ej: Juan Pérez">
                </div>
                <div class="form-group" style="flex: 1 1 160px; margin-bottom: 0;">
                    <label><i class="fas fa-layer-group"></i> Estado</label>
                    <select name="estado">
                        <option value="">Todos</option>
                        <option value="pendiente" @selected($estado === 'pendiente')>Pendiente</option>
                        <option value="notificado" @selected($estado === 'notificado')>Notificado</option>
                        <option value="confirmado" @selected($estado === 'confirmado')>Confirmado</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1 1 150px; margin-bottom: 0;">
                    <label><i class="fas fa-calendar"></i> Desde</label>
                    <input type="date" name="desde" value="{{ $desde }}">
                </div>
                <div class="form-group" style="flex: 1 1 150px; margin-bottom: 0;">
                    <label><i class="fas fa-calendar"></i> Hasta</label>
                    <input type="date" name="hasta" value="{{ $hasta }}">
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn-glossy" style="width: auto; padding: 10px 20px;"><i class="fas fa-filter"></i> Filtrar</button>
                    @if($estado || $q !== '' || $desde || $hasta)
                        <a href="{{ route('admin.pagos') }}" class="btn-glossy" style="width: auto; padding: 10px 20px; background: rgba(255,255,255,0.15); text-decoration: none; text-align: center;">Limpiar</a>
                    @endif
                </div>
            </form>

            <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                            <th style="padding: 12px;">Inquilino</th>
                            <th style="padding: 12px;">Residencia</th>
                            <th style="padding: 12px;">Monto</th>
                            <th style="padding: 12px;">Fecha</th>
                            <th style="padding: 12px;">Estado</th>
                            <th style="padding: 12px;">Comprobante</th>
                            <th style="padding: 12px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagos as $pago)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 12px;">{{ $pago->asignacion?->usuario?->name ?? '—' }}</td>
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
                                @endphp
                                <form action="{{ route('admin.pagos.quick-estado', $pago->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <select name="estado_pago" onchange="this.form.submit()" title="Cambiar estado" style="width: auto; background: {{ $colores[$pago->estado_pago] ?? '#6b7280' }}; color: white; border: none; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem; cursor: pointer;">
                                        <option value="pendiente" @selected($pago->estado_pago === 'pendiente')>Pendiente</option>
                                        <option value="notificado" @selected($pago->estado_pago === 'notificado')>Notificado</option>
                                        <option value="confirmado" @selected($pago->estado_pago === 'confirmado')>Confirmado</option>
                                    </select>
                                </form>
                            </td>
                            <td style="padding: 12px;">
                                @if($pago->ruta_comprobante)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($pago->ruta_comprobante) }}" target="_blank" style="color: #86EFAC;"><i class="fas fa-file-alt"></i> Ver</a>
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding: 12px; text-align: center; display: flex; justify-content: center; gap: 15px;">
                                <a href="{{ route('admin.edit-pago', $pago->id) }}" style="color: #86EFAC; text-decoration: none;" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.destroy-pago', $pago->id) }}" method="POST" onsubmit="return confirm('¿Eliminar este pago?');" style="display: inline;">
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
                @if($pagos->isEmpty())
                    <p style="text-align: center; opacity: 0.8; margin-top: 20px;">
                        @if($estado || $q !== '' || $desde || $hasta)
                            No hay pagos que coincidan con los filtros aplicados.
                        @else
                            No hay pagos registrados actualmente.
                        @endif
                    </p>
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
        </main>
    </div>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        const alertaMensaje = document.getElementById('alerta-mensaje');
        if (alertaMensaje) {
            setTimeout(() => {
                alertaMensaje.style.opacity = '0';
                setTimeout(() => alertaMensaje.remove(), 600);
            }, 3000);
        }
    </script>
</body>
</html>
