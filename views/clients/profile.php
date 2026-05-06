<?php 
/**
 * Vista: Perfil Detallado del Cliente
 * ----------------------------------
 * Muestra información personal, historial de medicamentos, compras y puntos.
 */
$client = $data['client']; 
?>

<!-- Cabecera del Perfil -->
<div class="profile-header card mb-4 p-4">
    <div class="d-flex align-items-center gap-4">
        <div class="profile-avatar-large">
            <?= strtoupper(substr($client['first_name'], 0, 1) . substr($client['last_name'], 0, 1)) ?>
        </div>
        
        <div class="profile-info flex-grow-1">
            <h2 class="mb-1"><?= htmlspecialchars($client['first_name'] . ' ' . $client['last_name']) ?></h2>
            <p class="text-muted mb-2">
                <i class="fas fa-id-card"></i> <?= $client['document_type'] ?> <?= $client['document_number'] ?> 
                <span class="mx-2">|</span>
                <i class="fas fa-phone"></i> <?= $client['phone'] ?? 'Sin teléfono' ?>
                <span class="mx-2">|</span>
                <i class="fas fa-map-marker-alt"></i> <?= $client['city'] ?? 'Sin ciudad' ?>
            </p>
            
            <?php if ($client['allergies']): ?>
                <div class="alert alert-danger d-inline-block py-1 px-3 mb-3">
                    <i class="fas fa-exclamation-triangle"></i> 
                    <strong>Alergias:</strong> <?= htmlspecialchars($client['allergies']) ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas Rápidas -->
            <div class="profile-stats d-flex gap-4 mt-2">
                <div class="profile-stat">
                    <div class="stat-value text-accent"><?= formatCurrency($client['total_purchases']) ?></div>
                    <div class="stat-label">Total Compras</div>
                </div>
                <div class="profile-stat">
                    <div class="stat-value text-primary"><?= $client['visit_count'] ?></div>
                    <div class="stat-label">Visitas Realizadas</div>
                </div>
                <div class="profile-stat">
                    <div class="stat-value text-warning"><?= $client['loyalty_points'] ?></div>
                    <div class="stat-label">Puntos Acumulados</div>
                </div>
            </div>
        </div>
        
        <div class="profile-actions align-self-start">
            <a href="<?= APP_URL ?>?route=clients&action=edit&id=<?= $client['id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-edit"></i> Editar Perfil
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Columna Izquierda: Medicamentos Frecuentes -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-pills text-primary"></i> Medicamentos Frecuentes
                </h3>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Última Compra</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['medications'] as $m): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($m['name']) ?></strong><br>
                                    <small class="text-muted"><?= $m['presentation'] ?> <?= $m['concentration'] ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= $m['total_purchased'] ?></span>
                                </td>
                                <td class="text-right text-muted">
                                    <?= date('d/m/Y', strtotime($m['last_purchase'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($data['medications'])): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    No hay historial de medicamentos para este cliente.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Historial de Compras -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-receipt text-success"></i> Historial de Ventas
                </h3>
            </div>
            <div class="table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Total</th>
                            <th class="text-right">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['purchases'] as $p): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-primary"><?= $p['invoice_number'] ?></span>
                                </td>
                                <td class="font-weight-bold text-accent">
                                    <?= formatCurrency($p['total']) ?>
                                </td>
                                <td class="text-right text-muted">
                                    <?= date('d/m/Y H:i', strtotime($p['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($data['purchases'])): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    No se han registrado ventas para este cliente.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Sección Inferior: Movimientos de Lealtad -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-star text-warning"></i> Programa de Lealtad: Movimientos de Puntos
        </h3>
    </div>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Puntos</th>
                    <th>Descripción</th>
                    <th>Saldo Posterior</th>
                    <th class="text-right">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['loyaltyTransactions'] as $lt): ?>
                    <?php 
                    $isEarned = in_array($lt['type'], ['earned', 'bonus']);
                    $badgeClass = $isEarned ? 'badge-success' : 'badge-warning';
                    $sign = $isEarned ? '+' : '-';
                    $colorClass = $isEarned ? 'text-success' : 'text-warning';
                    ?>
                    <tr>
                        <td>
                            <span class="badge <?= $badgeClass ?>">
                                <?= ucfirst($lt['type']) ?>
                            </span>
                        </td>
                        <td class="font-weight-bold <?= $colorClass ?>">
                            <?= $sign ?><?= $lt['points'] ?>
                        </td>
                        <td><?= htmlspecialchars($lt['description']) ?></td>
                        <td class="text-muted"><?= $lt['balance_after'] ?> pts</td>
                        <td class="text-right text-muted">
                            <?= date('d/m/Y', strtotime($lt['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                
                <?php if (empty($data['loyaltyTransactions'])): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            No hay movimientos de puntos registrados.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

