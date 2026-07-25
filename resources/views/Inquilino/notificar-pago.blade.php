<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificar Pago</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="tenant-container glass-panel">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; margin-bottom: 20px;">
            <h2><i class="fas fa-upload"></i> Notificar Pago</h2>
            <a href="{{ route('tenant.dashboard') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
        </div>

        <div class="glass-panel" style="padding: 30px; max-width: 500px; margin: 0 auto;">
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
                <p style="text-align: center; opacity: 0.8;">No tienes un alquiler activo asignado, por lo que no puedes notificar un pago.</p>
            @else
                <p style="opacity: 0.85; margin-bottom: 20px;">
                    Indica el monto y la fecha del pago que realizaste. El encargado revisará y confirmará tu pago.
                </p>
                <form action="{{ route('tenant.store-notificar-pago') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label><i class="fas fa-dollar-sign"></i> Monto</label>
                        <input type="number" step="0.01" min="0" name="monto" value="{{ old('monto') }}" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Fecha</label>
                        <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-paperclip"></i> Comprobante (opcional, JPG/PNG/PDF, máx. 5MB)</label>
                        <input type="file" name="comprobante" accept=".jpg,.jpeg,.png,.pdf">
                    </div>
                    <div style="text-align: center; margin-top: 20px;">
                        <button type="submit" class="btn-glossy">Notificar Pago</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</body>
</html>
