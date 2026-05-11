<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Resultado del Pago — PharmaCRM</title>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.response-page{min-height:100vh;background:linear-gradient(135deg,#f0f4f8,#e2e8f0);display:flex;align-items:center;justify-content:center;padding:20px}
.response-card{background:#fff;border-radius:20px;padding:48px;max-width:520px;width:100%;text-align:center;box-shadow:0 8px 32px rgba(0,0,0,.08)}
.status-icon{width:80px;height:80px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:36px}
.status-icon.success{background:#f0fff4;color:#38a169}
.status-icon.pending{background:#fffbeb;color:#d69e2e}
.status-icon.failed{background:#fff5f5;color:#e53e3e}
.response-card h1{font-size:24px;margin-bottom:8px;color:#1a202c}
.response-card p{color:#718096;font-size:14px;margin-bottom:24px}
.detail-row{display:flex;justify-content:space-between;padding:10px 16px;background:#f7fafc;border-radius:8px;margin-bottom:8px;font-size:13px}
.detail-row span:first-child{color:#718096}
.detail-row span:last-child{font-weight:600;color:#2d3748}
.btn-group{display:flex;gap:12px;margin-top:28px;justify-content:center}
.btn-group a{padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:600;font-size:14px;transition:all .3s}
.btn-dashboard{background:#667eea;color:#fff}
.btn-dashboard:hover{background:#5a67d8}
.btn-shop{background:#edf2f7;color:#4a5568}
.btn-shop:hover{background:#e2e8f0}
</style>
</head>
<body>
<div class="response-page">
<div class="response-card">
<?php
$txData = $data['result']['data'] ?? null;
$status = $txData['x_response'] ?? 'Desconocido';
$isSuccess = ($status === 'Aceptada');
$isPending = ($status === 'Pendiente');
?>

<?php if ($isSuccess): ?>
<div class="status-icon success"><i class="fas fa-check"></i></div>
<h1>Pago Exitoso!</h1>
<p>Tu pago ha sido procesado correctamente.</p>
<?php elseif ($isPending): ?>
<div class="status-icon pending"><i class="fas fa-clock"></i></div>
<h1>Pago Pendiente</h1>
<p>Tu pago esta siendo procesado. Te notificaremos cuando se confirme.</p>
<?php else: ?>
<div class="status-icon failed"><i class="fas fa-times"></i></div>
<h1>Pago No Completado</h1>
<p>Hubo un problema con tu pago. Puedes intentarlo nuevamente.</p>
<?php endif; ?>

<?php if ($txData): ?>
<div class="detail-row">
<span>Referencia ePayco</span>
<span><?= htmlspecialchars($txData['x_ref_payco'] ?? $data['ref_payco'] ?? 'N/A') ?></span>
</div>
<div class="detail-row">
<span>Factura</span>
<span><?= htmlspecialchars($txData['x_invoice'] ?? 'N/A') ?></span>
</div>
<div class="detail-row">
<span>Monto</span>
<span>$ <?= number_format($txData['x_amount'] ?? 0, 0, ',', '.') ?> COP</span>
</div>
<div class="detail-row">
<span>Estado</span>
<span><?= htmlspecialchars($status) ?></span>
</div>
<div class="detail-row">
<span>Metodo de Pago</span>
<span><?= htmlspecialchars($txData['x_franchise'] ?? 'N/A') ?></span>
</div>
<?php else: ?>
<div class="detail-row">
<span>Referencia</span>
<span><?= htmlspecialchars($data['ref_payco'] ?? 'No disponible') ?></span>
</div>
<?php endif; ?>

<div class="btn-group">
<a href="<?= APP_URL ?>/?route=customer-dashboard" class="btn-dashboard">
<i class="fas fa-chart-pie"></i> Mi Dashboard
</a>
<a href="<?= APP_URL ?>/?route=home" class="btn-shop">
<i class="fas fa-store"></i> Seguir Comprando
</a>
</div>
</div>
</div>
</body>
</html>
