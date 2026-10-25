@extends('layouts.panel')

@section('title', 'Control de Pagos')

@push('head')
    <style>
        .filtro-pagos {
            display: grid;
            grid-template-columns: minmax(150px, 1.6fr) minmax(100px, 1fr) minmax(100px, 1fr) minmax(95px, 0.8fr) minmax(95px, 0.8fr) auto;
            gap: 10px;
            align-items: end;
        }
        .filtro-pagos .form-group { margin-bottom: 0; }
        .filtro-pagos label { font-size: 0.78rem; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .filtro-pagos input,
        .filtro-pagos select { padding: 8px 10px; font-size: 0.85rem; }
        .filtro-pagos .filtro-acciones { display: flex; gap: 8px; }
        .filtro-pagos .filtro-acciones .btn-glossy { padding: 8px 14px; font-size: 0.85rem; white-space: nowrap; }

        .filtro-pagos-toggle { display: none; }

        @media (max-width: 768px) {
            .filtro-pagos {
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            .filtro-pagos .filtro-buscar,
            .filtro-pagos .filtro-acciones { grid-column: 1 / -1; }

            .filtro-pagos-toggle {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 16px;
                margin-bottom: 10px;
                cursor: pointer;
                user-select: none;
            }
            .filtro-pagos-toggle .fa-chevron-down { transition: transform 0.2s ease; }
            .filtro-pagos-toggle.abierto .fa-chevron-down { transform: rotate(180deg); }

            .filtro-pagos { display: none; }
            .filtro-pagos.filtro-pagos--abierto { display: grid; }
        }
    </style>
@endpush

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-file-invoice-dollar"></i> Control de Pagos Mensuales</h2>
        <div style="display: flex; gap: 10px;">
            @if(auth()->user()->role === 'admin')
                <a href="{{ route('admin.pagos-papelera') }}" class="btn-glossy" style="width: auto; text-decoration: none; background: rgba(255,255,255,0.15);" title="Pagos eliminados"><i class="fas fa-trash-restore"></i> Papelera</a>
            @endif
            <a href="{{ route('admin.nuevo-pago') }}" class="btn-glossy" style="width: auto; text-decoration: none;">+ Registrar Pago</a>
        </div>
    </div>

    @php
        $filtrosActivos = $estado || $tipoPago || $q !== '' || $desde || $hasta;
    @endphp

    <div class="glass-panel filtro-pagos-toggle {{ $filtrosActivos ? 'abierto' : '' }}" id="filtro-pagos-toggle" onclick="toggleFiltroPagos()">
        <span><i class="fas fa-filter"></i> Filtros{{ $filtrosActivos ? ' (activos)' : '' }}</span>
        <i class="fas fa-chevron-down"></i>
    </div>

    <form method="GET" action="{{ route('admin.pagos') }}" id="filtro-pagos-form" class="glass-panel filtro-pagos {{ $filtrosActivos ? 'filtro-pagos--abierto' : '' }}" style="padding: 15px 20px; margin-bottom: 20px;">
        <div class="form-group filtro-buscar">
            <label><i class="fas fa-search"></i> Buscar</label>
            <input type="text" name="q" value="{{ $q }}" placeholder="Inquilino, propiedad o cédula">
        </div>
        <div class="form-group">
            <label><i class="fas fa-layer-group"></i> Estado</label>
            <select name="estado">
                <option value="">Todos</option>
                <option value="pendiente" @selected($estado === 'pendiente')>Pendiente</option>
                <option value="notificado" @selected($estado === 'notificado')>Notificado</option>
                <option value="confirmado" @selected($estado === 'confirmado')>Confirmado</option>
            </select>
        </div>
        <div class="form-group">
            <label><i class="fas fa-money-bill-wave"></i> Tipo de Pago</label>
            <select name="tipo_pago">
                <option value="">Todos</option>
                <option value="divisas" @selected($tipoPago === 'divisas')>Divisas</option>
                <option value="pago_movil" @selected($tipoPago === 'pago_movil')>Pago Móvil</option>
            </select>
        </div>
        <div class="form-group">
            <label><i class="fas fa-calendar"></i> Desde</label>
            <input type="date" name="desde" value="{{ $desde }}">
        </div>
        <div class="form-group">
            <label><i class="fas fa-calendar"></i> Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta }}">
        </div>
        <div class="filtro-acciones">
            <button type="submit" class="btn-glossy" style="width: auto;"><i class="fas fa-filter"></i> Filtrar</button>
            @if($estado || $tipoPago || $q !== '' || $desde || $hasta)
                <a href="{{ route('admin.pagos') }}" class="btn-glossy" style="width: auto; background: rgba(255,255,255,0.15); text-decoration: none; text-align: center;">Limpiar</a>
            @endif
        </div>
    </form>

    <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                    <th>Inquilino</th>
                    <th>Residencia</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                    <th>Tipo Pago</th>
                    <th>Comprob.</th>
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
                    <td>
                        @php
                            $colores = ['pendiente' => '#ef4444', 'notificado' => '#f59e0b', 'confirmado' => 'var(--accent-hover)'];
                        @endphp
                        <form action="{{ route('admin.pagos.quick-estado', $pago->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <select name="estado_pago" onchange="this.form.submit()" title="{{ $pago->confirmadoPor ? 'Confirmado por ' . $pago->confirmadoPor->name . ' el ' . $pago->confirmado_en?->format('d/m/Y H:i') : 'Cambiar estado' }}" style="width: auto; background: {{ $colores[$pago->estado_pago] ?? '#6b7280' }}; color: white; border: none; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem; cursor: pointer;">
                                <option value="pendiente" @selected($pago->estado_pago === 'pendiente')>Pendiente</option>
                                <option value="notificado" @selected($pago->estado_pago === 'notificado')>Notificado</option>
                                <option value="confirmado" @selected($pago->estado_pago === 'confirmado')>Confirmado</option>
                            </select>
                        </form>
                    </td>
                    <td>
                        @php
                            $etiquetasTipoPago = ['divisas' => 'Divisas', 'pago_movil' => 'Pago Móvil'];
                        @endphp
                        {{ $etiquetasTipoPago[$pago->tipo_pago] ?? '—' }}
                    </td>
                    <td>
                        @if($pago->ruta_comprobante)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($pago->ruta_comprobante) }}" target="_blank" style="color: var(--accent-light);"><i class="fas fa-file-alt"></i> Ver</a>
                        @else
                            —
                        @endif
                    </td>
                    <td style="text-align: center; display: flex; justify-content: center; gap: 15px;">
                        <a href="{{ route('admin.edit-pago', $pago->id) }}" style="color: var(--accent-light); text-decoration: none;" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.destroy-pago', $pago->id) }}" method="POST" onsubmit="return confirm('¿Enviar este pago a la papelera?');" style="display: inline;">
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
                @if($estado || $tipoPago || $q !== '' || $desde || $hasta)
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
                        <span style="padding: 8px 12px; border-radius: 6px; background: var(--accent-dark); color: white;">{{ $page }}</span>
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
@endsection

@push('scripts')
    <script>
        function toggleFiltroPagos() {
            document.getElementById('filtro-pagos-form').classList.toggle('filtro-pagos--abierto');
            document.getElementById('filtro-pagos-toggle').classList.toggle('abierto');
        }
    </script>
@endpush
