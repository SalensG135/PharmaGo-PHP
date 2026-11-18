<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/disponibilidad/disponibilidadModel.js. Usa la vista v_buscar_medicamento_sede (ver migración 001). */
final class DisponibilidadRepository extends BaseRepository
{
    protected const TABLE = 'v_buscar_medicamento_sede';
    protected const PK = 'idInventario';

    /** @return array<int,array<string,mixed>> */
    public function buscar(string $texto): array
    {
        $patron = '%' . addcslashes($texto, '%_\\') . '%';
        $st = $this->pdo()->prepare(
            'SELECT * FROM v_buscar_medicamento_sede
             WHERE Medicamento LIKE :q OR PrincipioActivo LIKE :q
             ORDER BY StockDisponible DESC'
        );
        $st->execute(['q' => $patron]);
        return $st->fetchAll();
    }

    /** @param array<string,mixed> $f */
    public static function direccionCompleta(array $f): string
    {
        return "{$f['Direccion']}, {$f['Localidad']}, {$f['Ciudad']}, Colombia";
    }

    /** @param array<string,mixed> $f */
    public static function urlMapa(array $f): string
    {
        return 'https://www.google.com/maps?q=' . rawurlencode(self::direccionCompleta($f)) . '&output=embed';
    }

    /** @param array<string,mixed> $f */
    public static function urlGoogleMaps(array $f): string
    {
        return 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode(self::direccionCompleta($f));
    }
}
