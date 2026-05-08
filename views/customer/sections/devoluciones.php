<?php /** Seccion Devoluciones - RF-04 */ ?>
<div class="flex-between mb-2">
    <h3 class="card-title"><i class="fas fa-rotate-left text-danger"></i> Mis Devoluciones</h3>
    <?php if (!empty($data['eligibleOrders'])): ?>
    <button class="btn btn-danger btn-sm" onclick="document.getElementById('modalDevolucion').classList.add('active')">
        <i class="fas fa-plus"></i> Solicitar Devolucion
    </button>
    <?php endif; ?>
</div>

<?php if (empty($data['misDevoluciones'])): ?>
<div class="card">
    <div class="empty-state-customer">
        <i class="fas fa-rotate-left"></i>
        <h3>Sin devoluciones</h3>
        <p>Aqui podras ver el estado de tus solicitudes de devolucion.</p>
    </div>
</div>
<?php else: ?>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Factura</th><th>Producto</th><th>Cant.</th><th>Motivo</th><th>Estado</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php foreach ($data['misDevoluciones'] as $d): ?>
            <tr>
                <td><span class="badge badge-secondary"><?= $d['invoice_number'] ?></span></td>
                <td><?= htmlspecialchars($d['product_name']) ?></td>
                <td><?= $d['quantity'] ?></td>
                <td><small><?= ucfirst($d['motivo']) ?></small></td>
                <td><span class="estado-badge estado-<?= $d['estado'] ?>"><?= ucfirst(str_replace('_',' ',$d['estado'])) ?></span></td>
                <td class="text-muted"><?= date('d/m/Y', strtotime($d['created_at'])) ?></td>
            </tr>
            <?php if ($d['observacion_admin']): ?>
            <tr><td colspan="6" style="background:var(--bg-surface);font-size:12px;padding:8px 16px">
                <i class="fas fa-comment-dots text-info"></i> <strong>Admin:</strong> <?= htmlspecialchars($d['observacion_admin']) ?>
            </td></tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Modal Nueva Devolucion -->
<div class="modal-overlay" id="modalDevolucion">
<div class="modal" style="max-width:600px">
    <div class="modal-header">
        <h2>Solicitar Devolucion</h2>
        <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>?route=customer-dashboard&action=createDevolucion&client_id=<?= $data['profile']['id'] ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>Selecciona la Factura</label>
            <select name="sale_id" class="form-control" required onchange="loadOrderItems(this.value)">
                <option value="">Seleccione una factura reciente...</option>
                <?php foreach ($data['eligibleOrders'] as $o): ?>
                <option value="<?= $o['id'] ?>">Factura <?= $o['invoice_number'] ?> - <?= date('d/m/Y', strtotime($o['created_at'])) ?> (<?= formatCurrency($o['total']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group" id="itemsContainer" style="display:none">
            <label>Selecciona el Producto</label>
            <select name="sale_item_id" id="itemSelect" class="form-control" required>
                <option value="">Seleccione producto...</option>
            </select>
        </div>
        <div class="form-group">
            <label>Motivo de la devolucion</label>
            <select name="motivo" class="form-control" required>
                <?php foreach (Devolucion::getMotivos() as $k => $v): ?>
                <option value="<?= $k ?>"><?= $v ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Descripcion/Detalles</label>
            <textarea name="descripcion" class="form-control" rows="2" placeholder="Explique el problema con el producto..."></textarea>
        </div>
        <div class="form-group">
            <label>Evidencia Fotografica (Opcional)</label>
            <input type="file" name="evidencia" class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-rotate-left"></i> Enviar Solicitud</button>
    </form>
</div>
</div>

<script>
function loadOrderItems(saleId) {
    if (!saleId) {
        document.getElementById('itemsContainer').style.display = 'none';
        return;
    }
    // En una app real, esto seria una llamada AJAX. 
    // Para el demo, lo simulamos o podriamos pasar los datos en JSON al cargar la pagina.
    fetch('<?= APP_URL ?>?route=api&entity=order_items&id=' + saleId)
        .then(response => response.json())
        .then(items => {
            const select = document.getElementById('itemSelect');
            select.innerHTML = '<option value="">Seleccione producto...</option>';
            items.forEach(item => {
                const opt = document.createElement('option');
                opt.value = item.id;
                opt.textContent = `${item.product_name} - ${item.quantity} und. (${item.unit_price})`;
                select.appendChild(opt);
            });
            document.getElementById('itemsContainer').style.display = 'block';
        });
}
</script>
