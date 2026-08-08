@props(['texto'])

{{--
    Tooltip reutilizable, sin JS: el CSS de .tooltip-wrap en public/css/style.css
    muestra $texto en un globo al hacer hover sobre lo que se le pase como slot.
    Uso: <x-tooltip texto="Registrar pago"><a href="...">...</a></x-tooltip>
--}}
<span {{ $attributes->merge(['class' => 'tooltip-wrap']) }} data-tooltip="{{ $texto }}">
    {{ $slot }}
</span>
