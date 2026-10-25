@extends('layouts.panel')

@section('title', 'Ajustes')

@php
    $tabActiva = $errors->hasAny(['tema_fondo', 'tema_color', 'tema_fuente', 'tema_boton'])
        ? 'personalizacion'
        : ($errors->has('precio_habitacion') ? 'precio-habitacion' : 'datos');
@endphp

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-gear"></i> Ajustes</h2>
        <a href="{{ route('admin.dashboard') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="ajustes-tabs">
        <button type="button" class="ajustes-tab @if($tabActiva === 'datos') is-active @endif" data-tab="datos"><i class="fas fa-user-edit"></i> Datos Personales</button>
        <button type="button" class="ajustes-tab @if($tabActiva === 'personalizacion') is-active @endif" data-tab="personalizacion"><i class="fas fa-palette"></i> Personalización</button>
        <button type="button" class="ajustes-tab @if($tabActiva === 'precio-habitacion') is-active @endif" data-tab="precio-habitacion"><i class="fas fa-tags"></i> Precio de Habitación</button>
        <button type="button" class="ajustes-tab @if($tabActiva === 'historial-inquilinos') is-active @endif" data-tab="historial-inquilinos"><i class="fas fa-clock-rotate-left"></i> Historial de Inquilinos</button>
    </div>

    @if ($errors->any())
        <div class="glass-panel" style="background: rgba(239, 68, 68, 0.3); padding: 10px; border-radius: 16px; margin-bottom: 15px; max-width: 600px; margin-left: auto; margin-right: auto;">
            <ul style="margin: 0; color: #ff9b9b;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ajustes-panel @if($tabActiva === 'datos') is-active @endif" data-panel="datos">
        <div class="glass-panel" style="padding: 30px; max-width: 600px; margin: 0 auto;">
            <form action="{{ route('admin.update-ajustes') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label><i class="fas fa-id-card"></i> Nombre</label>
                        <input type="text" name="nombre" class="solo-letras" value="{{ old('nombre', $usuario->nombre) }}" pattern="[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+" title="Solo se permiten letras" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label><i class="fas fa-id-card"></i> Apellido</label>
                        <input type="text" name="apellido" class="solo-letras" value="{{ old('apellido', $usuario->apellido) }}" pattern="[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+" title="Solo se permiten letras" required>
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Cédula</label>
                    <div class="form-row form-row-fija" style="gap: 10px;">
                        <select id="cedula-tipo" style="flex: 0 0 90px;" required>
                            <option value="V">V</option>
                            <option value="E">E</option>
                        </select>
                        <input type="text" id="cedula-numero" inputmode="numeric" maxlength="9" placeholder="Ej. 12345678" style="flex: 1;" required>
                    </div>
                    <input type="hidden" name="cedula" id="cedula" value="{{ old('cedula', $usuario->cedula) }}">
                    <small style="color: rgba(255,255,255,0.6);">V: Venezolano &nbsp; E: Extranjero</small>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-phone"></i> Teléfono de Contacto</label>
                    <div class="form-row form-row-fija" style="gap: 10px;">
                        <select id="telefono-prefijo" style="flex: 0 0 170px;"></select>
                        <input type="text" id="telefono-numero" inputmode="numeric" maxlength="15" placeholder="Ej. 4121234567" style="flex: 1;">
                    </div>
                    <input type="hidden" name="telefono" id="telefono" value="{{ old('telefono', $usuario->telefono) }}">
                </div>

                <div class="form-group">
                    <label><i class="fas fa-lock"></i> Nueva Contraseña <span style="font-weight: normal; opacity: 0.7;">(dejar vacío para no cambiarla)</span></label>
                    <div class="password-wrapper">
                        <input type="password" name="password" id="password">
                        <button type="button" id="toggle-password" class="toggle-password" aria-label="Mostrar contraseña">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn-glossy">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ajustes-panel @if($tabActiva === 'personalizacion') is-active @endif" data-panel="personalizacion">
        <div class="glass-panel" style="padding: 30px; max-width: 600px; margin: 0 auto;">
            <form action="{{ route('admin.update-personalizacion') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label><i class="fas fa-image"></i> Fondo</label>
                    <div class="tema-opciones">
                        @foreach($fondos as $clave => $fondo)
                            <label class="tema-opcion">
                                <input type="radio" name="tema_fondo" value="{{ $clave }}" @checked(old('tema_fondo', $usuario->tema_fondo ?? 'clasico') === $clave)>
                                <span class="tema-opcion-tarjeta">
                                    <span class="tema-swatch" style="background: {{ $fondo['swatch'] }};"></span>
                                    <span class="tema-opcion-label">{{ $fondo['label'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-palette"></i> Color de Acento</label>
                    <div class="tema-opciones">
                        @foreach($colores as $clave => $color)
                            <label class="tema-opcion">
                                <input type="radio" name="tema_color" value="{{ $clave }}" @checked(old('tema_color', $usuario->tema_color ?? 'verde') === $clave)>
                                <span class="tema-opcion-tarjeta">
                                    <span class="tema-swatch" style="background: linear-gradient(135deg, {{ $color['base'] }} 0%, {{ $color['dark'] }} 100%);"></span>
                                    <span class="tema-opcion-label">{{ $color['label'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-font"></i> Fuente</label>
                    <div class="tema-opciones">
                        @foreach($fuentes as $clave => $fuente)
                            <label class="tema-opcion">
                                <input type="radio" name="tema_fuente" value="{{ $clave }}" @checked(old('tema_fuente', $usuario->tema_fuente ?? 'segoe') === $clave)>
                                <span class="tema-opcion-tarjeta">
                                    <span class="tema-swatch tema-swatch-fuente" style="font-family: {{ $fuente['css'] }};">{{ $fuente['sample'] }}</span>
                                    <span class="tema-opcion-label">{{ $fuente['label'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="form-group">
                    <label><i class="fas fa-square"></i> Estilo de Botón</label>
                    <div class="tema-opciones">
                        @foreach($botones as $clave => $boton)
                            <label class="tema-opcion">
                                <input type="radio" name="tema_boton" value="{{ $clave }}" @checked(old('tema_boton', $usuario->tema_boton ?? 'glossy') === $clave)>
                                <span class="tema-opcion-tarjeta">
                                    <span class="btn-glossy" data-preview-btn="{{ $clave }}" style="padding: 8px; font-size: 0.7rem; pointer-events: none;">Aa</span>
                                    <span class="tema-opcion-label">{{ $boton['label'] }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn-glossy">Guardar Personalización</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ajustes-panel @if($tabActiva === 'precio-habitacion') is-active @endif" data-panel="precio-habitacion">
        <div class="glass-panel" style="padding: 30px; max-width: 600px; margin: 0 auto;">
            <p style="opacity: 0.8; margin-top: 0;">Este precio se aplica automáticamente a cada nueva habitación que registres; ya no se pide al crear la habitación ni al registrar un inquilino por habitación.</p>
            <form action="{{ route('admin.update-precio-habitacion') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label><i class="fas fa-dollar-sign"></i> Precio Mensual de Habitación</label>
                    <input type="number" step="0.01" min="0" name="precio_habitacion" value="{{ old('precio_habitacion', $usuario->precio_habitacion) }}" placeholder="Ej. 150" required>
                </div>

                <div style="text-align: center; margin-top: 20px;">
                    <button type="submit" class="btn-glossy">Guardar Precio</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ajustes-panel @if($tabActiva === 'historial-inquilinos') is-active @endif" data-panel="historial-inquilinos">
        <div class="glass-panel" style="padding: 20px; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; color: #D1D5DB;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.137); text-align: left;">
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Residencia Actual</th>
                        <th>Residencias Registradas</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inquilinos as $inquilino)
                    @php($asignacion = $inquilino->asignaciones->first())
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                        <td>{{ $inquilino->name }}</td>
                        <td>{{ $inquilino->cedula ?? '—' }}</td>
                        <td>
                            @if($asignacion && $asignacion->habitacion)
                                Hab. {{ $asignacion->habitacion->numero }} — {{ $asignacion->habitacion->propiedad?->nombre ?? 'Sin propiedad' }}
                            @elseif($asignacion && $asignacion->propiedad)
                                {{ $asignacion->propiedad->nombre }} (Casa completa)
                            @else
                                <span style="opacity: 0.7;">Sin residencia activa</span>
                            @endif
                        </td>
                        <td>{{ $inquilino->asignaciones_count }}</td>
                        <td style="text-align: center;">
                            <a href="{{ route('admin.historial-inquilino', $inquilino->id) }}" style="color: #FCD34D; text-decoration: none;">
                                <i class="fas fa-clock-rotate-left"></i> Ver historial
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if($inquilinos->isEmpty())
                <p style="text-align: center; opacity: 0.8; margin-top: 20px;">No hay inquilinos registrados actualmente.</p>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Pestañas de Ajustes: alterna qué panel se muestra sin recargar
            document.querySelectorAll('.ajustes-tab[data-tab]').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    document.querySelectorAll('.ajustes-tab[data-tab]').forEach(t => t.classList.remove('is-active'));
                    document.querySelectorAll('.ajustes-panel').forEach(p => p.classList.remove('is-active'));

                    this.classList.add('is-active');
                    document.querySelector('.ajustes-panel[data-panel="' + this.dataset.tab + '"]').classList.add('is-active');
                });
            });

            // Nombre y apellido: solo letras y espacios
            document.querySelectorAll('.solo-letras').forEach(function (input) {
                input.addEventListener('input', function () {
                    this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]/g, '');
                });
            });

            // Cédula: V/E + solo números, combinados en el campo oculto "cedula"
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

            // Prefijos telefónicos del mundo
            const prefijos = [
                ['Afganistán', '+93'], ['Albania', '+355'], ['Alemania', '+49'], ['Andorra', '+376'],
                ['Angola', '+244'], ['Antigua y Barbuda', '+1268'], ['Arabia Saudita', '+966'], ['Argelia', '+213'],
                ['Argentina', '+54'], ['Armenia', '+374'], ['Australia', '+61'], ['Austria', '+43'],
                ['Azerbaiyán', '+994'], ['Bahamas', '+1242'], ['Bangladés', '+880'], ['Barbados', '+1246'],
                ['Baréin', '+973'], ['Bélgica', '+32'], ['Belice', '+501'], ['Benín', '+229'],
                ['Bielorrusia', '+375'], ['Birmania (Myanmar)', '+95'], ['Bolivia', '+591'],
                ['Bosnia y Herzegovina', '+387'], ['Botsuana', '+267'], ['Brasil', '+55'], ['Brunéi', '+673'],
                ['Bulgaria', '+359'], ['Burkina Faso', '+226'], ['Burundi', '+257'], ['Bután', '+975'],
                ['Cabo Verde', '+238'], ['Camboya', '+855'], ['Camerún', '+237'], ['Canadá', '+1'],
                ['Catar', '+974'], ['Chad', '+235'], ['Chile', '+56'], ['China', '+86'], ['Chipre', '+357'],
                ['Ciudad del Vaticano', '+379'], ['Colombia', '+57'], ['Comoras', '+269'],
                ['Corea del Norte', '+850'], ['Corea del Sur', '+82'], ['Costa de Marfil', '+225'],
                ['Costa Rica', '+506'], ['Croacia', '+385'], ['Cuba', '+53'], ['Dinamarca', '+45'],
                ['Dominica', '+1767'], ['Ecuador', '+593'], ['Egipto', '+20'], ['El Salvador', '+503'],
                ['Emiratos Árabes Unidos', '+971'], ['Eritrea', '+291'], ['Eslovaquia', '+421'],
                ['Eslovenia', '+386'], ['España', '+34'], ['Estados Unidos', '+1'], ['Estonia', '+372'],
                ['Esuatini (Suazilandia)', '+268'], ['Etiopía', '+251'], ['Filipinas', '+63'], ['Finlandia', '+358'],
                ['Fiyi', '+679'], ['Francia', '+33'], ['Gabón', '+241'], ['Gambia', '+220'], ['Georgia', '+995'],
                ['Ghana', '+233'], ['Granada', '+1473'], ['Grecia', '+30'], ['Guatemala', '+502'],
                ['Guyana', '+592'], ['Guinea', '+224'], ['Guinea-Bisáu', '+245'], ['Guinea Ecuatorial', '+240'],
                ['Haití', '+509'], ['Honduras', '+504'], ['Hungría', '+36'], ['India', '+91'],
                ['Indonesia', '+62'], ['Irak', '+964'], ['Irán', '+98'], ['Irlanda', '+353'], ['Islandia', '+354'],
                ['Islas Marshall', '+692'], ['Islas Salomón', '+677'], ['Israel', '+972'], ['Italia', '+39'],
                ['Jamaica', '+1876'], ['Japón', '+81'], ['Jordania', '+962'], ['Kazajistán', '+7'],
                ['Kenia', '+254'], ['Kirguistán', '+996'], ['Kiribati', '+686'], ['Kuwait', '+965'],
                ['Laos', '+856'], ['Lesoto', '+266'], ['Letonia', '+371'], ['Líbano', '+961'], ['Liberia', '+231'],
                ['Libia', '+218'], ['Liechtenstein', '+423'], ['Lituania', '+370'], ['Luxemburgo', '+352'],
                ['Macedonia del Norte', '+389'], ['Madagascar', '+261'], ['Malasia', '+60'], ['Malaui', '+265'],
                ['Maldivas', '+960'], ['Malí', '+223'], ['Malta', '+356'], ['Marruecos', '+212'],
                ['Mauricio', '+230'], ['Mauritania', '+222'], ['México', '+52'], ['Micronesia', '+691'],
                ['Moldavia', '+373'], ['Mónaco', '+377'], ['Mongolia', '+976'], ['Montenegro', '+382'],
                ['Mozambique', '+258'], ['Namibia', '+264'], ['Nauru', '+674'], ['Nepal', '+977'],
                ['Nicaragua', '+505'], ['Níger', '+227'], ['Nigeria', '+234'], ['Noruega', '+47'],
                ['Nueva Zelanda', '+64'], ['Omán', '+968'], ['Países Bajos', '+31'], ['Pakistán', '+92'],
                ['Palaos', '+680'], ['Panamá', '+507'], ['Papúa Nueva Guinea', '+675'], ['Paraguay', '+595'],
                ['Perú', '+51'], ['Polonia', '+48'], ['Portugal', '+351'], ['Reino Unido', '+44'],
                ['República Centroafricana', '+236'], ['República Checa', '+420'], ['República del Congo', '+242'],
                ['República Democrática del Congo', '+243'], ['República Dominicana', '+1809'], ['Ruanda', '+250'],
                ['Rumanía', '+40'], ['Rusia', '+7'], ['Samoa', '+685'], ['San Cristóbal y Nieves', '+1869'],
                ['San Marino', '+378'], ['San Vicente y las Granadinas', '+1784'], ['Santa Lucía', '+1758'],
                ['Santo Tomé y Príncipe', '+239'], ['Senegal', '+221'], ['Serbia', '+381'], ['Seychelles', '+248'],
                ['Sierra Leona', '+232'], ['Singapur', '+65'], ['Siria', '+963'], ['Somalia', '+252'],
                ['Sri Lanka', '+94'], ['Sudáfrica', '+27'], ['Sudán', '+249'], ['Sudán del Sur', '+211'],
                ['Suecia', '+46'], ['Suiza', '+41'], ['Surinam', '+597'], ['Tailandia', '+66'],
                ['Tanzania', '+255'], ['Tayikistán', '+992'], ['Timor Oriental', '+670'], ['Togo', '+228'],
                ['Tonga', '+676'], ['Trinidad y Tobago', '+1868'], ['Túnez', '+216'], ['Turkmenistán', '+993'],
                ['Turquía', '+90'], ['Tuvalu', '+688'], ['Ucrania', '+380'], ['Uganda', '+256'],
                ['Uruguay', '+598'], ['Uzbekistán', '+998'], ['Vanuatu', '+678'], ['Venezuela', '+58'],
                ['Vietnam', '+84'], ['Yemen', '+967'], ['Yibuti', '+253'], ['Zambia', '+260'], ['Zimbabue', '+263'],
            ];

            const telefonoPrefijo = document.getElementById('telefono-prefijo');
            const telefonoNumero = document.getElementById('telefono-numero');
            const telefonoHidden = document.getElementById('telefono');

            prefijos.forEach(function ([pais, dial]) {
                const option = document.createElement('option');
                option.value = dial;
                option.textContent = pais + ' (' + dial + ')';
                telefonoPrefijo.appendChild(option);
            });
            telefonoPrefijo.value = '+58';

            function actualizarTelefono() {
                const numeroSinCero = telefonoNumero.value.replace(/^0+/, '');
                telefonoHidden.value = numeroSinCero ? (telefonoPrefijo.value + ' ' + numeroSinCero) : '';
            }

            telefonoNumero.addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '');
                actualizarTelefono();
            });
            telefonoPrefijo.addEventListener('change', actualizarTelefono);

            if (telefonoHidden.value) {
                const match = telefonoHidden.value.match(/^(\+\d{1,4})\s(\d+)$/);
                if (match && [...telefonoPrefijo.options].some(o => o.value === match[1])) {
                    telefonoPrefijo.value = match[1];
                    telefonoNumero.value = match[2];
                } else {
                    telefonoNumero.value = telefonoHidden.value.replace(/\D/g, '');
                }
            }
            actualizarTelefono();

            // Mostrar/ocultar contraseña
            document.getElementById('toggle-password').addEventListener('click', function () {
                const input = document.getElementById('password');
                const icon = this.querySelector('i');
                const mostrando = input.type === 'text';

                input.type = mostrando ? 'password' : 'text';
                icon.classList.toggle('fa-eye', mostrando);
                icon.classList.toggle('fa-eye-slash', !mostrando);
                this.setAttribute('aria-label', mostrando ? 'Mostrar contraseña' : 'Ocultar contraseña');
            });
        });
    </script>
@endpush
