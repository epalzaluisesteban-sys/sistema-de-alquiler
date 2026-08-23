@extends('layouts.panel')

@section('title', 'Registrar Encargado')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2><i class="fas fa-user-plus"></i> Registrar Encargado</h2>
        <a href="{{ route('admin.encargados') }}" style="color: white; text-decoration: none;"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="glass-panel" style="padding: 30px; max-width: 600px; margin: 0 auto;">
        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.3); padding: 10px; border-radius: 8px; margin-bottom: 15px;">
                <ul style="margin: 0; color: #ff9b9b;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.store-encargado') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-id-card"></i> Nombre</label>
                    <input type="text" name="nombre" class="solo-letras" value="{{ old('nombre') }}" pattern="[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+" title="Solo se permiten letras" required>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label><i class="fas fa-id-card"></i> Apellido</label>
                    <input type="text" name="apellido" class="solo-letras" value="{{ old('apellido') }}" pattern="[A-Za-zÁÉÍÓÚÑÜáéíóúñü\s]+" title="Solo se permiten letras" required>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-id-card"></i> Cédula</label>
                <div class="form-row" style="gap: 10px;">
                    <select id="cedula-tipo" style="flex: 0 0 90px;" required>
                        <option value="V" @selected(old('cedula_tipo', 'V') === 'V')>V</option>
                        <option value="E" @selected(old('cedula_tipo') === 'E')>E</option>
                    </select>
                    <input type="text" id="cedula-numero" inputmode="numeric" maxlength="9" placeholder="Ej. 12345678" value="{{ old('cedula_numero') }}" style="flex: 1;" required>
                </div>
                <input type="hidden" name="cedula" id="cedula" value="{{ old('cedula') }}">
                <small style="color: rgba(255,255,255,0.6);">V: Venezolano &nbsp; E: Extranjero</small>
            </div>
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Teléfono</label>
                <div class="form-row" style="gap: 10px;">
                    <select id="telefono-prefijo" style="flex: 0 0 170px;"></select>
                    <input type="text" id="telefono-numero" inputmode="numeric" maxlength="15" placeholder="Ej. 4121234567" style="flex: 1;">
                </div>
                <input type="hidden" name="telefono" id="telefono" value="{{ old('telefono') }}">
            </div>

            <div class="form-group">
                <label><i class="fas fa-key"></i> Residencia (opcional)</label>
                <select id="rental-type" name="rental_type" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                    <option value="">-- No vive en una propiedad --</option>
                    <option value="Casa" @selected(old('rental_type') === 'Casa')>Propiedad completa (Casa)</option>
                    <option value="Habitación" @selected(old('rental_type') === 'Habitación')>Habitación</option>
                </select>
                <small style="color: rgba(255,255,255,0.6);">Si el encargado también vive en una de las propiedades, selecciónala aquí.</small>
            </div>

            <div class="form-group" id="house-field" style="display: none;">
                <label><i class="fas fa-building"></i> Propiedad</label>
                <select id="house-select" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                    <option value="">Seleccione una propiedad</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}">{{ $property->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" id="room-field" style="display: none;">
                <label><i class="fas fa-door-open"></i> Habitación</label>
                <select id="room-select" style="width: 100%; padding: 10px; border-radius: 8px; background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3);">
                    <option value="">Seleccione una habitación</option>
                    @foreach($rooms as $room)
                        <option value="{{ $room->id }}">Habitación {{ $room->numero }} — {{ $room->propiedad?->nombre ?? 'Sin propiedad' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-lock"></i> Contraseña</label>
                <div class="password-wrapper">
                    <input type="password" name="contrasena" id="contrasena" required>
                    <button type="button" id="toggle-password" class="toggle-password" aria-label="Mostrar contraseña">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" class="btn-glossy">Guardar Encargado</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rental = document.getElementById('rental-type');
            const houseField = document.getElementById('house-field');
            const roomField = document.getElementById('room-field');
            const houseSelect = document.getElementById('house-select');
            const roomSelect = document.getElementById('room-select');

            function updateResidenciaField() {
                const esCasa = rental.value === 'Casa';
                const esHabitacion = rental.value === 'Habitación';

                houseField.style.display = esCasa ? '' : 'none';
                roomField.style.display = esHabitacion ? '' : 'none';

                houseSelect.required = esCasa;
                roomSelect.required = esHabitacion;

                houseSelect.name = esCasa ? 'property_id' : '';
                roomSelect.name = esHabitacion ? 'property_id' : '';
                if (!esCasa) houseSelect.removeAttribute('name');
                if (!esHabitacion) roomSelect.removeAttribute('name');
            }

            rental.addEventListener('change', updateResidenciaField);
            updateResidenciaField();

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

            if (cedulaHidden.value.includes('-')) {
                const [tipo, numero] = cedulaHidden.value.split('-');
                cedulaTipo.value = tipo;
                cedulaNumero.value = numero;
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

            if (telefonoHidden.value.includes(' ')) {
                const [prefijo, numero] = telefonoHidden.value.split(' ');
                if ([...telefonoPrefijo.options].some(o => o.value === prefijo)) {
                    telefonoPrefijo.value = prefijo;
                }
                telefonoNumero.value = numero;
            }

            // Mostrar/ocultar contraseña
            document.getElementById('toggle-password').addEventListener('click', function () {
                const input = document.getElementById('contrasena');
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
