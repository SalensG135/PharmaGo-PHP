<?php

declare(strict_types=1);

namespace PharmaGo\Controller;

use PDOException;
use PharmaGo\Core\{Auth, Session, Validator, View};
use PharmaGo\Repository\{BaseRepository, EpsRepository, UsuarioRepository};

/** Reemplaza a js/auth/login.js, registro.js y sesion.js. */
final class AuthController
{
    private const MAX_INTENTOS = 5;
    private const BLOQUEO_SEG = 60;

    public function formLogin(): void
    {
        if (Auth::check()) {
            View::redirect('/panel');
        }
        View::render('login', ['error' => null, 'correo' => '', 'rol' => 'Paciente'], null);
    }

    public function login(): void
    {
        $correo = trim((string) ($_POST['correo'] ?? ''));
        $clave = (string) ($_POST['password'] ?? '');
        $rol = (string) ($_POST['rol'] ?? 'Paciente');
        $error = null;

        if (Session::get('bloqueado_hasta', 0) > time()) {
            $error = 'Demasiados intentos. Espera un minuto.';
        } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $error = 'Escribe un correo válido, por ejemplo nombre@correo.com.';
        } elseif ($clave === '') {
            $error = 'La contraseña es obligatoria.';
        } else {
            $error = Auth::attempt($correo, $clave, $rol);
        }

        if ($error === null) {
            Session::set('intentos', 0);
            View::redirect('/panel');
        }

        $intentos = (int) Session::get('intentos', 0) + 1;
        Session::set('intentos', $intentos);
        if ($intentos >= self::MAX_INTENTOS) {
            Session::set('bloqueado_hasta', time() + self::BLOQUEO_SEG);
            Session::set('intentos', 0);
        }
        View::render('login', ['error' => $error, 'correo' => $correo, 'rol' => $rol], null);
    }

    public function logout(): void
    {
        Auth::logout();
        View::redirect('/login');
    }

    /** @return array<int,array<string,mixed>> */
    private function camposRegistro(): array
    {
        $sl = '^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ\' ]{2,50}$';
        return [
            ['nombre' => 'tipo_documento', 'etiqueta' => 'Tipo de documento', 'tipo' => 'select', 'requerido' => true,
                'opciones' => [['CC', 'Cédula de ciudadanía'], ['CE', 'Cédula de extranjería'], ['PA', 'Pasaporte']]],
            ['nombre' => 'numero_documento', 'etiqueta' => 'Número de documento', 'tipo' => 'text', 'requerido' => true, 'max' => 20,
                'patron' => '^[A-Za-z0-9]{5,20}$', 'mensajePatron' => 'Use de 5 a 20 letras o números.'],
            ['nombre' => 'nombre', 'etiqueta' => 'Nombres', 'tipo' => 'text', 'requerido' => true, 'max' => 50, 'patron' => $sl, 'mensajePatron' => 'Solo letras, mínimo 2 caracteres.'],
            ['nombre' => 'apellido', 'etiqueta' => 'Apellidos', 'tipo' => 'text', 'requerido' => true, 'max' => 50, 'patron' => $sl, 'mensajePatron' => 'Solo letras, mínimo 2 caracteres.'],
            ['nombre' => 'correo', 'etiqueta' => 'Correo', 'tipo' => 'email', 'requerido' => true, 'max' => 100],
            ['nombre' => 'password', 'etiqueta' => 'Contraseña', 'tipo' => 'password', 'requerido' => true, 'minimo' => 6, 'max' => 72],
            ['nombre' => 'idEPS', 'etiqueta' => 'EPS', 'tipo' => 'select', 'requerido' => true, 'numero' => true,
                'origen' => ['repo' => EpsRepository::class, 'valor' => 'idEPS', 'texto' => 'nombre_eps', 'soloActivos' => true]],
        ];
    }

    public function formRegistro(): void
    {
        View::render('registro', ['errores' => [], 'v' => [], 'campos' => $this->camposRegistro()], null);
    }

    public function registro(): void
    {
        $campos = $this->camposRegistro();
        $res = Validator::validate($campos, $_POST);
        $errores = $res['errors'];
        $d = $res['data'];
        $repo = new UsuarioRepository();

        if (!isset($errores['password']) && ($_POST['confirmar'] ?? '') !== ($_POST['password'] ?? '')) {
            $errores['confirmar'] = 'Las contraseñas no coinciden.';
        }
        if (empty($_POST['acepto'])) {
            $errores['acepto'] = 'Debe aceptar para continuar.';
        }
        if (!$errores) {
            $d['correo'] = mb_strtolower($d['correo']);
            if ($repo->existe(['correo'], $d)) {
                $errores['correo'] = 'Este correo ya está registrado.';
            } elseif ($repo->existe(['numero_documento'], $d)) {
                $errores['numero_documento'] = 'Este documento ya está registrado.';
            }
        }
        $idRol = $errores ? null : $repo->idRolPorNombre('Paciente');
        if (!$errores && $idRol === null) {
            $errores['correo'] = 'El registro no está disponible: falta el rol Paciente.';
        }

        if (!$errores) {
            try {
                $repo->create(['password' => password_hash($d['password'], PASSWORD_DEFAULT), 'idRol' => $idRol] + $d);
                Session::flash('exito', 'Cuenta creada con éxito. Ya puedes iniciar sesión.');
                View::redirect('/login');
            } catch (PDOException $e) {
                if (!BaseRepository::esDuplicado($e)) {
                    throw $e;
                }
                $errores['correo'] = 'Este correo o documento ya está registrado.';
            }
        }
        unset($_POST['password'], $_POST['confirmar']);
        View::render('registro', ['errores' => $errores, 'v' => $_POST, 'campos' => $campos], null);
    }
}
