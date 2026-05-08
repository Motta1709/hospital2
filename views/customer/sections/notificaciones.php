<?php /** Seccion Notificaciones - RF-07 */ ?>
<div class="flex-between mb-2">
    <h3 class="card-title"><i class="fas fa-bell text-warning"></i> Notificaciones</h3>
    <?php if ($data['unreadCount'] > 0): ?>
    <a href="<?= APP_URL ?>?route=customer-dashboard&action=markNotificationsRead&client_id=<?= $data['profile']['id'] ?>" class="btn btn-sm btn-secondary">
        <i class="fas fa-check-double"></i> Marcar todas como leidas
    </a>
    <?php endif; ?>
</div>

<?php if (empty($data['notifications'])): ?>
<div class="card"><div class="empty-state-customer"><i class="fas fa-bell-slash"></i><h3>Sin notificaciones</h3><p>No tienes notificaciones.</p></div></div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden">
    <div class="notif-list" style="max-height:600px">
    <?php
    $eventIcons = Notificacion::getEventIcons();
    foreach ($data['notifications'] as $n):
        $icon = $eventIcons[$n['evento']] ?? ['icon'=>'fa-bell','color'=>'info'];
    ?>
    <div class="notif-item <?= !$n['leida'] ? 'unread' : '' ?>">
        <div class="notif-icon notif-<?= $icon['color'] ?>"><i class="fas <?= $icon['icon'] ?>"></i></div>
        <div class="notif-content" style="flex:1">
            <h4><?= htmlspecialchars($n['titulo']) ?></h4>
            <p><?= htmlspecialchars($n['mensaje']) ?></p>
            <div class="notif-time">
                <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?>
                <?php if ($n['estado_envio'] === 'enviado'): ?>
                    <span class="badge badge-success" style="margin-left:6px;font-size:10px">Enviado</span>
                <?php elseif ($n['estado_envio'] === 'fallido'): ?>
                    <span class="badge badge-danger" style="margin-left:6px;font-size:10px">Fallido</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
