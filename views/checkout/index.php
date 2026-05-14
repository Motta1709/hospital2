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
.checkout-page { min-height: 100vh; background: var(--bg-main); padding: 40px 20px; color: var(--text-primary); }
.checkout-container { max-width: 1000px; margin: 0 auto; }
.checkout-header { text-align: center; margin-bottom: 40px; }
.checkout-header h1 { font-size: 32px; color: var(--midnight-blue); font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em; }
.checkout-header p { color: var(--steel-gray); font-size: 16px; }
.checkout-grid { display: grid; grid-template-columns: 1fr 400px; gap: 32px; align-items: start; }
.checkout-items { background: #fff; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow); border: 1px solid var(--soft-cyan); }
.checkout-items h2 { font-size: 20px; margin-bottom: 24px; color: var(--midnight-blue); display: flex; align-items: center; gap: 12px; font-weight: 700; }
.item-row { display: flex; align-items: center; gap: 20px; padding: 16px 0; border-bottom: 1px solid var(--soft-cyan); }
.item-row:last-child { border-bottom: none; }
.item-icon { width: 56px; height: 56px; border-radius: 14px; background: var(--soft-cyan); display: flex; align-items: center; justify-content: center; color: var(--midnight-blue); font-size: 20px; flex-shrink: 0; border: 1px solid rgba(16, 42, 67, 0.1); }
.item-info { flex: 1; }
.item-info h4 { font-size: 15px; color: var(--midnight-blue); margin-bottom: 4px; font-weight: 600; }
.item-info small { color: var(--steel-gray); font-size: 13px; display: block; }
.item-price { text-align: right; font-weight: 700; color: var(--midnight-blue); font-size: 16px; }
.item-qty { color: var(--steel-gray); font-size: 13px; }
.checkout-summary { background: #fff; border-radius: var(--radius-lg); padding: 32px; box-shadow: var(--shadow-lg); position: sticky; top: 20px; border: 1px solid var(--soft-cyan); }
.checkout-summary h2 { font-size: 20px; margin-bottom: 24px; color: var(--midnight-blue); display: flex; align-items: center; gap: 12px; font-weight: 700; }
.summary-row { display: flex; justify-content: space-between; padding: 12px 0; font-size: 15px; color: var(--steel-gray); }
.summary-row.total { border-top: 2px solid var(--soft-cyan); padding-top: 20px; margin-top: 12px; font-size: 20px; font-weight: 800; color: var(--midnight-blue); }
.btn-epayco { width: 100%; padding: 16px; border: none; border-radius: var(--radius); background: var(--midnight-blue); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 24px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: var(--transition); box-shadow: 0 4px 14px rgba(16, 42, 67, 0.2); }
.btn-epayco:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(16, 42, 67, 0.3); background: #0a1b2d; }
.btn-nequi-link { width: 100%; padding: 16px; border: 2px solid var(--soft-cyan); border-radius: var(--radius); background: #fff; color: var(--midnight-blue); font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 12px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: var(--transition); text-decoration: none; }
.btn-nequi-link:hover { background: var(--soft-cyan); color: var(--midnight-blue); border-color: var(--midnight-blue); }
.btn-nequi-link i { color: #da3791; }
.btn-back { display: inline-flex; align-items: center; gap: 8px; color: var(--steel-gray); text-decoration: none; font-size: 14px; margin-top: 24px; transition: color .2s; font-weight: 500; }
.btn-back:hover { color: var(--midnight-blue); }
.secure-badge { display: flex; align-items: center; gap: 12px; padding: 16px; background: #f0fdf4; border-radius: 12px; margin-top: 20px; font-size: 13px; color: #166534; border: 1px solid #bbf7d0; line-height: 1.4; }
.secure-badge i { font-size: 20px; color: var(--emerald-vital); }
.payment-separator { display: flex; align-items: center; gap: 16px; margin: 20px 0; color: var(--steel-gray); font-size: 13px; font-weight: 500; }
.payment-separator::before, .payment-separator::after { content: ''; flex: 1; height: 1px; background: var(--soft-cyan); }
@media(max-width: 900px) { .checkout-grid { grid-template-columns: 1fr; } .checkout-summary { position: static; } }
</style>
</head>
<body>
<div class="checkout-page">
<div class="checkout-container">
<div class="checkout-header">
<h1><i class="fas fa-shield-halved"></i> Checkout Seguro</h1>
<p>Revisa tu pedido y procede al pago con ePayco</p>
</div>

<div class="checkout-grid">
<!-- Productos -->
<div class="checkout-items">
<h2><i class="fas fa-shopping-bag"></i> Tu Pedido (<?= count($data['items']) ?> productos)</h2>
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
<h2><i class="fas fa-receipt"></i> Resumen</h2>
<div class="summary-row">
<span>Precio total (IVA incl.)</span>
<span><?= formatCurrency($data['total']) ?></span>
</div>
<div class="summary-row" style="font-size:12px;color:#a0aec0">
<span>Base imponible</span>
<span><?= formatCurrency($data['subtotal']) ?></span>
</div>
<div class="summary-row" style="font-size:12px;color:#a0aec0">
<span>IVA 0% (exento)</span>
<span><?= formatCurrency(0) ?></span>
</div>
<div class="summary-row total">
<span>Total a Pagar</span>
<span><?= formatCurrency($data['total']) ?></span>
</div>

<button class="btn-epayco" id="btnPayEpayco" onclick="openEpaycoCheckout()">
<i class="fas fa-credit-card"></i> Pagar con ePayco
</button>

<a href="<?= APP_URL ?>/?route=nequi" class="btn-nequi-link" id="btnNequi">
    <i class="fas fa-mobile-alt"></i> Pagar con Nequi
</a>

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
