<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/productos/productoModel.js */
final class MedicamentoRepository extends BaseRepository
{
    protected const TABLE = 'Medicamento';
    protected const PK = 'idMedicamento';
    protected const COLUMNS = ['nombre_comercial', 'principio_activo', 'presentacion', 'concentracion', 'laboratorio', 'codigo_cum', 'registro_invima', 'requiere_receta', 'estado'];
}
