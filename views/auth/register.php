<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Crear Cuenta — PharmaCRM</title>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="login-wrapper">
<div class="login-card" style="max-width:480px">
<div class="logo">
<div style="font-size:48px;margin-bottom:12px"><i class="fas fa-user-plus" style="color:var(--primary-light)"></i></div>
<h1>Crear Cuenta</h1>
<p>Registrate como cliente de PharmaCRM</p>
</div>
<?php $flash = getFlash(); if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>">
<i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>
<form action="<?= APP_URL ?>/?route=auth&action=register" method="POST">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group">
<label for="first_name"><i class="fas fa-user"></i> Nombre</label>
<input type="text" id="first_name" name="first_name" class="form-control" placeholder="Tu nombre" required value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>">
</div>
<div class="form-group">
<label for="last_name"><i class="fas fa-user"></i> Apellido</label>
<input type="text" id="last_name" name="last_name" class="form-control" placeholder="Tu apellido" required value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>">
</div>
</div>
<div style="display:grid;grid-template-columns:100px 1fr;gap:12px">
<div class="form-group">
<label for="document_type">Tipo</label>
<select id="document_type" name="document_type" class="form-control">
<option value="CC">CC</option>
<option value="CE">CE</option>
<option value="TI">TI</option>
<option value="PP">PP</option>
</select>
</div>
<div class="form-group">
<label for="document_number"><i class="fas fa-id-card"></i> Documento</label>
<input type="text" id="document_number" name="document_number" class="form-control" placeholder="No. de documento" required value="<?= htmlspecialchars($_POST['document_number'] ?? '') ?>">
</div>
</div>
<div class="form-group">
<label for="email"><i class="fas fa-envelope"></i> Correo electronico</label>
<input type="email" id="email" name="email" class="form-control" placeholder="tu@correo.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
</div>
<div class="form-group">
<label for="phone"><i class="fas fa-phone"></i> Telefono (opcional)</label>
<input type="text" id="phone" name="phone" class="form-control" placeholder="3001234567" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="form-group">
<label for="password"><i class="fas fa-lock"></i> Contrasena</label>
<input type="password" id="password" name="password" class="form-control" placeholder="Min. 6 caracteres" required minlength="6">
</div>
<div class="form-group">
<label for="password_confirm"><i class="fas fa-lock"></i> Confirmar</label>
<input type="password" id="password_confirm" name="password_confirm" class="form-control" placeholder="Repita contrasena" required minlength="6">
</div>
</div>
<button type="submit" class="btn btn-primary btn-block" style="margin-top:12px">
<i class="fas fa-user-plus"></i> Crear Mi Cuenta
</button>
</form>
<div style="text-align:center;margin-top:20px;font-size:13px">
<p>Ya tienes cuenta? <a href="<?= APP_URL ?>/?route=login" style="color:var(--primary-light);text-decoration:none;font-weight:600">Iniciar Sesion</a></p>
</div>
</div>
</div>
</body>
</html>
