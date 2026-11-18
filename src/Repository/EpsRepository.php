<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/eps/epsModel.js */
final class EpsRepository extends BaseRepository
{
    protected const TABLE = 'EPS';
    protected const PK = 'idEPS';
    protected const COLUMNS = ['nombre_eps', 'estado'];

    /** @return array<int,array<string,mixed>> */
    public function activos(): array
    {
        return $this->pdo()->query('SELECT * FROM EPS WHERE estado = 1 ORDER BY nombre_eps')->fetchAll();
    }
}
