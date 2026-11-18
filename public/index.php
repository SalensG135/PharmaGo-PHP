<?php

declare(strict_types=1);

// Servidor embebido: php -S localhost:8000 -t public public/index.php
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . (string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

$raiz = dirname(__DIR__);
if (is_file($raiz . '/vendor/autoload.php')) {
    require $raiz . '/vendor/autoload.php';
} else {
    require $raiz . '/src/Core/helpers.php';
    spl_autoload_register(static function (string $clase) use ($raiz): void {
        if (str_starts_with($clase, 'PharmaGo\\')) {
            $f = $raiz . '/src/' . str_replace('\\', '/', substr($clase, 9)) . '.php';
            if (is_file($f)) {
                require $f;
            }
        }
    });
}

use PharmaGo\Controller\{AuthController, BuscarController, CrudController, ReporteController};
use PharmaGo\Core\{Auth, Env, Router, Session, View};

Env::load($raiz . '/.env');
Session::start();

$metodo = $_SERVER['REQUEST_METHOD'];
$ruta = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ?: '/';

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: DENY');
if ($ruta !== '/') {
    header("Content-Security-Policy: default-src 'self'; frame-src https://www.google.com; img-src 'self' data:; style-src 'self'; script-src 'self'");
}

if ($metodo === 'POST' && !Session::verifyCsrf($_POST['_csrf'] ?? null)) {
    http_response_code(419);
    exit('Sesión expirada o petición no válida. Recarga la página.');
}

$protegida = static function (callable $accion): callable {
    return static function (string ...$p) use ($accion): void {
        Auth::require();
        $accion(...$p);
    };
};

$r = new Router();
$r->add('GET', '/', static fn() => View::render('landing', [], null));
$r->add('GET', '/login', [new AuthController(), 'formLogin']);
$r->add('POST', '/login', [new AuthController(), 'login']);
$r->add('GET', '/registro', [new AuthController(), 'formRegistro']);
$r->add('POST', '/registro', [new AuthController(), 'registro']);
$r->add('POST', '/logout', [new AuthController(), 'logout']);

$r->add('GET', '/panel', $protegida(static function (): void {
    $primero = array_key_first(PharmaGo\Core\Modulos::visibles());
    $m = PharmaGo\Core\Modulos::visibles()[$primero];
    View::redirect($m['ruta'] ?? '/panel/' . $primero);
}));
$r->add('GET', '/panel/buscar', $protegida([new BuscarController(), 'index']));
$r->add('GET', '/panel/reportes', $protegida([new ReporteController(), 'index']));
$r->add('GET', '/panel/{modulo}', $protegida(static fn(string $modulo) => (new CrudController())->index($modulo)));
$r->add('POST', '/panel/{modulo}', $protegida(static fn(string $modulo) => (new CrudController())->guardar($modulo)));
$r->add('POST', '/panel/{modulo}/{id}/eliminar', $protegida(static fn(string $modulo, string $id) => (new CrudController())->eliminar($modulo, $id)));

$r->dispatch($metodo, $ruta);
