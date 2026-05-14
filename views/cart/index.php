<div class="cart-page py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h1 class="h2 mb-1">Mi Carrito</h1>
                <p class="text-muted">Gestiona tus productos y procede al pago seguro</p>
            </div>
            <a href="<?= APP_URL ?>?route=customer-dashboard&section=tienda" class="btn btn-link text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i> Continuar comprando
            </a>
        </div>

        <?php if (empty($data['cart'])): ?>
            <div class="card border-0 shadow-sm py-5 text-center">
                <div class="card-body">
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart fa-4x text-muted opacity-25"></i>
                    </div>
                    <h3>Tu carrito está vacío</h3>
                    <p class="text-muted mb-4">Parece que aún no has añadido ningún producto farmacéutico.</p>
                    <a href="<?= APP_URL ?>?route=customer-dashboard&section=tienda" class="btn btn-primary px-5 py-3 rounded-pill">
                        Ver Catálogo de Productos
                    </a>
                </div>
            </div>
        <?php else: ?>
            <form action="<?= APP_URL ?>/?route=cart&action=update" method="POST" id="cartForm">
                <div class="row g-4">
                    <!-- Lista de Productos -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm overflow-hidden">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="ps-4 py-3" style="width: 50px;">
                                                <input type="checkbox" class="form-check-input" id="selectAll" checked>
                                            </th>
                                            <th class="py-3">Producto</th>
                                            <th class="py-3 text-center">Cantidad</th>
                                            <th class="py-3 text-end">Precio</th>
                                            <th class="py-3 text-end pe-4">Subtotal</th>
                                            <th class="py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['cart'] as $item): ?>
                                            <tr class="<?= !$item['selected'] ? 'opacity-50' : '' ?>">
                                                <td class="ps-4">
                                                    <input type="checkbox" 
                                                           name="selected[<?= $item['id'] ?>]" 
                                                           value="1" 
                                                           class="form-check-input item-checkbox" 
                                                           <?= $item['selected'] ? 'checked' : '' ?>
                                                           onchange="updateCart()">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center py-2">
                                                        <div class="product-icon me-3 bg-light rounded-3 p-2 text-primary">
                                                            <i class="fas fa-pills fa-lg"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold"><?= htmlspecialchars($item['name']) ?></div>
                                                            <small class="text-muted"><?= htmlspecialchars($item['presentation']) ?></small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group input-group-sm mx-auto" style="width: 100px;">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(<?= $item['id'] ?>, -1)">-</button>
                                                        <input type="text" 
                                                               name="qtys[<?= $item['id'] ?>]" 
                                                               id="qty_<?= $item['id'] ?>"
                                                               class="form-control text-center" 
                                                               value="<?= $item['qty'] ?>" 
                                                               readonly>
                                                        <button class="btn btn-outline-secondary" type="button" onclick="changeQty(<?= $item['id'] ?>, 1)">+</button>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <?= formatCurrency($item['sale_price']) ?>
                                                </td>
                                                <td class="text-end fw-bold pe-4">
                                                    <?= formatCurrency($item['subtotal']) ?>
                                                </td>
                                                <td class="pe-3">
                                                    <a href="<?= APP_URL ?>?route=cart&action=remove&id=<?= $item['id'] ?>" class="text-danger opacity-50 hover-opacity-100">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm sticky-top" style="top: 100px;">
                            <div class="card-body p-4">
                                <h3 class="h5 mb-4">Resumen del Pedido</h3>
                                
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Subtotal (Todos)</span>
                                    <span><?= formatCurrency($data['total']) ?></span>
                                </div>
                                
                                <div class="d-flex justify-content-between mb-4 pb-4 border-bottom">
                                    <span class="text-muted">Descuentos</span>
                                    <span class="text-success">-$ 0</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="fw-bold h5 mb-0">Total a Pagar</span>
                                    <span class="fw-bold h4 mb-0 text-primary" id="selectedTotal">
                                        <?= formatCurrency($data['selectedTotal']) ?>
                                    </span>
                                </div>

                                <?php if ($data['selectedTotal'] > 0): ?>
                                    <button type="button" onclick="goToCheckout()" class="btn btn-primary w-100 py-3 rounded-3 shadow-sm mb-3">
                                        <i class="fas fa-shield-alt me-2"></i> Proceder al Pago Seguro
                                    </button>
                                <?php else: ?>
                                    <div class="alert alert-warning small py-2 text-center">
                                        Selecciona al menos un producto para pagar
                                    </div>
                                <?php endif; ?>

                                <div class="text-center mt-3">
                                    <img src="https://multimedia.epayco.co/epayco-landing/btns/epayco-logo-fondo-claro-lite.png" height="30" alt="ePayco Secure" class="opacity-75">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
function changeQty(id, delta) {
    const input = document.getElementById('qty_' + id);
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    input.value = val;
    updateCart();
}

function updateCart() {
    document.getElementById('cartForm').submit();
}

function goToCheckout() {
    window.location.href = '<?= APP_URL ?>?route=checkout';
}

// Select/Deselect All
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.item-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateCart();
});
</script>

<style>
.cart-page { background-color: #f8fafc; min-height: calc(100vh - 200px); }
.product-icon { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
.hover-opacity-100:hover { opacity: 1 !important; }
</style>

