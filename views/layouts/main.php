<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pageTitle ?? 'Dashboard' ?> — PharmaCRM</title>
<meta name="description" content="PharmaCRM - Sistema CRM para farmacias independientes en Colombia">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="app-layout">
<aside class="sidebar" id="sidebar">
<div class="sidebar-brand">
<div class="brand-icon"><i class="fas fa-prescription-bottle-medical"></i></div>
<div><h2>PharmaCRM</h2><small>Gestión Farmacéutica</small></div>
</div>
<nav class="sidebar-nav">
<div class="nav-section">
<div class="nav-section-title">Principal</div>
<?php if (Auth::hasPermission('view_dashboard')): ?>
<a href="<?= APP_URL ?>?route=dashboard" class="nav-link <?= ($currentRoute ?? '') === 'dashboard' ? 'active' : '' ?>">
<i class="fas fa-chart-pie"></i> Dashboard
</a>
<?php endif; ?>
</div>

<div class="nav-section">
<div class="nav-section-title">Operaciones</div>
<?php if (Auth::hasPermission('process_sales')): ?>
<a href="<?= APP_URL ?>?route=sales&action=pos" class="nav-link <?= ($currentRoute ?? '') === 'sales' ? 'active' : '' ?>">
<i class="fas fa-cash-register"></i> Punto de Venta
</a>
<?php endif; ?>

<?php if (Auth::hasPermission('view_inventory')): ?>
<a href="<?= APP_URL ?>?route=inventory" class="nav-link <?= ($currentRoute ?? '') === 'inventory' ? 'active' : '' ?>">
<i class="fas fa-boxes-stacked"></i> Inventario
<?php
$pm = new Product();
$exp = count($pm->getExpiringProducts(7));
if($exp > 0) echo '<span class="badge">'.$exp.'</span>';
?>
</a>
<?php endif; ?>

<?php if (Auth::hasPermission('view_sales')): ?>
<a href="<?= APP_URL ?>?route=sales" class="nav-link <?= ($currentRoute ?? '') === 'sales-history' ? 'active' : '' ?>">
<i class="fas fa-receipt"></i> Historial Ventas
</a>
<?php endif; ?>
</div>

<div class="nav-section">
<div class="nav-section-title">CRM</div>
<?php if (Auth::hasPermission('view_clients')): ?>
<a href="<?= APP_URL ?>?route=clients" class="nav-link <?= ($currentRoute ?? '') === 'clients' ? 'active' : '' ?>">
<i class="fas fa-users"></i> Clientes
</a>
<?php endif; ?>

<?php if (Auth::hasPermission('view_loyalty')): ?>
<a href="<?= APP_URL ?>?route=loyalty" class="nav-link <?= ($currentRoute ?? '') === 'loyalty' ? 'active' : '' ?>">
<i class="fas fa-star"></i> Fidelización
</a>
<?php endif; ?>
</div>

<div class="nav-section">
<div class="nav-section-title">Análisis</div>
<?php if (Auth::hasPermission('view_reports')): ?>
<a href="<?= APP_URL ?>?route=reports" class="nav-link <?= ($currentRoute ?? '') === 'reports' ? 'active' : '' ?>">
<i class="fas fa-chart-bar"></i> Reportes
</a>
<?php endif; ?>
</div>

<?php if (Auth::hasPermission('view_users') || Auth::hasPermission('manage_rbac')): ?>
<div class="nav-section">
<div class="nav-section-title">Seguridad</div>
<?php if (Auth::hasPermission('view_users')): ?>
<a href="<?= APP_URL ?>?route=users" class="nav-link <?= ($currentRoute ?? '') === 'users' ? 'active' : '' ?>">
<i class="fas fa-users-gear"></i> Usuarios
</a>
<?php endif; ?>

<?php if (Auth::hasPermission('manage_rbac')): ?>
<a href="<?= APP_URL ?>?route=rbac" class="nav-link <?= ($currentRoute ?? '') === 'rbac' ? 'active' : '' ?>">
<i class="fas fa-shield-halved"></i> Permisos RBAC
</a>
<?php endif; ?>
</div>
<?php endif; ?>
</nav>
<div class="sidebar-footer">
<div class="user-info">
<div class="user-avatar"><?= strtoupper(substr(Auth::user()['full_name'] ?? 'A', 0, 1)) ?></div>
<div class="user-details">
<h4><?= Auth::user()['full_name'] ?? 'Admin' ?></h4>
<span><?= ucfirst(Auth::user()['role'] ?? 'admin') ?></span>
</div>
<a href="<?= APP_URL ?>?route=auth&action=logout" title="Cerrar sesión" style="color:var(--text-muted)"><i class="fas fa-right-from-bracket"></i></a>
</div>
</div>
</aside>

<main class="main-content">
<header class="topbar">
<div style="display:flex;align-items:center;gap:12px">
<button class="sidebar-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
<h1><?= $pageTitle ?? 'Dashboard' ?></h1>
</div>
<div class="topbar-actions">
<span style="font-size:13px;color:var(--text-muted)"><i class="fas fa-calendar"></i> <?= date('d M Y') ?></span>
</div>
</header>
<div class="page-content">
<?php
$flash = getFlash();
if ($flash):
?>
<div class="alert alert-<?= $flash['type'] ?>">
<i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
<?= htmlspecialchars($flash['message']) ?>
</div>
<?php endif; ?>
<?php if (isset($content) && file_exists($content)) include $content; ?>
</div>
</main>
</div>
<script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
