<?php

namespace App\Support;

/**
 * Catálogo de presets para la pestaña "Personalización" de Ajustes.
 * Fuente única de verdad tanto para la validación del formulario
 * (DashboardController::updatePersonalizacion) como para el render de las
 * muestras y la aplicación del tema (layouts/panel.blade.php).
 */
class Personalizacion
{
    public const FONDOS = [
        'clasico' => [
            'label' => 'Clásico',
            'css' => "url('/imagen/liam_10.webp') no-repeat center center fixed",
            'swatch' => "url('/imagen/liam_10.webp') center center / cover",
        ],
        'bosque' => [
            'label' => 'Bosque',
            'css' => 'linear-gradient(135deg, #0f2c1f 0%, #1e8d47 100%)',
            'swatch' => 'linear-gradient(135deg, #0f2c1f 0%, #1e8d47 100%)',
        ],
        'oceano' => [
            'label' => 'Océano',
            'css' => 'linear-gradient(135deg, #0f1f2c 0%, #1e6d8d 100%)',
            'swatch' => 'linear-gradient(135deg, #0f1f2c 0%, #1e6d8d 100%)',
        ],
        'medianoche' => [
            'label' => 'Medianoche',
            'css' => 'linear-gradient(135deg, #111827 0%, #1f2937 100%)',
            'swatch' => 'linear-gradient(135deg, #111827 0%, #1f2937 100%)',
        ],
        'atardecer' => [
            'label' => 'Atardecer',
            'css' => 'linear-gradient(135deg, #451a03 0%, #ea580c 100%)',
            'swatch' => 'linear-gradient(135deg, #451a03 0%, #ea580c 100%)',
        ],
        'lavanda' => [
            'label' => 'Lavanda',
            'css' => 'linear-gradient(135deg, #1e1b4b 0%, #a21caf 100%)',
            'swatch' => 'linear-gradient(135deg, #1e1b4b 0%, #a21caf 100%)',
        ],
        'grafito' => [
            'label' => 'Grafito',
            'css' => 'linear-gradient(135deg, #18181b 0%, #3f3f46 100%)',
            'swatch' => 'linear-gradient(135deg, #18181b 0%, #3f3f46 100%)',
        ],
        'vino' => [
            'label' => 'Vino',
            'css' => 'linear-gradient(135deg, #450a0a 0%, #991b1b 100%)',
            'swatch' => 'linear-gradient(135deg, #450a0a 0%, #991b1b 100%)',
        ],
        'esmeralda' => [
            'label' => 'Esmeralda',
            'css' => 'linear-gradient(135deg, #022c22 0%, #059669 100%)',
            'swatch' => 'linear-gradient(135deg, #022c22 0%, #059669 100%)',
        ],
        'cielo' => [
            'label' => 'Cielo',
            'css' => 'linear-gradient(135deg, #082f49 0%, #0ea5e9 100%)',
            'swatch' => 'linear-gradient(135deg, #082f49 0%, #0ea5e9 100%)',
        ],
        'arena' => [
            'label' => 'Arena',
            'css' => 'linear-gradient(135deg, #422006 0%, #ca8a04 100%)',
            'swatch' => 'linear-gradient(135deg, #422006 0%, #ca8a04 100%)',
        ],
        'nocturno' => [
            'label' => 'Nocturno',
            'css' => 'linear-gradient(135deg, #0f0c29 0%, #4338ca 100%)',
            'swatch' => 'linear-gradient(135deg, #0f0c29 0%, #4338ca 100%)',
        ],
    ];

