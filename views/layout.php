<?php
use PharmaGo\Core\{Auth, Modulos, Session};
$u = Auth::user();
$flash = Session::pullFlash();
$actual = rtrim((string) parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - PharmaGo</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
</head>
<body>
<div class="app">
    <aside class="lateral">
        <div class="logo">Pharma<strong>Go</strong></div>
        <nav id="menu">
            <?php foreach (Modulos::visibles() as $slug => $m): $href = $m['ruta'] ?? '/panel/' . $slug; ?>
                <form method="get" action="<?= e($href) ?>">
                    <button type="submit" class="<?= str_starts_with($actual, $href) ? 'activo' : '' ?>"><?= e($m['nombre']) ?></button>
                </form>
            <?php endforeach; ?>
        </nav>
        <div class="sesion">
            <div><strong><?= e($u['nombre']) ?></strong> <span><?= e($u['rol']) ?></span></div>
            <form method="post" action="/logout">
                <input type="hidden" name="_csrf" value="<?= e(Session::csrf()) ?>">
                <button class="btn btn-secondary btn-sm" type="submit">Cerrar sesión</button>
            </form>
        </div>
    </aside>
    <main class="contenido">
        <div class="container" id="contenido">
            <?php if ($flash): ?><p role="status" class="<?= e($flash['tipo']) ?>"><?= e($flash['mensaje']) ?></p><?php endif; ?>
            <?= $contenido ?>
        </div>
    </main>
</div>
<script src="/assets/js/panel.js"></script>
</body>
</html>
