<?php

declare(strict_types=1);

namespace PharmaGo\Core;

final class Router
{
    /** @var array<int,array{0:string,1:string,2:callable}> */
    private array $rutas = [];

    public function add(string $metodo, string $patron, callable $accion): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[A-Za-z0-9_-]+)', $patron) . '$#';
        $this->rutas[] = [$metodo, $regex, $accion];
    }

    public function dispatch(string $metodo, string $ruta): void
    {
        foreach ($this->rutas as [$m, $regex, $accion]) {
            if ($m === $metodo && preg_match($regex, $ruta, $coincide)) {
                $accion(...array_filter($coincide, 'is_string', ARRAY_FILTER_USE_KEY));
                return;
            }
        }
        http_response_code(404);
        echo 'No encontrado';
    }
}
