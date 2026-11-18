<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/farmacias/farmaciaModel.js */
final class FarmaciaRepository extends BaseRepository
{
    protected const TABLE = 'Farmacia';
    protected const PK = 'idFarmacia';
    protected const COLUMNS = ['nombre_sede', 'direccion', 'telefono', 'ciudad', 'localidad'];
}
