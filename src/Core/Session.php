<?php

declare(strict_types=1);

namespace PharmaGo\Core;

/** Reemplaza a sesion.js (sessionStorage, falsificable desde DevTools) por sesiones del servidor. */
final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'httponly' => true,
                'samesite' => 'Lax',
                'secure' => !empty($_SERVER['HTTPS']),
            ]);
            session_start();
        }
    }

    public static function get(string $k, mixed $default = null): mixed
    {
        return $_SESSION[$k] ?? $default;
    }

    public static function set(string $k, mixed $v): void
    {
        $_SESSION[$k] = $v;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function flash(string $tipo, string $mensaje): void
    {
        $_SESSION['_flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
    }

    /** @return array{tipo:string,mensaje:string}|null */
    public static function pullFlash(): ?array
    {
        $f = $_SESSION['_flash'] ?? null;
        unset($_SESSION['_flash']);
        return $f;
    }

    public static function csrf(): string
    {
        return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function verifyCsrf(?string $token): bool
    {
        return is_string($token) && hash_equals(self::csrf(), $token);
    }
}
