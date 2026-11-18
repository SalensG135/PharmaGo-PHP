<?php

declare(strict_types=1);

namespace PharmaGo\Core;

/** Port de la lógica de menú/permisos de dashboard.js y Controlador.permitido(). */
final class Modulos
{
    /** Orden del menú original (los módulos especiales se intercalan). */
    private const ORDEN = ['buscar', 'productos', 'inventario', 'farmacias', 'eps', 'reportes', 'usuarios', 'roles'];

    /** @return array<string,array<string,mixed>> */
    public static function todos(): array
    {
        $crud = require dirname(__DIR__, 2) . '/config/modules.php';
        $especiales = [
            'buscar' => ['nombre' => 'Buscar medicamento', 'pacienteVe' => true, 'ruta' => '/panel/buscar'],
            'reportes' => ['nombre' => 'Reportes', 'ruta' => '/panel/reportes'],
        ];
        $todos = $especiales + $crud;
        $ordenados = [];
        foreach (self::ORDEN as $slug) {
            $ordenados[$slug] = $todos[$slug];
        }
        return $ordenados;
    }

    public static function puedeVer(array $m): bool
    {
        if (!empty($m['soloAdmin']) && !Auth::isAdmin()) {
            return false;
        }
        return Auth::canEdit() || !empty($m['pacienteVe']);
    }

    public static function puedeEscribir(array $m): bool
    {
        return !empty($m['soloAdmin']) ? Auth::isAdmin() : Auth::canEdit();
    }

    /** @return array<string,array<string,mixed>> */
    public static function visibles(): array
    {
        return array_filter(self::todos(), [self::class, 'puedeVer']);
    }
}
