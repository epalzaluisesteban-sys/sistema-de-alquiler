<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Habitaciones</title>
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
                <h2><i class="fas fa-door-open"></i> Listado de Habitaciones</h2>
                <a href="{{ route('admin.nueva-habitacion') }}" class="btn-glossy" style="width: auto; text-decoration: none;">+ Nueva Habitación</a>
            </div>

            <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                            <th style="padding: 12px;">Propiedad</th>
                            <th style="padding: 12px;">Número</th>
                            <th style="padding: 12px;">Precio</th>
                            <th style="padding: 12px;">Estado</th>
                            <th style="padding: 12px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($habitaciones as $habitacion)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                            <td style="padding: 12px;">{{ $habitacion->propiedad?->nombre ?? '—' }}</td>
                            <td style="padding: 12px;">{{ $habitacion->numero }}</td>
                            <td style="padding: 12px;">${{ number_format($habitacion->precio, 2) }}</td>
                            <td style="padding: 12px;">
                                <span style="background: {{ $habitacion->estado === 'disponible' ? '#22c55e' : ($habitacion->estado === 'ocupada' ? '#ef4444' : '#f59e0b') }}; padding: 4px 8px; border-radius: 5px; font-size: 0.8rem;">
                                    {{ ucfirst($habitacion->estado) }}
                                </span>
                            </td>
                            <td style="padding: 12px; text-align: center; display: flex; justify-content: center; gap: 15px;">
                                <a href="{{ route('admin.edit-habitacion', $habitacion->id) }}" style="color: #86EFAC; text-decoration: none;" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.destroy-habitacion', $habitacion->id) }}" method="POST" onsubmit="return confirm('¿Eliminar esta habitación? También se eliminarán sus asignaciones y pagos asociados.');" style="display: inline;">
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
                @if($habitaciones->isEmpty())
                    <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No hay habitaciones registradas actualmente.</p>
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
