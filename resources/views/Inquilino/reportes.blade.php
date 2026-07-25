<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Problema</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="tenant-container glass-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h2><i class="fas fa-tools"></i> Reportar Problema</h2>
            <a href="{{ route('tenant.dashboard') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        @if(session('success'))
            <div id="alerta-mensaje" class="glass-panel" style="padding: 15px; margin-bottom: 20px; background: rgba(22, 163, 74, 0.3); border-color: #22c55e;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="glass-panel" style="padding: 30px; max-width: 600px; margin: 0 auto 30px;">
            @if ($errors->any())
                <div style="background: rgba(239, 68, 68, 0.3); padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                    <ul style="margin: 0; color: #ff9b9b;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(! $asignacion)
                <p style="text-align: center; opacity: 0.8;">No tienes un alquiler activo asignado, por lo que no puedes crear un reporte.</p>
            @else
                <form action="{{ route('tenant.store-reporte') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label><i class="fas fa-align-left"></i> Describe el problema</label>
                        <textarea name="descripcion" rows="4" required style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">{{ old('descripcion') }}</textarea>
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-glossy">Enviar Reporte</button>
                    </div>
                </form>
            @endif
        </div>

        <h3 style="margin-bottom: 15px;"><i class="fas fa-list"></i> Mis Reportes</h3>
        <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                        <th style="padding: 12px;">Descripción</th>
                        <th style="padding: 12px;">Fecha</th>
                        <th style="padding: 12px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reportes as $reporte)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td style="padding: 12px;">{{ $reporte->descripcion }}</td>
                        <td style="padding: 12px;">{{ $reporte->fecha?->format('d/m/Y') }}</td>
                        <td style="padding: 12px;">
                            @php
                                $colores = ['pendiente' => '#ef4444', 'en_revision' => '#f59e0b', 'resuelto' => '#22c55e'];
                                $etiquetas = ['pendiente' => 'Pendiente', 'en_revision' => 'En revisión', 'resuelto' => 'Resuelto'];
                            @endphp
                            <span style="background: {{ $colores[$reporte->estado_reporte] ?? '#6b7280' }}; color: white; padding: 4px 10px; border-radius: 5px; font-size: 0.8rem;">
                                {{ $etiquetas[$reporte->estado_reporte] ?? ucfirst($reporte->estado_reporte) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($reportes->isEmpty())
                <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No has creado ningún reporte todavía.</p>
            @endif
        </div>
    </div>
    <script>
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
