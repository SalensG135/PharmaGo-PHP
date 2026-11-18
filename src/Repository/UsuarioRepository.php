<?php

declare(strict_types=1);

namespace PharmaGo\Repository;

/** Origen: js/usuarios/usuarioModel.js + parte de js/auth/login.js y registro.js. Tabla real: personaa. */
final class UsuarioRepository extends BaseRepository
{
    protected const TABLE = 'personaa';
    protected const PK = 'idUsuario';
    protected const COLUMNS = [
        'tipo_documento', 'numero_documento', 'nombre', 'apellido',
        'correo', 'password', 'telefono', 'idRol', 'idEPS',
    ];

    /** Nunca se expone el hash en los listados. */
    public function all(): array
    {
        return $this->pdo()->query(
            'SELECT idUsuario, tipo_documento, numero_documento, nombre, apellido, correo, telefono, idRol, idEPS
             FROM personaa ORDER BY idUsuario'
        )->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public function findByCorreo(string $correo): ?array
    {
        $st = $this->pdo()->prepare(
            'SELECT p.*, r.nombre_rol FROM personaa p JOIN Rol r ON r.idRol = p.idRol WHERE p.correo = :c LIMIT 1'
        );
        $st->execute(['c' => $correo]);
        return $st->fetch() ?: null;
    }

    public function idRolPorNombre(string $nombre): ?int
    {
        $st = $this->pdo()->prepare('SELECT idRol FROM Rol WHERE nombre_rol = :n LIMIT 1');
        $st->execute(['n' => $nombre]);
        $id = $st->fetchColumn();
        return $id === false ? null : (int) $id;
    }
}
