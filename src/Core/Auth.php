<?php

declare(strict_types=1);

namespace PharmaGo\Core;

use PharmaGo\Repository\UsuarioRepository;

/** Reemplaza a login.js + sesion.js: la autenticación y los permisos viven en el servidor. */
final class Auth
{
    public const ROLES_EDITORES = ['farmaceutico', 'administrador'];
    private const HASH_FALSO = '$2y$12$usesomesillystringforsaltuOzqXQ0h0xv3Qh0n6W1ZtP3m4Y5R8k2G';

    /** @return array{id:int,nombre:string,correo:string,rol:string}|null */
    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isAdmin(): bool
    {
        return self::user() !== null && Text::normalize(self::user()['rol']) === 'administrador';
    }

    /** Solo Farmacéutico y Administrador editan (Auxiliar/Auditor quedan en consulta). */
    public static function canEdit(): bool
    {
        return self::user() !== null && in_array(Text::normalize(self::user()['rol']), self::ROLES_EDITORES, true);
    }

    public static function require(): void
    {
        if (!self::check()) {
            View::redirect('/login');
        }
    }

    /** @return string|null mensaje de error, o null si el acceso fue correcto */
    public static function attempt(string $correo, string $clave, string $rolElegido): ?string
    {
        $repo = new UsuarioRepository();
        $u = $repo->findByCorreo($correo);
        $hash = $u['password'] ?? self::HASH_FALSO;
        $ok = password_verify($clave, $hash) && $u !== null;

        if (!$ok) {
            return 'Correo, contraseña o rol incorrecto.';
        }
        if (Text::normalize($u['nombre_rol']) !== Text::normalize($rolElegido)) {
            return 'Esta cuenta no tiene el rol ' . $rolElegido . '.';
        }
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $repo->update((int) $u['idUsuario'], ['password' => password_hash($clave, PASSWORD_DEFAULT)]);
        }

        session_regenerate_id(true);
        Session::set('user', [
            'id' => (int) $u['idUsuario'],
            'nombre' => $u['nombre'],
            'correo' => mb_strtolower($u['correo']),
            'rol' => $u['nombre_rol'],
        ]);
        return null;
    }

    public static function logout(): void
    {
        Session::destroy();
    }
}
