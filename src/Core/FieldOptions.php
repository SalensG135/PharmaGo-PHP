<?php

declare(strict_types=1);

namespace PharmaGo\Core;

/** Port de Vista.opciones(): lista de [valor, texto] para un campo select. */
final class FieldOptions
{
    /**
     * @param array<string,mixed> $campo
     * @return array<int,array{0:int|string,1:string}>
     */
    public static function for(array $campo): array
    {
        if (isset($campo['opciones'])) {
            return $campo['opciones'];
        }
        $o = $campo['origen'];
        $repo = new $o['repo']();
        $filas = isset($o['soloActivos']) ? $repo->activos() : $repo->all();

        return array_map(static function (array $f) use ($o): array {
            $texto = $f[$o['texto']];
            if (!empty($o['extra'])) {
                $texto .= ' (' . $f[$o['extra']] . ')';
            }
            return [$f[$o['valor']], $texto];
        }, $filas);
    }
}
