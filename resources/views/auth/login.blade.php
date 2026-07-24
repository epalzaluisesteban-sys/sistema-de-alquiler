<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" href="{{ asset('imagen/tl.png') }}">
    @vite(['public/css/style.css', 'resources/js/app.js'])
    
</head>
<body>
    <div class="login-card glass-panel">
        <h2><i class="fas fa-lock"></i> Acceso al Sistema</h2>
        <p>Ingresa tus credenciales para continuar</p>

        @if ($errors->any())
            <div class="alert-error">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <form action="{{ route('login.post') }}" method="POST" id="login-form">
            @csrf
            <div class="form-group">
                <label>Cédula</label>
                <input type="text" name="cedula" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="password" id="password" class="form-input" required>
                    <button type="button" id="toggle-password" class="toggle-password" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-glossy" id="login-btn">Iniciar Sesión</button>
        </form>
    </div>
    <script>
        document.getElementById('login-form').addEventListener('submit', function () {
            const boton = document.getElementById('login-btn');
            boton.disabled = true;
            boton.textContent = 'Iniciando sesión...';
        });

        document.getElementById('toggle-password').addEventListener('click', function () {
            const input = document.getElementById('password');
            const icon = this.querySelector('i');
            const mostrando = input.type === 'text';

            input.type = mostrando ? 'password' : 'text';
            icon.classList.toggle('fa-eye', mostrando);
            icon.classList.toggle('fa-eye-slash', !mostrando);
            this.setAttribute('aria-label', mostrando ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    </script>
</body>
</html>