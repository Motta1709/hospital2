<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pageTitle ?? 'Mi Panel' ?> — PharmaCRM</title>
<meta name="description" content="PharmaCRM - Panel del Cliente">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/customer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="customer-body">
<div class="customer-layout">
<!-- Sidebar del Cliente -->
<aside class="customer-sidebar" id="customerSidebar">
<div class="sidebar-brand">
    <div class="brand-icon"><i class="fas fa-prescription-bottle-medical"></i></div>
    <div><h2>PharmaCRM</h2><small>Mi Cuenta</small></div>
</div>
<nav class="sidebar-nav">
    <div class="nav-section">
        <div class="nav-section-title">Panel</div>
        <a href="<?= APP_URL ?>?route=customer-dashboard" class="nav-link <?= ($_GET['section'] ?? '') === '' ? 'active' : '' ?>" data-section="resumen">
            <i class="fas fa-chart-pie"></i> Resumen
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-title">Mis Operaciones</div>
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=compras" class="nav-link <?= ($_GET['section'] ?? '') === 'compras' ? 'active' : '' ?>" data-section="compras">
            <i class="fas fa-shopping-bag"></i> Mis Compras
        </a>
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=domicilios" class="nav-link <?= ($_GET['section'] ?? '') === 'domicilios' ? 'active' : '' ?>" data-section="domicilios">
            <i class="fas fa-truck"></i> Domicilios
            <?php if (!empty($data['summary']['domicilios_activos'])): ?>
            <span class="badge"><?= $data['summary']['domicilios_activos'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=reservas" class="nav-link <?= ($_GET['section'] ?? '') === 'reservas' ? 'active' : '' ?>" data-section="reservas">
            <i class="fas fa-bookmark"></i> Reservas
            <?php if (!empty($data['summary']['reservas_pendientes'])): ?>
            <span class="badge"><?= $data['summary']['reservas_pendientes'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=devoluciones" class="nav-link <?= ($_GET['section'] ?? '') === 'devoluciones' ? 'active' : '' ?>" data-section="devoluciones">
            <i class="fas fa-rotate-left"></i> Devoluciones
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-title">Soporte</div>
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=pqrsf" class="nav-link <?= ($_GET['section'] ?? '') === 'pqrsf' ? 'active' : '' ?>" data-section="pqrsf">
            <i class="fas fa-headset"></i> PQRSF
            <?php if (!empty($data['summary']['pqrsf_abiertas'])): ?>
            <span class="badge"><?= $data['summary']['pqrsf_abiertas'] ?></span>
            <?php endif; ?>
        </a>
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=notificaciones" class="nav-link <?= ($_GET['section'] ?? '') === 'notificaciones' ? 'active' : '' ?>" data-section="notificaciones">
            <i class="fas fa-bell"></i> Notificaciones
            <?php if (!empty($data['unreadCount'])): ?>
            <span class="badge"><?= $data['unreadCount'] ?></span>
            <?php endif; ?>
        </a>
    </div>
    <div class="nav-section">
        <div class="nav-section-title">Cuenta</div>
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=perfil" class="nav-link <?= ($_GET['section'] ?? '') === 'perfil' ? 'active' : '' ?>" data-section="perfil">
            <i class="fas fa-user-cog"></i> Mi Perfil
        </a>
    </div>
</nav>
<div class="sidebar-footer">
    <div class="user-info">
        <div class="user-avatar"><?= strtoupper(substr($data['profile']['first_name'] ?? 'C', 0, 1)) ?></div>
        <div class="user-details">
            <h4><?= htmlspecialchars(($data['profile']['first_name'] ?? '') . ' ' . ($data['profile']['last_name'] ?? '')) ?></h4>
            <span><i class="fas fa-star text-warning"></i> <?= number_format($data['summary']['puntos'] ?? 0) ?> puntos</span>
        </div>
        <a href="<?= APP_URL ?>?route=home" title="Volver a la tienda" style="color:var(--text-muted)"><i class="fas fa-store"></i></a>
    </div>
</div>
</aside>

<!-- Contenido Principal -->
<main class="main-content">
<header class="topbar">
    <div style="display:flex;align-items:center;gap:12px">
        <button class="sidebar-toggle" onclick="document.getElementById('customerSidebar').classList.toggle('open')"><i class="fas fa-bars"></i></button>
        <h1><?= $pageTitle ?? 'Mi Panel' ?></h1>
    </div>
    <div class="topbar-actions">
        <span class="customer-greeting">Hola, <?= htmlspecialchars($data['profile']['first_name'] ?? 'Cliente') ?></span>
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
<script>
// Cerrar modales al hacer clic fuera del contenido
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});

// Resaltar navegacion actual
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const section = params.get('section') || 'resumen';
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.getAttribute('data-section') === section) {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
});

// Manejo de carga de items para devoluciones (Refinado)
function loadOrderItems(saleId) {
    const select = document.getElementById('itemSelect');
    const container = document.getElementById('itemsContainer');
    if (!saleId) { container.style.display = 'none'; return; }
    
    select.innerHTML = '<option value="">Cargando productos...</option>';
    container.style.display = 'block';
    
    fetch('<?= APP_URL ?>?route=customer-dashboard&action=getOrderItems&id=' + saleId)
        .then(res => res.json())
        .then(items => {
            select.innerHTML = '<option value="">Seleccione producto...</option>';
            if (items.length === 0) {
                select.innerHTML = '<option value="">No hay productos elegibles</option>';
            }
            items.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = `${item.product_name} x${item.quantity} (${item.unit_price})`;
                select.appendChild(opt);
            });
        })
        .catch(err => {
            select.innerHTML = '<option value="">Error al cargar items</option>';
            console.error(err);
        });
}
</script>
</body>
</html>
