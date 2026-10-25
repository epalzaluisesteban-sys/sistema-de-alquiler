<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva contraseña</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="login-card glass-panel">
        <h2><i class="fas fa-lock"></i> Establece tu nueva contraseña</h2>
        <p>Este enlace es de un solo uso y vence a los 15 minutos de haberlo solicitado.</p>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.reset', ['usuario' => $usuario->id, 'token' => $token]) }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nueva contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password" class="form-input" minlength="6" required>
                </div>
            </div>

            <div class="form-group">
                <label>Confirmar contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password_confirmation" class="form-input" minlength="6" required>
                </div>
            </div>

            <button type="submit" class="btn-glossy">Guardar nueva contraseña</button>
        </form>
    </div>
</body>
</html>
