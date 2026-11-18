<?php
/** @var array $d */
$tarjeta = static fn(string $t, int $v) => '<div class="tarjeta"><span>' . e($t) . '</span><strong>' . $v . '</strong></div>';
$tabla = static function (string $titulo, array $filas): void { ?>
    <h3><?= e($titulo) ?></h3>
    <div class="tabla-scroll"><table>
        <thead><tr><th>Sede</th><th>Medicamento</th><th>Lote</th><th>Vencimiento</th><th>Stock</th></tr></thead>
        <tbody>
        <?php foreach ($filas as $f): ?>
            <tr><td><?= e($f['nombre_sede']) ?></td><td><?= e($f['nombre_comercial']) ?></td><td><?= e($f['lote']) ?></td><td><?= e($f['fecha_vencimiento']) ?></td><td><?= (int) $f['cantidad'] ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
    <?php if (!$filas): ?><p class="vacio">Sin registros.</p><?php endif;
}; ?>
<h2>Reportes</h2>
<div class="tarjetas">
    <?= $tarjeta('Medicamentos', $d['medicamentos']) ?>
    <?= $tarjeta('Farmacias', $d['farmacias']) ?>
    <?= $tarjeta('Unidades en stock', $d['unidades']) ?>
    <?= $tarjeta('Lotes vencidos', count($d['vencidos'])) ?>
</div>
<?php $tabla('Lotes que vencen en 30 días', $d['porVencer']); $tabla('Lotes vencidos', $d['vencidos']); $tabla('Stock bajo (menos de 10 unidades)', $d['stockBajo']); ?>
