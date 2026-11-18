<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/reportes/reporteModel.js (los filtros ahora se ejecutan en SQL). */
final class ReporteRepository extends BaseRepository
{
    protected const TABLE = 'Inventario';
    protected const PK = 'idInventario';

    /** @return array<string,mixed> */
    public function resumen(): array
    {
        $pdo = $this->pdo();
        $lotes = static fn(string $where): string =>
            "SELECT F.nombre_sede, M.nombre_comercial, I.lote, I.fecha_vencimiento, I.cantidad
             FROM Inventario I
             JOIN Farmacia F ON F.idFarmacia = I.idFarmacia
             JOIN Medicamento M ON M.idMedicamento = I.idMedicamento
             WHERE $where ORDER BY I.fecha_vencimiento";

        return [
            'medicamentos' => (int) $pdo->query('SELECT COUNT(*) FROM Medicamento')->fetchColumn(),
            'farmacias' => (int) $pdo->query('SELECT COUNT(*) FROM Farmacia')->fetchColumn(),
            'unidades' => (int) $pdo->query('SELECT COALESCE(SUM(cantidad),0) FROM Inventario')->fetchColumn(),
            'vencidos' => $pdo->query($lotes('I.fecha_vencimiento < CURDATE()'))->fetchAll(),
            'porVencer' => $pdo->query($lotes('I.fecha_vencimiento BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)'))->fetchAll(),
            'stockBajo' => $pdo->query($lotes('I.cantidad < 10'))->fetchAll(),
        ];
    }
}
