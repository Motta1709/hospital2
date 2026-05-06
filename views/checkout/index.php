<div class="checkout-page py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-primary py-4 text-center">
                        <h2 class="h4 text-white mb-0">Resumen de Pago</h2>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        <div class="mb-4 text-center">
                            <p class="text-muted mb-1">Total a pagar</p>
                            <h2 class="display-5 fw-bold text-primary mb-0">
                                <?= formatCurrency($data['total']) ?>
                            </h2>
                            <small class="text-muted">Factura: <?= $data['invoice'] ?></small>
                        </div>

                        <div class="order-summary bg-light rounded-3 p-3 mb-4">
                            <h6 class="fw-bold mb-3 border-bottom pb-2">Detalle de productos</h6>
                            <?php foreach ($data['items'] as $item): ?>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span><?= $item['qty'] ?>x <?= htmlspecialchars($item['name']) ?></span>
                                    <span class="fw-bold"><?= formatCurrency($item['subtotal']) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-grid gap-3">
                            <button id="btnPay" class="btn btn-primary btn-lg py-3 rounded-pill shadow">
                                <i class="fas fa-credit-card me-2"></i> Pagar con ePayco
                            </button>
                            <a href="<?= APP_URL ?>?route=cart" class="btn btn-link text-muted">
                                Cancelar y volver al carrito
                            </a>
                        </div>

                        <div class="mt-4 text-center">
                            <p class="small text-muted mb-0">
                                <i class="fas fa-lock me-1"></i> Pago 100% seguro procesado por ePayco
                            </p>
                            <img src="https://multimedia.epayco.co/epayco-landing/btns/epayco-logo-fondo-claro-lite.png" height="25" class="mt-2 grayscale opacity-50">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SDK de ePayco -->
<script type="text/javascript" src="https://checkout.epayco.co/checkout.js"></script>

<script>
    const handler = ePayco.checkout.configure({
        key: '<?= EPAYCO_PUBLIC_KEY ?>',
        test: <?= EPAYCO_TESTING ? 'true' : 'false' ?>
    });

    const btnPay = document.getElementById('btnPay');

    btnPay.addEventListener('click', function() {
        const data = {
            // Parámetros obligatorios
            name: "PharmaCRM - Pedido <?= $data['invoice'] ?>",
            description: "Pago de medicamentos en PharmaCRM",
            invoice: "<?= $data['invoice'] ?>",
            currency: "cop",
            amount: "<?= $data['total'] ?>",
            tax_base: "0",
            tax: "0",
            country: "co",
            lang: "es",

            // Onboarding
            external: "false",

            // Atributos opcionales
            extra1: "<?= session_id() ?>",
            confirmation: "<?= APP_URL ?>?route=checkout&action=confirmation",
            response: "<?= APP_URL ?>?route=checkout&action=response",

            // Datos del pagador (Opcional)
            name_billing: "<?= $_SESSION['user_full_name'] ?? '' ?>",
            address_billing: "",
            type_doc_billing: "cc",
            mobile_billing: "",
            number_doc_billing: ""
        };

        handler.open(data);
    });
</script>

<style>
.checkout-page { background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%); min-height: calc(100vh - 100px); }
.grayscale { filter: grayscale(100%); }
</style>

