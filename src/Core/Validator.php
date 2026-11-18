<?php

declare(strict_types=1);

namespace PharmaGo\Core;

/** Port de Vista.mensajeDe() / leerFormulario(): ahora la validación es obligatoria en el servidor. */
final class Validator
{
    /**
     * @param array<int,array<string,mixed>> $campos
     * @param array<string,mixed> $in
     * @return array{errors:array<string,string>,data:array<string,mixed>}
     */
    public static function validate(array $campos, array $in, bool $editando = false): array
    {
        $errors = [];
        $data = [];

        foreach ($campos as $c) {
            if (!empty($c['soloTabla'])) {
                continue;
            }
            $n = $c['nombre'];
            $tipo = $c['tipo'];

            if ($tipo === 'checkbox') {
                $data[$n] = isset($in[$n]) ? 1 : 0;
                continue;
            }

            $raw = is_string($in[$n] ?? null) ? $in[$n] : '';
            $v = $tipo === 'password' ? $raw : trim($raw);

            if ($v === '') {
                $obligatorio = !empty($c['requerido']) && !($editando && !empty($c['opcionalAlEditar']));
                if ($obligatorio) {
                    $errors[$n] = $tipo === 'select' ? 'Seleccione una opción.' : 'Complete este campo.';
                } elseif (!$editando || empty($c['opcionalAlEditar'])) {
                    $data[$n] = null;
                }
                continue;
            }

            $largo = mb_strlen($v);
            if (!empty($c['minimo']) && $largo < $c['minimo']) {
                $errors[$n] = 'Escriba al menos ' . $c['minimo'] . ' caracteres.';
            } elseif (!empty($c['max']) && $largo > $c['max']) {
                $errors[$n] = 'Máximo ' . $c['max'] . ' caracteres.';
            } elseif ($tipo === 'email' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
                $errors[$n] = 'Escriba un correo válido, por ejemplo nombre@correo.com.';
            } elseif (!empty($c['patron']) && !preg_match('~' . $c['patron'] . '~u', $v)) {
                $errors[$n] = $c['mensajePatron'] ?? 'Formato no válido.';
            } elseif ($tipo === 'date' && !self::fechaValida($v)) {
                $errors[$n] = 'Fecha no válida.';
            } elseif ($tipo === 'number' || !empty($c['numero'])) {
                $int = filter_var($v, FILTER_VALIDATE_INT);
                if ($int === false) {
                    $errors[$n] = 'Escriba un número entero.';
                } elseif (isset($c['min']) && $int < $c['min']) {
                    $errors[$n] = 'El valor mínimo es ' . $c['min'] . '.';
                }
            }

            if ($tipo === 'select' && !isset($errors[$n])) {
                $validas = array_map(static fn($o) => (string) $o[0], FieldOptions::for($c));
                if (!in_array($v, $validas, true)) {
                    $errors[$n] = 'Seleccione una opción válida.';
                }
            }

            if (!isset($errors[$n])) {
                $data[$n] = (!empty($c['numero']) || $tipo === 'number') ? (int) $v : $v;
            }
        }

        return ['errors' => $errors, 'data' => $data];
    }

    private static function fechaValida(string $v): bool
    {
        $d = \DateTimeImmutable::createFromFormat('!Y-m-d', $v);
        return $d !== false && $d->format('Y-m-d') === $v;
    }
}
