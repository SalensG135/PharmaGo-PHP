<?php
use PharmaGo\Core\{FieldOptions, Session};
/** @var array $m @var string $slug @var bool $puedeEscribir @var array $columnas @var array $filas @var array $errores @var array $valores @var ?int $editId @var string $q */
?>
<h2><?= e($m['titulo']) ?></h2>

<?php if (!$puedeEscribir): ?>
    <p class="aviso">Tu rol es de solo consulta. Puedes buscar y ver registros, pero no crear, editar ni eliminar.</p>
<?php else: ?>
    <form id="formulario" method="post" action="/panel/<?= e($slug) ?>" novalidate>
        <input type="hidden" name="_csrf" value="<?= e(Session::csrf()) ?>">
        <input type="hidden" name="id" value="<?= e($editId) ?>">
        <div class="form-grid">
        <?php foreach ($m['campos'] as $c): if (!empty($c['soloTabla'])) { continue; }
            $n = $c['nombre'];
            $val = $valores[$n] ?? ($editId ? '' : ($c['valor'] ?? ''));
            $attrs = '';
            if (!empty($c['max'])) { $attrs .= ' maxlength="' . (int) $c['max'] . '"'; }
            if (!empty($c['minimo'])) { $attrs .= ' minlength="' . (int) $c['minimo'] . '"'; }
            if (isset($c['min'])) { $attrs .= ' min="' . (int) $c['min'] . '"'; }
            if (!empty($c['patron'])) { $attrs .= ' pattern="' . e($c['patron']) . '"'; }
            if (!empty($c['requerido']) && empty($c['opcionalAlEditar'])) { $attrs .= ' required'; }
        ?>
            <div class="form-group">
                <label for="<?= e($n) ?>"><?= e($c['etiqueta']) ?><?= !empty($c['requerido']) ? ' *' : '' ?></label>
                <?php if ($c['tipo'] === 'select'): ?>
                    <select id="<?= e($n) ?>" name="<?= e($n) ?>"<?= $attrs ?>>
                        <option value="">-- Seleccionar --</option>
                        <?php foreach (FieldOptions::for($c) as [$ov, $ot]): ?>
                            <option value="<?= e($ov) ?>" <?= (string) $ov === (string) $val ? 'selected' : '' ?>><?= e($ot) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php elseif ($c['tipo'] === 'checkbox'): ?>
                    <input type="checkbox" id="<?= e($n) ?>" name="<?= e($n) ?>" value="1" <?= $val ? 'checked' : '' ?>>
                <?php else: ?>
                    <input type="<?= e($c['tipo']) ?>" id="<?= e($n) ?>" name="<?= e($n) ?>"<?= $attrs ?>
                           value="<?= $c['tipo'] === 'password' ? '' : e($val) ?>"
                           <?= !empty($c['ejemplo']) ? 'placeholder="' . e($c['ejemplo']) . '"' : '' ?>
                           <?= $c['tipo'] === 'password' ? 'autocomplete="new-password"' : '' ?>>
                <?php endif; ?>
                <small class="error"><?= e($errores[$n] ?? '') ?></small>
            </div>
        <?php endforeach; ?>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $editId ? 'Guardar cambios' : 'Guardar' ?></button>
            <a class="btn btn-secondary" href="/panel/<?= e($slug) ?>">Cancelar</a>
        </div>
    </form>
    <hr>
<?php endif; ?>

<div class="barra">
    <h3>Registros</h3>
    <form method="get" action="/panel/<?= e($slug) ?>">
        <input type="search" name="q" value="<?= e($q) ?>" placeholder="Buscar en la tabla">
    </form>
</div>
<div class="tabla-scroll">
    <table>
        <thead><tr><th>ID</th><?php foreach ($columnas as $c): ?><th><?= e($c['etiqueta']) ?></th><?php endforeach; ?><?= $puedeEscribir ? '<th>Acciones</th>' : '' ?></tr></thead>
        <tbody>
        <?php foreach ($filas as $f): ?>
            <tr>
                <td><?= e($f['id']) ?></td>
                <?php foreach ($f['celdas'] as $i => $celda): ?>
                    <td><?php if (($columnas[$i]['nombre'] ?? '') === 'estado'): ?>
                        <span class="badge <?= $celda === 'Activo' ? 'active' : 'inactive' ?>"><?= e($celda) ?></span>
                    <?php else: ?><?= e($celda) ?><?php endif; ?></td>
                <?php endforeach; ?>
                <?php if ($puedeEscribir): ?>
                    <td class="acciones">
                        <a class="btn btn-warning btn-sm" href="/panel/<?= e($slug) ?>?editar=<?= e($f['id']) ?>">Editar</a>
                        <form method="post" action="/panel/<?= e($slug) ?>/<?= e($f['id']) ?>/eliminar" data-confirmar="¿Desea eliminar este registro?" style="display:inline">
                            <input type="hidden" name="_csrf" value="<?= e(Session::csrf()) ?>">
                            <button class="btn btn-danger btn-sm" type="submit">Eliminar</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php if (!$filas): ?><p class="vacio">Aún no hay registros.</p><?php endif; ?>
