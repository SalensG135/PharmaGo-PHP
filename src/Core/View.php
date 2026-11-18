<?php

declare(strict_types=1);

namespace PharmaGo\Core;

final class View
{
    /** @param array<string,mixed> $datos */
    public static function render(string $vista, array $datos = [], ?string $layout = 'layout'): void
    {
        $base = dirname(__DIR__, 2) . '/views/';
        extract($datos, EXTR_SKIP);
        ob_start();
        require $base . $vista . '.php';
        $contenido = (string) ob_get_clean();
        if ($layout === null) {
            echo $contenido;
            return;
        }
        require $base . $layout . '.php';
    }

    public static function redirect(string $ruta): never
    {
        header('Location: ' . $ruta, true, 303);
        exit;
    }
}
