@extends('layouts.tenant')

@section('title', 'Mi Alquiler')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <img src="{{ asset('imagen/tl.png') }}" alt="Logo" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">
            <div>
                <h2 style="margin: 0;">Hola, {{ $usuario->name ?? 'Inquilino' }}</h2>
                <p style="margin: 4px 0 0 0; font-family: inherit; font-weight: normal; opacity: 0.8;">
                    @if($asignacion?->habitacion)
                        Hab. {{ $asignacion->habitacion->numero }} — {{ $asignacion->habitacion->propiedad?->nombre ?? 'Sin propiedad' }}
                    @elseif($asignacion?->propiedad)
                        {{ $asignacion->propiedad->nombre }} (Casa)
                    @else
                        Aún no tienes un alquiler asignado
                    @endif
                </p>
            </div>
        </div>
        <a href="javascript:void(0)" style="color: #ff6b6b; text-decoration: none; font-weight: 600;" onclick="document.getElementById('logout-form').submit();">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a>
        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
            @csrf
        </form>
    </div>
    <hr style="border-color: rgba(255,255,255,0.2); margin: 20px 0;">

    <div class="action-cards">
        <div class="action-card glass-panel">
            <i class="fas fa-history fa-3x" style="margin-bottom: 15px;"></i>
            <h4>Historial de Pagos</h4>
            <a href="{{ route('tenant.pagos') }}" class="btn-glossy" style="padding: 8px; display: inline-block; text-decoration: none; text-align: center;">Ver Historial</a>
        </div>

        <div class="action-card glass-panel">
            <i class="fas fa-upload fa-3x" style="margin-bottom: 15px;"></i>
            <h4>Notificar Pago</h4>
            <a href="{{ route('tenant.notificar-pago') }}" class="btn-glossy" style="padding: 8px; display: inline-block; text-decoration: none; text-align: center;">Notificar Pago</a>
        </div>

        <div class="action-card glass-panel">
            <i class="fas fa-tools fa-3x" style="margin-bottom: 15px;"></i>
            <h4>Reportar Problema</h4>
            <a href="{{ route('tenant.reportes') }}" class="btn-glossy" style="padding: 8px; display: inline-block; text-decoration: none; text-align: center;">Crear Reporte</a>
        </div>
    </div>
@endsection
