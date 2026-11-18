<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

use PDO;
use PDOException;
use PharmaGo\Core\Database;

/**
 * Reemplaza a modelo.js. Nombres de tabla/columna salen de constantes (lista blanca);
 * los valores siempre van como parámetros preparados.
 */
abstract class BaseRepository
{
    protected const TABLE = '';
    protected const PK = '';
    /** Columnas que se pueden escribir desde la aplicación. */
    protected const COLUMNS = [];

    protected function pdo(): PDO
    {
        return Database::pdo();
    }

    public function pk(): string
    {
        return static::PK;
    }

    /** @return array<int,array<string,mixed>> */
    public function all(): array
    {
        return $this->pdo()->query('SELECT * FROM `' . static::TABLE . '` ORDER BY `' . static::PK . '`')->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function find(int $id): ?array
    {
        $st = $this->pdo()->prepare('SELECT * FROM `' . static::TABLE . '` WHERE `' . static::PK . '` = :id');
        $st->execute(['id' => $id]);
        return $st->fetch() ?: null;
    }

    /** @param array<string,mixed> $data */
    public function create(array $data): int
    {
        $data = array_intersect_key($data, array_flip(static::COLUMNS));
        $cols = implode(',', array_map(static fn($c) => "`$c`", array_keys($data)));
        $params = implode(',', array_map(static fn($c) => ":$c", array_keys($data)));
        $this->pdo()->prepare('INSERT INTO `' . static::TABLE . "` ($cols) VALUES ($params)")->execute($data);
        return (int) $this->pdo()->lastInsertId();
    }

    /** @param array<string,mixed> $data */
    public function update(int $id, array $data): void
    {
        $data = array_intersect_key($data, array_flip(static::COLUMNS));
        if ($data === []) {
            return;
        }
        $set = implode(',', array_map(static fn($c) => "`$c` = :$c", array_keys($data)));
        $data['__id'] = $id;
        $this->pdo()->prepare('UPDATE `' . static::TABLE . "` SET $set WHERE `" . static::PK . '` = :__id')->execute($data);
    }

    public function delete(int $id): void
    {
        $this->pdo()->prepare('DELETE FROM `' . static::TABLE . '` WHERE `' . static::PK . '` = :id')->execute(['id' => $id]);
    }

    /**
     * Port de Modelo.duplicado(). Compara combinación de columnas (colación utf8mb4_unicode_ci: sin tildes ni mayúsculas).
     * @param string[] $columnas
     * @param array<string,mixed> $data
     */
    public function existe(array $columnas, array $data, ?int $excluirId = null): bool
    {
        $where = [];
        $params = [];
        foreach ($columnas as $c) {
            if (!in_array($c, static::COLUMNS, true)) {
                throw new \LogicException("Columna no permitida: $c");
            }
            $where[] = "`$c` = :$c";
            $params[$c] = $data[$c] ?? null;
        }
        $sql = 'SELECT 1 FROM `' . static::TABLE . '` WHERE ' . implode(' AND ', $where);
        if ($excluirId !== null) {
            $sql .= ' AND `' . static::PK . '` <> :__excluir';
            $params['__excluir'] = $excluirId;
        }
        $st = $this->pdo()->prepare($sql . ' LIMIT 1');
        $st->execute($params);
        return (bool) $st->fetchColumn();
    }

    public static function esViolacionFk(PDOException $e): bool
    {
        return ($e->errorInfo[1] ?? 0) === 1451 || ($e->errorInfo[1] ?? 0) === 1452;
    }

    public static function esDuplicado(PDOException $e): bool
    {
        return ($e->errorInfo[1] ?? 0) === 1062;
    }
}
