<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Checkout — PharmaCRM</title>
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
<link rel="stylesheet" href="<?= APP_URL ?>/public/css/customer.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<script src="https://checkout.epayco.co/checkout.js"></script>
<style>
.checkout-page{min-height:100vh;background:linear-gradient(135deg,#f0f4f8 0%,#e2e8f0 100%);padding:40px 20px}
.checkout-container{max-width:900px;margin:0 auto}
.checkout-header{text-align:center;margin-bottom:32px}
.checkout-header h1{font-size:28px;color:#1a202c;margin-bottom:8px}
.checkout-header p{color:#718096;font-size:14px}
.checkout-grid{display:grid;grid-template-columns:1fr 380px;gap:24px;align-items:start}
.checkout-items{background:#fff;border-radius:16px;padding:24px;box-shadow:0 4px 16px rgba(0,0,0,.06)}
.checkout-items h2{font-size:18px;margin-bottom:16px;color:#2d3748;display:flex;align-items:center;gap:8px}
.item-row{display:flex;align-items:center;gap:16px;padding:14px 0;border-bottom:1px solid #edf2f7}
.item-row:last-child{border-bottom:none}
.item-icon{width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;flex-shrink:0}
.item-info{flex:1}
.item-info h4{font-size:14px;color:#2d3748;margin-bottom:2px}
.item-info small{color:#a0aec0;font-size:12px}
.item-price{text-align:right;font-weight:700;color:#2d3748;font-size:15px}
.item-qty{color:#a0aec0;font-size:12px}
.checkout-summary{background:#fff;border-radius:16px;padding:24px;box-shadow:0 4px 16px rgba(0,0,0,.06);position:sticky;top:20px}
.checkout-summary h2{font-size:18px;margin-bottom:20px;color:#2d3748;display:flex;align-items:center;gap:8px}
.summary-row{display:flex;justify-content:space-between;padding:10px 0;font-size:14px;color:#4a5568}
.summary-row.total{border-top:2px solid #edf2f7;padding-top:16px;margin-top:8px;font-size:18px;font-weight:700;color:#1a202c}
.btn-epayco{width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,#38b2ac,#319795);color:#fff;font-size:16px;font-weight:700;cursor:pointer;margin-top:20px;display:flex;align-items:center;justify-content:center;gap:10px;transition:all .3s ease;box-shadow:0 4px 14px rgba(56,178,172,.3)}
.btn-epayco:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(56,178,172,.4)}
.btn-back{display:inline-flex;align-items:center;gap:6px;color:#718096;text-decoration:none;font-size:13px;margin-top:16px;transition:color .2s}
.btn-back:hover{color:#4a5568}
.secure-badge{display:flex;align-items:center;gap:8px;padding:12px;background:#f0fff4;border-radius:10px;margin-top:16px;font-size:12px;color:#38a169}
.secure-badge i{font-size:16px}
@media(max-width:768px){.checkout-grid{grid-template-columns:1fr}}
</style>
</head>
<body>
<div class="checkout-page">
<div class="checkout-container">
<div class="checkout-header">
<h1><i class="fas fa-shield-halved" style="color:#38b2ac"></i> Checkout Seguro</h1>
<p>Revisa tu pedido y procede al pago con ePayco</p>
</div>

<div class="checkout-grid">
<!-- Productos -->
<div class="checkout-items">
<h2><i class="fas fa-shopping-bag" style="color:#667eea"></i> Tu Pedido (<?= count($data['items']) ?> productos)</h2>
<?php foreach ($data['items'] as $item): ?>
<div class="item-row">
<div class="item-icon"><i class="fas fa-pills"></i></div>
<div class="item-info">
<h4><?= htmlspecialchars($item['name']) ?></h4>
<small><?= htmlspecialchars($item['generic_name'] ?? '') ?> — <?= htmlspecialchars($item['presentation'] ?? '') ?></small>
</div>
<div>
<div class="item-price"><?= formatCurrency($item['subtotal']) ?></div>
<div class="item-qty">x<?= $item['qty'] ?> und</div>
</div>
</div>
<?php endforeach; ?>
</div>

<!-- Resumen y boton de pago -->
<div class="checkout-summary">
<h2><i class="fas fa-receipt" style="color:#ed8936"></i> Resumen</h2>
<div class="summary-row">
<span>Subtotal</span>
<span><?= formatCurrency($data['subtotal']) ?></span>
</div>
<div class="summary-row">
<span>IVA (19%)</span>
<span><?= formatCurrency($data['iva']) ?></span>
</div>
<div class="summary-row total">
<span>Total a Pagar</span>
<span><?= formatCurrency($data['total']) ?></span>
</div>

<button class="btn-epayco" id="btnPayEpayco" onclick="openEpaycoCheckout()">
<i class="fas fa-credit-card"></i> Pagar con ePayco
</button>

<div class="secure-badge">
<i class="fas fa-lock"></i>
<span>Pago seguro procesado por ePayco. Tus datos estan protegidos con cifrado SSL.</span>
</div>

<a href="<?= APP_URL ?>/?route=cart" class="btn-back">
<i class="fas fa-arrow-left"></i> Volver al carrito
</a>
</div>
</div>
</div>
</div>

<script>
var handler = ePayco.checkout.configure({
    key: '<?= EPAYCO_PUBLIC_KEY ?>',
    test: <?= EPAYCO_TESTING ? 'true' : 'false' ?>
});

function openEpaycoCheckout() {
    handler.open({
        external: 'false',
        autoclick: 'false',

        // Datos obligatorios
        name: 'Compra PharmaCRM',
        description: 'Pedido <?= $data['invoiceRef'] ?> - <?= count($data['items']) ?> producto(s)',
        invoice: '<?= $data['invoiceRef'] ?>',
        currency: '<?= EPAYCO_CURRENCY ?>',
        amount: '<?= $data['total'] ?>',
        tax_base: '<?= $data['subtotal'] ?>',
        tax: '<?= $data['iva'] ?>',
        country: '<?= EPAYCO_COUNTRY ?>',
        lang: '<?= EPAYCO_LANG ?>',

        // Datos del cliente
        name_billing: '<?= htmlspecialchars($data['client']['first_name'] . ' ' . $data['client']['last_name']) ?>',
        address_billing: '<?= htmlspecialchars($data['client']['address'] ?? 'N/A') ?>',
        mobilephone_billing: '<?= htmlspecialchars($data['client']['phone'] ?? '') ?>',
        email_billing: '<?= htmlspecialchars($data['client']['email'] ?? '') ?>',
        type_doc_billing: '<?= $data['client']['document_type'] ?? 'CC' ?>',
        number_doc_billing: '<?= $data['client']['document_number'] ?? '' ?>',

        // URLs de respuesta
        response: '<?= EPAYCO_RESPONSE_URL ?>',
        confirmation: '<?= EPAYCO_CONFIRMATION_URL ?>',

        // Datos extra para identificar la orden
        extra1: '<?= $data['invoiceRef'] ?>',
        extra2: '<?= $_SESSION['client_id'] ?? '' ?>',
    });
}
</script>
</body>
</html>
