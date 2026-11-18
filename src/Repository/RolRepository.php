<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/roles/rolModel.js */
final class RolRepository extends BaseRepository
{
    protected const TABLE = 'Rol';
    protected const PK = 'idRol';
    protected const COLUMNS = ['nombre_rol'];
}
