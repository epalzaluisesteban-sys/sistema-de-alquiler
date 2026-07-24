<aside class="sidebar glass-panel" id="sidebar">
    <div style="display: flex; align-items: center; gap: 12px;">
        <img src="{{ asset('imagen/tl.png') }}" alt="Logo" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
        <div>
            <h2 style="margin: 0;">Panel Propietario</h2>
            <p style="margin: 4px 0 0 0; font-family: inherit; font-weight: normal; opacity: 0.8;">{{ auth()->user()->name }}</p>
        </div>
    </div>
    <hr style="border-color: rgba(255,255,255,0.2);">
    <a href="{{ route('admin.dashboard') }}"><i class="fas fa-chart-line"></i> Resumen</a>
    <a href="{{ route('admin.inquilinos') }}"><i class="fas fa-users"></i> Inquilinos</a>
    <a href="{{ route('admin.propiedades') }}"><i class="fas fa-building"></i> Propiedades</a>
    <a href="{{ route('admin.habitaciones') }}"><i class="fas fa-door-open"></i> Habitaciones</a>
    <a href="{{ route('admin.pagos') }}"><i class="fas fa-file-invoice-dollar"></i> Control de Pagos</a>
    <a href="{{ route('admin.notificaciones') }}"><i class="fas fa-bell"></i> Notificaciones</a>
    @if(strtolower(trim(auth()->user()->role ?? '')) === 'admin')
        <a href="{{ route('admin.encargados') }}"><i class="fas fa-user-plus"></i> Encargados</a>
    @endif
    <a href="javascript:void(0)" style="margin-top: auto; color: #ff6b6b;" onclick="document.getElementById('logout-form').submit();">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
    </a>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</aside>