    public const COLORES = [
        // 'rgb' = versión "r, g, b" de 'base' (para rgba() en hovers tipo sidebar).
        // 'glassRgb' = tinte oscuro de esa familia de color para el fondo de
        // .glass-panel (tarjetas, tablas, formularios). 'light' = variante
        // clara para textos/íconos de acción sobre fondo oscuro.
        'verde' => ['label' => 'Verde', 'base' => '#1e8d47', 'dark' => '#14532d', 'hover' => '#22c55e', 'hoverDark' => '#16a34a', 'rgb' => '30, 141, 71', 'glassRgb' => '18, 49, 38', 'light' => '#86EFAC'],
        'azul' => ['label' => 'Azul', 'base' => '#1e6d8d', 'dark' => '#14374a', 'hover' => '#2596c5', 'hoverDark' => '#1a6f94', 'rgb' => '30, 109, 141', 'glassRgb' => '15, 40, 56', 'light' => '#7dd3fc'],
        'morado' => ['label' => 'Morado', 'base' => '#6d28d9', 'dark' => '#4c1d95', 'hover' => '#8b5cf6', 'hoverDark' => '#7c3aed', 'rgb' => '109, 40, 217', 'glassRgb' => '36, 24, 58', 'light' => '#c4b5fd'],
        'ambar' => ['label' => 'Ámbar', 'base' => '#d97706', 'dark' => '#92400e', 'hover' => '#f59e0b', 'hoverDark' => '#d97706', 'rgb' => '217, 119, 6', 'glassRgb' => '54, 38, 12', 'light' => '#fcd34d'],
        'rosa' => ['label' => 'Rosa', 'base' => '#db2777', 'dark' => '#831843', 'hover' => '#ec4899', 'hoverDark' => '#db2777', 'rgb' => '219, 39, 119', 'glassRgb' => '54, 15, 35', 'light' => '#f9a8d4'],
        'turquesa' => ['label' => 'Turquesa', 'base' => '#0d9488', 'dark' => '#134e4a', 'hover' => '#14b8a6', 'hoverDark' => '#0f766e', 'rgb' => '13, 148, 136', 'glassRgb' => '10, 46, 42', 'light' => '#5eead4'],
        'rojo' => ['label' => 'Rojo', 'base' => '#dc2626', 'dark' => '#7f1d1d', 'hover' => '#ef4444', 'hoverDark' => '#dc2626', 'rgb' => '220, 38, 38', 'glassRgb' => '50, 15, 15', 'light' => '#fca5a5'],
        'indigo' => ['label' => 'Índigo', 'base' => '#4f46e5', 'dark' => '#312e81', 'hover' => '#6366f1', 'hoverDark' => '#4f46e5', 'rgb' => '79, 70, 229', 'glassRgb' => '28, 25, 58', 'light' => '#a5b4fc'],
        'cian' => ['label' => 'Cian', 'base' => '#0891b2', 'dark' => '#164e63', 'hover' => '#06b6d4', 'hoverDark' => '#0891b2', 'rgb' => '8, 145, 178', 'glassRgb' => '10, 40, 48', 'light' => '#67e8f9'],
        'lima' => ['label' => 'Lima', 'base' => '#65a30d', 'dark' => '#365314', 'hover' => '#84cc16', 'hoverDark' => '#65a30d', 'rgb' => '101, 163, 13', 'glassRgb' => '28, 40, 12', 'light' => '#bef264'],
        'naranja' => ['label' => 'Naranja', 'base' => '#ea580c', 'dark' => '#7c2d12', 'hover' => '#f97316', 'hoverDark' => '#ea580c', 'rgb' => '234, 88, 12', 'glassRgb' => '50, 26, 14', 'light' => '#fdba74'],
        'pizarra' => ['label' => 'Pizarra', 'base' => '#475569', 'dark' => '#1e293b', 'hover' => '#64748b', 'hoverDark' => '#475569', 'rgb' => '71, 85, 105', 'glassRgb' => '24, 32, 44', 'light' => '#cbd5e1'],
    ];

    public const FUENTES = [
        'segoe' => ['label' => 'Predeterminada', 'css' => "'Segoe UI', Tahoma, Geneva, Verdana, sans-serif", 'sample' => 'Aa'],
        'poppins' => ['label' => 'Moderna', 'css' => "'Poppins', 'Segoe UI', sans-serif", 'sample' => 'Aa'],
        'merriweather' => ['label' => 'Elegante', 'css' => "'Merriweather', Georgia, serif", 'sample' => 'Aa'],
        'nunito' => ['label' => 'Redondeada', 'css' => "'Nunito', 'Segoe UI', sans-serif", 'sample' => 'Aa'],
        'firacode' => ['label' => 'Monoespaciada', 'css' => "'Fira Code', 'Courier New', monospace", 'sample' => 'Aa'],
        'roboto' => ['label' => 'Clásica Web', 'css' => "'Roboto', 'Segoe UI', sans-serif", 'sample' => 'Aa'],
        'lato' => ['label' => 'Suave', 'css' => "'Lato', 'Segoe UI', sans-serif", 'sample' => 'Aa'],
        'oswald' => ['label' => 'Condensada', 'css' => "'Oswald', 'Segoe UI', sans-serif", 'sample' => 'Aa'],
        'playfair' => ['label' => 'Editorial', 'css' => "'Playfair Display', Georgia, serif", 'sample' => 'Aa'],
        'montserrat' => ['label' => 'Geométrica', 'css' => "'Montserrat', 'Segoe UI', sans-serif", 'sample' => 'Aa'],
        'comicneue' => ['label' => 'Divertida', 'css' => "'Comic Neue', 'Comic Sans MS', cursive", 'sample' => 'Aa'],
    ];

    public const BOTONES = [
        'glossy' => ['label' => 'Glossy'],
        'plano' => ['label' => 'Plano'],
        'contorno' => ['label' => 'Contorno'],
        'neon' => ['label' => 'Neón'],
        'sombra' => ['label' => 'Elevado'],
        'pastilla' => ['label' => 'Píldora'],
        'cuadrado' => ['label' => 'Cuadrado'],
        'cristal' => ['label' => 'Cristal'],
        'relieve' => ['label' => 'Relieve'],
        'punteado' => ['label' => 'Punteado'],
    ];

    public static function fondo(?string $clave): array
    {
        return self::FONDOS[$clave] ?? self::FONDOS['clasico'];
    }

    public static function color(?string $clave): array
    {
        return self::COLORES[$clave] ?? self::COLORES['verde'];
    }

    public static function fuente(?string $clave): array
    {
        return self::FUENTES[$clave] ?? self::FUENTES['segoe'];
    }

    public static function boton(?string $clave): string
    {
        return array_key_exists($clave ?? '', self::BOTONES) ? $clave : 'glossy';
    }
}
