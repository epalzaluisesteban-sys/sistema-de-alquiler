<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inquilinos</title>
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
                <h2><i class="fas fa-users"></i> Listado de Inquilinos</h2>
                <a href="{{ route('admin.nuevo-inquilino') }}" class="btn-glossy" style="width: auto; text-decoration: none;">+ Nuevo Registro</a>
            </div>

            <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                            <th style="padding: 12px;">Nombre</th>
                            <th style="padding: 12px;">Cédula</th>
                            <th style="padding: 12px;">Teléfono</th>
                            <th style="padding: 12px;">Residencia</th>
                            <th style="padding: 12px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inquilinos as $inquilino)
                        @php($asignacion = $inquilino->asignaciones->first())
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 12px;">{{ $inquilino->name }}</td>
                            <td style="padding: 12px;">{{ $inquilino->cedula ?? '—' }}</td>
                            <td style="padding: 12px;">{{ $inquilino->telefono ?? '—' }}</td>
                            <td style="padding: 12px;">
                                @if($asignacion && $asignacion->habitacion)
                                    Hab. {{ $asignacion->habitacion->numero }} — {{ $asignacion->habitacion->propiedad?->nombre ?? 'Sin propiedad' }}
                                @elseif($asignacion && $asignacion->propiedad)
                                    {{ $asignacion->propiedad->nombre }} (Casa completa)
                                @else
                                    <span style="opacity: 0.7;">Sin asignar</span>
                                @endif
                            </td>
                            <td style="padding: 12px; text-align: center; display: flex; justify-content: center; gap: 15px;">
                                @if($asignacion)
                                    <x-tooltip texto="Registrar pago">
                                        <a href="{{ route('admin.nuevo-pago', ['asignacion' => $asignacion->id]) }}" style="color: #7DD3FC; text-decoration: none;">
                                            <i class="fas fa-dollar-sign"></i>
                                        </a>
                                    </x-tooltip>
                                @endif
                                <x-tooltip texto="Ver historial">
                                    <a href="{{ route('admin.historial-inquilino', $inquilino->id) }}" style="color: #FCD34D; text-decoration: none;">
                                        <i class="fas fa-clock-rotate-left"></i>
                                    </a>
                                </x-tooltip>
                                <x-tooltip texto="Editar">
                                    <a href="{{ route('admin.edit-inquilino', $inquilino->id) }}" style="color: #86EFAC; text-decoration: none;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </x-tooltip>
                                <x-tooltip texto="Eliminar">
                                    <form action="{{ route('admin.destroy-inquilino', $inquilino->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este inquilino?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: #ff6b6b; cursor: pointer;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </x-tooltip>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($inquilinos->isEmpty())
                    <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No hay inquilinos registrados actualmente.</p>
                @endif
            </div>
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
