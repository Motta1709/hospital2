<?php /** Seccion Reservas - RF-06 */ ?>
<div class="flex-between mb-2">
    <h3 class="card-title"><i class="fas fa-bookmark" style="color:var(--customer-purple)"></i> Mis Reservas</h3>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalReserva').classList.add('active')">
        <i class="fas fa-plus"></i> Nueva Reserva
    </button>
</div>

<?php if (empty($data['reservas'])): ?>
<div class="card"><div class="empty-state-customer"><i class="fas fa-bookmark"></i><h3>Sin reservas</h3><p>No tienes reservas de medicamentos.</p></div></div>
<?php else: ?>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead><tr><th>Medicamento</th><th>Presentacion</th><th>Cant.</th><th>Prescripcion</th><th>Estado</th><th>Fecha</th></tr></thead>
            <tbody>
            <?php foreach ($data['reservas'] as $r): ?>
            <tr>
                <td>
                    <div style="font-weight:600"><?= htmlspecialchars($r['product_name']) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($r['generic_name'] ?? '') ?></small>
                </td>
                <td><?= htmlspecialchars($r['presentation'] ?? '') ?> <?= htmlspecialchars($r['concentration'] ?? '') ?></td>
                <td><span class="badge badge-info"><?= $r['cantidad'] ?></span></td>
                <td>
                    <?php if ($r['requires_prescription']): ?>
                        <?php if ($r['tiene_prescripcion']): ?>
                            <span class="badge badge-success" title="Documento cargado"><i class="fas fa-file-medical"></i> Si</span>
                        <?php else: ?>
                            <span class="badge badge-danger" title="Pendiente de carga"><i class="fas fa-exclamation"></i> Requerida</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="text-muted">No req.</span>
                    <?php endif; ?>
                </td>
                <td><span class="estado-badge estado-<?= $r['estado'] ?>"><?= ucfirst($r['estado']) ?></span></td>
                <td class="text-muted"><?= date('d/m/Y', strtotime($r['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Modal Nueva Reserva -->
<div class="modal-overlay" id="modalReserva">
<div class="modal">
    <div class="modal-header">
        <h2>Reservar Medicamento</h2>
        <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/?route=customer-dashboard&action=createReserva&client_id=<?= $data['profile']['id'] ?>" enctype="multipart/form-data">
        <div class="form-group">
            <label>Medicamento a reservar</label>
            <select name="product_id" class="form-control" required onchange="checkPrescription(this)">
                <option value="">Seleccione un medicamento...</option>
                <?php foreach ($data['availableProducts'] as $p): ?>
                <option value="<?= $p['id'] ?>" data-req-presc="<?= $p['requires_prescription'] ?>">
                    <?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['presentation']) ?>) - <?= formatCurrency($p['sale_price']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <small class="text-muted">Mostrando productos proximos a agotarse o agotados.</small>
        </div>
        <div class="form-group">
            <label>Cantidad</label>
            <input type="number" name="cantidad" class="form-control" value="1" min="1" max="10">
        </div>
        <div id="prescriptionField" style="display:none" class="form-group">
            <label class="text-danger"><i class="fas fa-file-prescription"></i> Adjuntar Prescripcion Medica (PDF/Imagen)</label>
            <input type="file" name="prescripcion" class="form-control" accept="image/*,.pdf">
            <small class="text-muted">Este medicamento es de venta bajo formula medica obligatoria.</small>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-bookmark"></i> Confirmar Reserva</button>
    </form>
</div>
</div>

<script>
function checkPrescription(select) {
    const selected = select.options[select.selectedIndex];
    const req = selected.getAttribute('data-req-presc') == '1';
    document.getElementById('prescriptionField').style.display = req ? 'block' : 'none';
    const fileInput = document.querySelector('input[name="prescripcion"]');
    if (req) fileInput.setAttribute('required', 'required');
    else fileInput.removeAttribute('required');
}
</script>
