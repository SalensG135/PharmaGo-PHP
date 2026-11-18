<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/inventario/inventarioModel.js (fecha_actualizacion la maneja la BD) */
final class InventarioRepository extends BaseRepository
{
    protected const TABLE = 'Inventario';
    protected const PK = 'idInventario';
    protected const COLUMNS = ['idFarmacia', 'idMedicamento', 'lote', 'fecha_vencimiento', 'cantidad'];
}
