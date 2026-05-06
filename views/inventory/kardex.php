<div class="kardex-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-history text-primary"></i> Kardex de Inventario
            </h2>
            <?php if ($data['product']): ?>
                <p class="text-muted">
                    Historial de movimientos para: <strong><?= htmlspecialchars($data['product']['name']) ?></strong> 
                    (<?= $data['product']['barcode'] ?>)
                </p>
            <?php else: ?>
                <p class="text-muted">Historial general de todos los movimientos de stock</p>
            <?php endif; ?>
        </div>
        <div>
            <a href="<?= APP_URL ?>?route=inventory" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Volver al Inventario
            </a>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Lote</th>
                    <th>Tipo</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-center">Saldo</th>
                    <th>Referencia</th>
                    <th>Usuario</th>
                    <th>Notas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['movements'] as $m): ?>
                    <tr>
                        <td class="small">
                            <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                        </td>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($m['product_name']) ?></div>
                        </td>
                        <td>
                            <code class="text-xs"><?= htmlspecialchars($m['lot_number'] ?? 'N/A') ?></code>
                        </td>
                        <td>
                            <?php if ($m['type'] === 'ENTRY'): ?>
                                <span class="badge badge-success-light">
                                    <i class="fas fa-arrow-down"></i> ENTRADA
                                </span>
                            <?php elseif ($m['type'] === 'EXIT'): ?>
                                <span class="badge badge-danger-light">
                                    <i class="fas fa-arrow-up"></i> SALIDA
                                </span>
                            <?php else: ?>
                                <span class="badge badge-info-light">
                                    <i class="fas fa-sync"></i> AJUSTE
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-bold">
                            <?= ($m['type'] === 'EXIT' ? '-' : '+') . $m['quantity'] ?>
                        </td>
                        <td class="text-center">
                            <strong><?= $m['balance_after'] ?></strong>
                        </td>
                        <td>
                            <small class="text-muted">
                                <?= $m['reference_type'] ?> #<?= $m['reference_id'] ?? 'N/A' ?>
                            </small>
                        </td>
                        <td>
                            <small><?= htmlspecialchars($m['username']) ?></small>
                        </td>
                        <td class="small text-muted">
                            <?= htmlspecialchars($m['notes'] ?? '') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($data['movements'])): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-scroll fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No se encontraron movimientos registrados.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.badge-success-light { background: #dcfce7; color: #166534; }
.badge-danger-light { background: #fee2e2; color: #991b1b; }
.badge-info-light { background: #e0f2fe; color: #075985; }
.text-xs { font-size: 0.75rem; }
</style>

