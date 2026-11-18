<?php
use PharmaGo\Core\{FieldOptions, Session};
/** @var array $errores @var array $v @var array $campos */
$err = static fn(string $n) => isset($errores[$n]) ? '<small class="error-campo">' . e($errores[$n]) . '</small>' : '';
?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PharmaGo - Crear cuenta</title>
    <link rel="stylesheet" href="/assets/css/login.css">
</head>
<body>
<div class="login-container">
    <div class="logo"><span>Pharma</span><strong>Go</strong></div>
    <div class="login-box">
        <h1>Crear cuenta</h1>
        <p class="descripcion">Regístrate como paciente para consultar medicamentos</p>
        <form method="post" action="/registro" novalidate>
            <input type="hidden" name="_csrf" value="<?= e(Session::csrf()) ?>">
            <?php foreach ($campos as $c): $n = $c['nombre']; ?>
                <div class="campo">
                    <label for="<?= e($n) ?>"><?= e($c['etiqueta']) ?></label>
                    <?php if ($c['tipo'] === 'select'): ?>
                        <select id="<?= e($n) ?>" name="<?= e($n) ?>" required>
                            <option value="">-- Seleccionar --</option>
                            <?php foreach (FieldOptions::for($c) as [$ov, $ot]): ?>
                                <option value="<?= e($ov) ?>" <?= (string) $ov === (string) ($v[$n] ?? '') ? 'selected' : '' ?>><?= e($ot) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input type="<?= e($c['tipo']) ?>" id="<?= e($n) ?>" name="<?= e($n) ?>" maxlength="<?= (int) ($c['max'] ?? 100) ?>"
                               value="<?= $c['tipo'] === 'password' ? '' : e($v[$n] ?? '') ?>" required>
                    <?php endif; ?>
                    <?= $err($n) ?>
                </div>
            <?php endforeach; ?>
            <div class="campo">
                <label for="confirmar">Confirmar contraseña</label>
                <input type="password" id="confirmar" name="confirmar" maxlength="72" required>
                <?= $err('confirmar') ?>
            </div>
            <label class="aceptar"><input type="checkbox" name="acepto" value="1" <?= !empty($v['acepto']) ? 'checked' : '' ?>> Acepto el tratamiento de mis datos</label>
            <?= $err('acepto') ?>
            <button type="submit" class="btn-login">Crear cuenta</button>
        </form>
        <div class="registro"><p>¿Ya tienes una cuenta? <a href="/login">Iniciar sesión</a></p></div>
    </div>
</div>
</body>
</html>
