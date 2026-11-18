<?php
use PharmaGo\Repository\DisponibilidadRepository as D;
/** @var string $q @var string $error @var array $resultados @var int $sel @var bool $buscado */
?>
<section class="panel">
    <h2>Buscar medicamento</h2>
    <p class="ayuda-busqueda">Escriba el nombre del medicamento y vea en qué farmacia lo encuentra y dónde queda.</p>
    <form method="get" action="/panel/buscar" class="busqueda-disp" role="search">
        <label for="q">Medicamento</label>
        <div class="fila-busqueda">
            <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Ej. Dolex, Losartán, Omeprazol" maxlength="60" autocomplete="off">
            <button type="submit" class="btn btn-primary">Buscar</button>
        </div>
        <small class="error"><?= e($error) ?></small>
    </form>

    <?php if ($buscado && !$error && !$resultados): ?>
        <p class="vacio">No encontramos "<?= e($q) ?>" disponible en ninguna farmacia.</p>
    <?php elseif ($resultados): $n = count($resultados); $f = $resultados[$sel]; ?>
        <p class="conteo"><?= $n ?> <?= $n === 1 ? 'farmacia lo tiene' : 'farmacias lo tienen' ?>:</p>
        <div class="resultado-grid">
            <ul class="lista-sedes">
                <?php foreach ($resultados as $i => $r): ?>
                    <li>
                        <a class="sede-resultado <?= $i === $sel ? 'activa' : '' ?>" href="/panel/buscar?q=<?= rawurlencode($q) ?>&amp;sel=<?= $i ?>">
                            <strong><?= e($r['Medicamento']) ?></strong>
                            <span><?= e($r['Concentracion']) ?> · <?= e($r['Presentacion']) ?></span>
                            <span class="sede-nombre"><?= e($r['Farmacia']) ?></span>
                            <span><?= e($r['Direccion']) ?>, <?= e($r['Localidad']) ?></span>
                            <span>Tel. <?= e($r['Telefono'] ?: 'no registrado') ?> · <?= (int) $r['StockDisponible'] ?> unidades</span>
                            <span class="etiqueta <?= $r['StockDisponible'] < 10 ? 'poco' : 'ok' ?>"><?= $r['StockDisponible'] < 10 ? 'Pocas unidades' : 'Disponible' ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="mapa-caja">
                <p class="direccion-mapa"><?= e(D::direccionCompleta($f)) ?></p>
                <iframe title="Mapa de Google con la ubicación de la farmacia" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="<?= e(D::urlMapa($f)) ?>"></iframe>
                <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="<?= e(D::urlGoogleMaps($f)) ?>">Abrir en Google Maps</a>
            </div>
        </div>
    <?php endif; ?>
</section>
