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
<form action="<?= APP_URL ?>/?route=auth&action=login" method="POST">
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
<div style="text-align:center;margin-top:20px">
<div style="display:flex;align-items:center;gap:12px;margin-bottom:16px">
<hr style="flex:1;border:none;border-top:1px solid var(--border-color,#333)">
<span style="font-size:12px;color:var(--text-muted);white-space:nowrap">Nuevo en PharmaCRM?</span>
<hr style="flex:1;border:none;border-top:1px solid var(--border-color,#333)">
</div>
<a href="<?= APP_URL ?>/?route=auth&action=register" class="btn btn-block" style="background:transparent;border:2px solid var(--primary-light);color:var(--primary-light);padding:10px 20px;border-radius:8px;text-decoration:none;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s ease" onmouseover="this.style.background='var(--primary-light)';this.style.color='#fff'" onmouseout="this.style.background='transparent';this.style.color='var(--primary-light)'">
<i class="fas fa-user-plus"></i> Crear Cuenta de Cliente
</a>
<p style="margin-top:16px;font-size:11px;color:var(--text-muted)">PharmaCRM v1.0 — CRM Farmaceutico</p>
</div>
</div>
</div>
</body>
</html>

