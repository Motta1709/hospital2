<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Resultado del Pago Nequi — PharmaCRM</title>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{margin:0;font-family:'Inter',sans-serif}

.response-page{min-height:100vh;background:linear-gradient(160deg,#0f0c29 0%,#1a1145 30%,#302b63 60%,#24243e 100%);display:flex;align-items:center;justify-content:center;padding:20px;position:relative;overflow:hidden}
.response-page::before{content:'';position:absolute;top:-100px;right:-100px;width:300px;height:300px;background:radial-gradient(circle,rgba(218,55,145,.12),transparent 70%);border-radius:50%;pointer-events:none}

.response-card{background:rgba(255,255,255,.06);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,.08);border-radius:24px;padding:48px;max-width:520px;width:100%;text-align:center;box-shadow:0 12px 48px rgba(0,0,0,.3);position:relative;z-index:1}

.status-icon{width:90px;height:90px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:38px;position:relative}
.status-icon.success{background:rgba(56,161,105,.12);color:#48bb78;box-shadow:0 0 0 0 rgba(72,187,120,.3);animation:successPulse 2s infinite}
.status-icon.pending{background:rgba(214,158,46,.12);color:#ecc94b;box-shadow:0 0 0 0 rgba(236,201,75,.3);animation:pendingPulse 2s infinite}
.status-icon.failed{background:rgba(229,62,62,.12);color:#fc8181}

@keyframes successPulse{0%{box-shadow:0 0 0 0 rgba(72,187,120,.3)}70%{box-shadow:0 0 0 15px rgba(72,187,120,0)}100%{box-shadow:0 0 0 0 rgba(72,187,120,0)}}
@keyframes pendingPulse{0%{box-shadow:0 0 0 0 rgba(236,201,75,.3)}70%{box-shadow:0 0 0 15px rgba(236,201,75,0)}100%{box-shadow:0 0 0 0 rgba(236,201,75,0)}}

.response-card h1{font-size:24px;color:#fff;margin-bottom:8px;font-weight:700}
.response-card>p{color:rgba(255,255,255,.5);font-size:14px;margin-bottom:28px;line-height:1.6}

.detail-row{display:flex;justify-content:space-between;padding:12px 18px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:12px;margin-bottom:8px;font-size:13px}
.detail-row span:first-child{color:rgba(255,255,255,.45)}
.detail-row span:last-child{font-weight:600;color:rgba(255,255,255,.9)}

.nequi-badge{display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,rgba(218,55,145,.15),rgba(224,64,104,.1));border:1px solid rgba(218,55,145,.2);border-radius:8px;padding:4px 12px;font-size:12px;color:#da3791;font-weight:700}

.btn-group{display:flex;gap:12px;margin-top:32px;justify-content:center;flex-wrap:wrap}
.btn-group a{padding:14px 28px;border-radius:14px;text-decoration:none;font-weight:600;font-size:14px;transition:all .3s;display:inline-flex;align-items:center;gap:8px}
.btn-dashboard{background:linear-gradient(135deg,#da3791,#e04068);color:#fff;box-shadow:0 4px 16px rgba(218,55,145,.3)}
.btn-dashboard:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(218,55,145,.4)}
.btn-shop{background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.7)}
.btn-shop:hover{background:rgba(255,255,255,.1);color:#fff}
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
<h1>Pago Nequi Exitoso!</h1>
<p>Tu pago ha sido procesado correctamente a traves de Nequi.</p>
<?php elseif ($isPending): ?>
<div class="status-icon pending"><i class="fas fa-clock"></i></div>
<h1>Pago Pendiente</h1>
<p>Tu pago esta siendo procesado por Nequi. Te notificaremos cuando se confirme.</p>
<?php else: ?>
<div class="status-icon failed"><i class="fas fa-times"></i></div>
<h1>Pago No Completado</h1>
<p>Hubo un problema con tu pago Nequi. Puedes intentarlo nuevamente.</p>
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
    <span><span class="nequi-badge"><i class="fas fa-mobile-alt"></i> Nequi</span></span>
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
