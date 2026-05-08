<?php /** Seccion Domicilios - RF-05 */ ?>
<div class="card-header mb-2">
    <h3 class="card-title"><i class="fas fa-truck text-info"></i> Mis Domicilios</h3>
</div>

<?php if (empty($data['domicilios'])): ?>
<div class="card"><div class="empty-state-customer"><i class="fas fa-truck"></i><h3>Sin domicilios</h3><p>No tienes pedidos a domicilio registrados.</p></div></div>
<?php else: ?>
<?php
$trackingStates = ['pendiente','confirmado','despachado','entregado'];
$trackingIcons = ['fa-clock','fa-check','fa-truck','fa-check-double'];
foreach ($data['domicilios'] as $dom):
    $currentIdx = array_search($dom['estado'], $trackingStates);
    if ($currentIdx === false) $currentIdx = -1;
?>
<div class="card mb-2">
    <div class="flex-between">
        <div>
            <strong><?= $dom['invoice_number'] ?? 'Pedido #'.$dom['id'] ?></strong>
            <span class="estado-badge estado-<?= $dom['estado'] ?>" style="margin-left:8px"><?= ucfirst($dom['estado']) ?></span>
        </div>
        <span class="text-muted" style="font-size:12px"><?= date('d/m/Y', strtotime($dom['created_at'])) ?></span>
    </div>
    
    <!-- Tracking Bar -->
    <div class="tracking-bar">
    <?php foreach ($trackingStates as $i => $state): 
        $class = $i < $currentIdx ? 'completed' : ($i === $currentIdx ? 'active' : '');
    ?>
        <div class="tracking-step <?= $class ?>">
            <div class="tracking-dot"><i class="fas <?= $trackingIcons[$i] ?>"></i></div>
            <span class="tracking-label"><?= ucfirst($state) ?></span>
        </div>
    <?php endforeach; ?>
    </div>

    <div style="font-size:13px;color:var(--text-secondary)">
        <i class="fas fa-location-dot"></i> <?= htmlspecialchars($dom['address_line']) ?>, <?= htmlspecialchars($dom['city']) ?>
        <span class="address-tag tag-<?= $dom['address_label'] ?>" style="margin-left:8px"><?= ucfirst($dom['address_label']) ?></span>
    </div>
    <?php if ($dom['tipo_entrega'] === 'programada' && $dom['fecha_programada']): ?>
    <div style="font-size:12px;color:var(--text-muted);margin-top:6px">
        <i class="fas fa-calendar"></i> Programado: <?= date('d/m/Y', strtotime($dom['fecha_programada'])) ?>
        <?php if ($dom['franja_horaria']): ?> - <?= ucfirst($dom['franja_horaria']) ?><?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
<?php endif; ?>
