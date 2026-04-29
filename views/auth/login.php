<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Iniciar Sesión — PharmaCRM</title>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="login-wrapper">
<div class="login-card">
<div class="logo">
<div style="font-size:48px;margin-bottom:12px"><i class="fas fa-prescription-bottle-medical" style="color:var(--primary-light)"></i></div>
<h1>PharmaCRM</h1>
<p>Sistema de Gestión Farmacéutica</p>
</div>
<?php $flash = getFlash(); if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>">
<i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>
<form action="<?= APP_URL ?>?route=auth&action=login" method="POST">
<div class="form-group">
<label for="username"><i class="fas fa-user"></i> Usuario</label>
<input type="text" id="username" name="username" class="form-control" placeholder="Ingrese su usuario" required autofocus>
</div>
<div class="form-group">
<label for="password"><i class="fas fa-lock"></i> Contraseña</label>
<input type="password" id="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
</div>
<button type="submit" class="btn btn-primary btn-block" style="margin-top:8px">
<i class="fas fa-sign-in-alt"></i> Iniciar Sesión
</button>
</form>
<div style="text-align:center;margin-top:24px;font-size:12px;color:var(--text-muted)">
<p>Demo: admin / password</p>
<p style="margin-top:8px">PharmaCRM v1.0 — CRM Farmacéutico</p>
</div>
</div>
</div>
</body>
</html>
