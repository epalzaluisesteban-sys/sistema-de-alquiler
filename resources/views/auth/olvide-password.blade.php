<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
</head>
<body>
    <div class="login-card glass-panel">
        <h2><i class="fas fa-key"></i> Recuperar contraseña</h2>
        <p>Como el sistema no usa correo, verifica tu cédula y tu teléfono registrados para continuar.</p>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('password.enviar-solicitud') }}" method="POST" id="olvide-form">
            @csrf
            <div class="form-group">
                <label>Cédula</label>
                <div class="form-row form-row-fija" style="gap: 10px;">
                    <select id="cedula-tipo" class="form-input" style="flex: 0 0 90px;" required>
                        <option value="V">V</option>
                        <option value="E">E</option>
                    </select>
                    <input type="text" id="cedula-numero" class="form-input" inputmode="numeric" maxlength="9" placeholder="Ej. 12345678" style="flex: 1;" required>
                </div>
                <input type="hidden" name="cedula" id="cedula" value="{{ old('cedula') }}">
            </div>

            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="telefono" class="form-input" value="{{ old('telefono') }}" placeholder="Igual al registrado en tu cuenta" required>
            </div>

            <button type="submit" class="btn-glossy">Verificar y continuar</button>
        </form>

        <p style="text-align: center; margin-top: 15px;">
            <a href="{{ route('login') }}">Volver al inicio de sesión</a>
        </p>
    </div>
    <script>
        const cedulaTipo = document.getElementById('cedula-tipo');
        const cedulaNumero = document.getElementById('cedula-numero');
        const cedulaHidden = document.getElementById('cedula');

        function actualizarCedula() {
            cedulaHidden.value = cedulaNumero.value ? (cedulaTipo.value + '-' + cedulaNumero.value) : '';
        }

        cedulaNumero.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            actualizarCedula();
        });
        cedulaTipo.addEventListener('change', actualizarCedula);

        if (cedulaHidden.value) {
            const match = cedulaHidden.value.match(/^([VE])-(\d+)$/);
            if (match) {
                cedulaTipo.value = match[1];
                cedulaNumero.value = match[2];
            } else {
                cedulaNumero.value = cedulaHidden.value.replace(/\D/g, '');
            }
        }
        actualizarCedula();
    </script>
</body>
</html>
