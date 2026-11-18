<?php

declare(strict_types=1);

/**
 * Origen: js/services/datosIniciales.js. Solo desarrollo: php database/seed.php
 * Los códigos CUM y documentos son MARCADORES de ejemplo, no datos reales.
 */
if (PHP_SAPI !== 'cli') {
    exit('Solo por línea de comandos.');
}

$raiz = dirname(__DIR__);
require $raiz . '/src/Core/helpers.php';
spl_autoload_register(static function (string $c) use ($raiz): void {
    $f = $raiz . '/src/' . str_replace('\\', '/', substr($c, 9)) . '.php';
    if (str_starts_with($c, 'PharmaGo\\') && is_file($f)) {
        require $f;
    }
});

use PharmaGo\Core\{Database, Env};
use PharmaGo\Repository\{EpsRepository, FarmaciaRepository, InventarioRepository, MedicamentoRepository, RolRepository, UsuarioRepository};

Env::load($raiz . '/.env');
$pdo = Database::pdo();
if ((int) $pdo->query('SELECT COUNT(*) FROM Rol')->fetchColumn() > 0) {
    exit("La base ya tiene datos; no se cargó nada.\n");
}

$dia = static fn(int $n): string => (new DateTimeImmutable("$n days"))->format('Y-m-d');
$pdo->beginTransaction();

$rol = [];
foreach (['Paciente', 'Farmacéutico', 'Administrador', 'Auxiliar de farmacia', 'Auditor'] as $n) {
    $rol[$n] = (new RolRepository())->create(['nombre_rol' => $n]);
}
$eps = [];
foreach ([['Sanitas', 1], ['Nueva EPS', 1], ['Compensar', 1], ['Sura', 1], ['Famisanar', 0]] as [$n, $e]) {
    $eps[$n] = (new EpsRepository())->create(['nombre_eps' => $n, 'estado' => $e]);
}
$sede = [];
foreach ([
    ['Sede Chapinero', 'Calle 63 # 10-25', '6015550101', 'Chapinero'], ['Sede Suba', 'Carrera 91 # 146-30', '6015550102', 'Suba'],
    ['Sede Kennedy', 'Avenida Américas # 68-40', '6015550103', 'Kennedy'], ['Sede Usaquén', 'Carrera 7 # 120-15', '6015550104', 'Usaquén'],
    ['Sede Fontibón', 'Calle 17 # 100-50', '6015550105', 'Fontibón'],
] as [$n, $d, $t, $l]) {
    $sede[$n] = (new FarmaciaRepository())->create(['nombre_sede' => $n, 'direccion' => $d, 'telefono' => $t, 'ciudad' => 'Bogotá D.C.', 'localidad' => $l]);
}
$med = [];
foreach ([
    ['Dolex', 'Acetaminofén', 'Tabletas x 10', '500 mg', 'GSK'], ['Losartán', 'Losartán potásico', 'Tabletas x 30', '50 mg', 'Genfar'],
    ['Metformina', 'Metformina clorhidrato', 'Tabletas x 30', '850 mg', 'MK'], ['Amoxicilina', 'Amoxicilina', 'Cápsulas x 21', '500 mg', 'Genfar'],
    ['Omeprazol', 'Omeprazol', 'Cápsulas x 14', '20 mg', 'La Santé'],
] as $i => [$n, $p, $pr, $c, $l]) {
    $med[$n] = (new MedicamentoRepository())->create([
        'nombre_comercial' => $n, 'principio_activo' => $p, 'presentacion' => $pr, 'concentracion' => $c,
        'laboratorio' => $l, 'codigo_cum' => sprintf('EJEMPLO-%04d', $i + 1), 'requiere_receta' => 0, 'estado' => 1,
    ]);
}
foreach ([
    ['Sede Chapinero', 'Dolex', 'LOT-2026-A', 400, 120], ['Sede Suba', 'Losartán', 'LOT-2026-B', 300, 75],
    ['Sede Kennedy', 'Metformina', 'LOT-2025-C', 15, 40], ['Sede Usaquén', 'Amoxicilina', 'LOT-2025-D', -20, 30],
    ['Sede Fontibón', 'Omeprazol', 'LOT-2026-E', 500, 6], ['Sede Suba', 'Dolex', 'LOT-2026-F', 350, 60],
    ['Sede Kennedy', 'Dolex', 'LOT-2026-G', 280, 8], ['Sede Usaquén', 'Losartán', 'LOT-2026-H', 420, 45],
] as [$s, $m, $lote, $d, $q]) {
    (new InventarioRepository())->create(['idFarmacia' => $sede[$s], 'idMedicamento' => $med[$m], 'lote' => $lote, 'fecha_vencimiento' => $dia($d), 'cantidad' => $q]);
}

$u = new UsuarioRepository();
$doc = 1000000000;
foreach ([
    ['Paciente', 'Demo', 'paciente@pharmago.com', '1234', 'Paciente'], ['Farmacéutico', 'Demo', 'farmaceutico@pharmago.com', '1234', 'Farmacéutico'],
    ['Administrador', 'Demo', 'admin@pharmago.com', '1234', 'Administrador'],
    ['Carlos', 'Ramírez', 'carlos.ramirez@pharmago.com', '123456', 'Administrador'], ['Laura', 'Gómez', 'laura.gomez@pharmago.com', '123456', 'Farmacéutico'],
    ['Andrés', 'Torres', 'andres.torres@correo.com', '123456', 'Paciente'], ['María', 'Fernández', 'maria.fernandez@correo.com', '123456', 'Paciente'],
    ['Julián', 'Castro', 'julian.castro@correo.com', '123456', 'Paciente'],
] as [$n, $a, $c, $p, $r]) {
    $u->create([
        'tipo_documento' => 'CC', 'numero_documento' => (string) ++$doc, 'nombre' => $n, 'apellido' => $a,
        'correo' => $c, 'password' => password_hash($p, PASSWORD_DEFAULT), 'idRol' => $rol[$r], 'idEPS' => $eps['Sanitas'],
    ]);
}
$pdo->commit();
echo "Datos de ejemplo cargados. CAMBIE las contraseñas antes de publicar.\n";
