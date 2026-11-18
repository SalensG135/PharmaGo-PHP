<?php

declare(strict_types=1);

namespace PharmaGo\Core;

final class Text
{
    /** Equivale a quitarTildes() de utils.js. */
    public static function normalize(?string $texto): string
    {
        $d = \Normalizer::normalize((string) $texto, \Normalizer::FORM_D) ?: (string) $texto;
        return mb_strtolower(trim((string) preg_replace('/\p{Mn}/u', '', $d)));
    }
}
