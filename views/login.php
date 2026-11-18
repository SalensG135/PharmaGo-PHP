<?php
use PharmaGo\Core\Session;
/** @var ?string $error @var string $correo @var string $rol */
$flash = Session::pullFlash();
$roles = ['Paciente' => '👤', 'Farmacéutico' => '💊', 'Administrador' => '⚙️'];
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaGo - Iniciar sesión</title>
    <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>
<div class="login-container">
    <div class="logo"><span>Pharma</span><strong>Go</strong></div>
    <p class="subtitulo">Consulta inteligente de medicamentos</p>
    <div class="login-box">
        <h1>Iniciar sesión</h1>
        <p class="descripcion">Selecciona tu tipo de cuenta para continuar</p>
        <form method="post" action="/login" novalidate>
            <input type="hidden" name="_csrf" value="<?= e(Session::csrf()) ?>">
            <div class="roles">
                <?php foreach ($roles as $nombre => $icono): ?>
                    <label class="rol <?= $nombre === $rol ? 'activo' : '' ?>">
                        <input type="radio" name="rol" value="<?= e($nombre) ?>" <?= $nombre === $rol ? 'checked' : '' ?> hidden>
                        <span class="icono"><?= $icono ?></span><span><?= e($nombre) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <div class="rol-seleccionado"><span id="textoRol">Ingresando como <?= e($rol) ?></span></div>
            <div class="campo">
                <label for="correo">Correo electrónico</label>
                <input type="email" id="correo" name="correo" value="<?= e($correo) ?>" placeholder="correo@ejemplo.com" autocomplete="email" required>
            </div>
            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn-login">Iniciar sesión</button>
        </form>
        <?php if ($error): ?><div class="mensaje error"><?= e($error) ?></div><?php endif; ?>
        <?php if ($flash): ?><div class="mensaje exito"><?= e($flash['mensaje']) ?></div><?php endif; ?>
        <div class="registro"><p>¿No tienes una cuenta? <a href="/registro">Registrarse</a></p></div>
        <a href="/" class="volver">Volver al inicio</a>
    </div>
</div>
<script src="/assets/js/login.js"></script>
</body>
</html>
