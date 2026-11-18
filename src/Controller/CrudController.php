<?php

declare(strict_types=1);

namespace PharmaGo\Controller;

use PDOException;
use PharmaGo\Core\{Auth, FieldOptions, Modulos, Session, Text, Validator, View};
use PharmaGo\Repository\BaseRepository;

/** Reemplaza a core/controlador.js + core/vista.js (formulario, tabla, guardar, eliminar). */
final class CrudController
{
    /** @return array{0:array<string,mixed>,1:BaseRepository} */
    private function cargar(string $slug): array
    {
        $m = Modulos::todos()[$slug] ?? null;
        if ($m === null || !isset($m['repo']) || !Modulos::puedeVer($m)) {
            http_response_code(404);
            exit('No encontrado');
        }
        return [$m, new $m['repo']()];
    }

    public function index(string $slug): void
    {
        [$m, $repo] = $this->cargar($slug);
        $editId = isset($_GET['editar']) ? (int) $_GET['editar'] : null;
        $valores = $editId && Modulos::puedeEscribir($m) ? ($repo->find($editId) ?? []) : [];
        unset($valores['password']);
        $this->mostrar($slug, $m, $repo, [], $valores, $editId);
    }

    public function guardar(string $slug): void
    {
        [$m, $repo] = $this->cargar($slug);
        if (!Modulos::puedeEscribir($m)) {
            http_response_code(403);
            exit('Sin permiso');
        }

        $id = isset($_POST['id']) && $_POST['id'] !== '' ? (int) $_POST['id'] : null;
        $res = Validator::validate($m['campos'], $_POST, $id !== null);
        $errores = $res['errors'];
        $datos = $res['data'];

        if (!$errores) {
            foreach ($m['unicos'] ?? [] as [$cols, $campoError, $mensaje]) {
                if ($repo->existe($cols, $datos, $id)) {
                    $errores[$campoError] = $mensaje;
                    break;
                }
            }
        }

        if (!$errores) {
            foreach ($m['hash'] ?? [] as $campo) {
                if (($datos[$campo] ?? '') !== '' && isset($datos[$campo])) {
                    $datos[$campo] = password_hash((string) $datos[$campo], PASSWORD_DEFAULT);
                } else {
                    unset($datos[$campo]);
                }
            }
            try {
                $id ? $repo->update($id, $datos) : $repo->create($datos);
            } catch (PDOException $e) {
                if (BaseRepository::esDuplicado($e)) {
                    $errores[array_key_first($datos)] = 'Ya existe un registro con esos datos.';
                } elseif (BaseRepository::esViolacionFk($e)) {
                    $errores[array_key_first($datos)] = 'Referencia no válida.';
                } else {
                    throw $e;
                }
            }
        }

        if ($errores) {
            $_POST['password'] = '';
            $this->mostrar($slug, $m, $repo, $errores, $_POST, $id);
            return;
        }

        Session::flash('exito', $id ? 'Cambios guardados correctamente.' : 'Registro guardado correctamente.');
        View::redirect('/panel/' . $slug);
    }

    public function eliminar(string $slug, string $id): void
    {
        [$m, $repo] = $this->cargar($slug);
        if (!Modulos::puedeEscribir($m)) {
            http_response_code(403);
            exit('Sin permiso');
        }
        $id = (int) $id;
        $item = $repo->find($id);

        if ($item && !empty($m['noEliminarPropio']) && $id === Auth::user()['id']) {
            Session::flash('error', 'No puedes eliminar la cuenta con la que iniciaste sesión.');
        } elseif ($item) {
            try {
                $repo->delete($id);
                Session::flash('exito', 'Registro eliminado.');
            } catch (PDOException $e) {
                if (!BaseRepository::esViolacionFk($e)) {
                    throw $e;
                }
                Session::flash('error', 'No se puede eliminar: hay registros que dependen de este.');
            }
        }
        View::redirect('/panel/' . $slug);
    }

    /**
     * @param array<string,mixed> $m
     * @param array<string,string> $errores
     * @param array<string,mixed> $valores
     */
    private function mostrar(string $slug, array $m, BaseRepository $repo, array $errores, array $valores, ?int $editId): void
    {
        $puedeEscribir = Modulos::puedeEscribir($m);
        $mapas = [];
        foreach ($m['campos'] as $c) {
            if ($c['tipo'] === 'select') {
                foreach (FieldOptions::for($c) as [$v, $t]) {
                    $mapas[$c['nombre']][(string) $v] = $t;
                }
            }
        }

        $columnas = array_values(array_filter($m['campos'], static fn($c) => $c['tipo'] !== 'password'));
        $q = Text::normalize((string) ($_GET['q'] ?? ''));
        $filas = [];
        foreach ($repo->all() as $fila) {
            $celdas = [];
            foreach ($columnas as $c) {
                $v = $fila[$c['nombre']] ?? null;
                $celdas[] = match (true) {
                    $c['tipo'] === 'select' => $mapas[$c['nombre']][(string) $v] ?? '-',
                    $c['tipo'] === 'checkbox' => $v ? 'Sí' : 'No',
                    default => ($v === null || $v === '') ? '-' : (string) $v,
                };
            }
            if ($q === '' || str_contains(Text::normalize(implode(' ', $celdas)), $q)) {
                $filas[] = ['id' => $fila[$repo->pk()], 'celdas' => $celdas, 'raw' => $fila];
            }
        }

        View::render('crud', compact('slug', 'm', 'puedeEscribir', 'columnas', 'filas', 'errores', 'valores', 'editId') + ['q' => $_GET['q'] ?? '']);
    }
}
