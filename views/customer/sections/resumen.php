<?php /** Seccion Resumen - KPIs y actividad reciente */ ?>
<!-- Banner de Bienvenida -->
<div class="welcome-banner">
    <h2>Bienvenido, <?= htmlspecialchars($data['profile']['first_name']) ?></h2>
    <p>Aqui tienes un resumen de tu actividad en PharmaCRM</p>
    <div class="welcome-stats">
        <div class="welcome-stat">
            <span class="stat-value"><?= number_format($data['summary']['puntos']) ?></span>
            <span class="stat-label">Puntos</span>
        </div>
        <div class="welcome-stat">
            <span class="stat-value"><?= $data['summary']['compras']['total_compras'] ?></span>
            <span class="stat-label">Compras</span>
        </div>
        <div class="welcome-stat">
            <span class="stat-value"><?= formatCurrency($data['summary']['compras']['total_gastado']) ?></span>
            <span class="stat-label">Total Gastado</span>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="customer-kpi-grid">
    <a href="<?= APP_URL ?>?route=customer-dashboard&section=compras" class="customer-kpi kpi-compras">
        <div class="kpi-icon-sm"><i class="fas fa-shopping-bag"></i></div>
        <div class="kpi-data">
            <h4>Compras</h4>
            <div class="kpi-number"><?= $data['summary']['compras']['total_compras'] ?></div>
        </div>
    </a>
    <a href="<?= APP_URL ?>?route=customer-dashboard&section=perfil" class="customer-kpi kpi-puntos">
        <div class="kpi-icon-sm"><i class="fas fa-star"></i></div>
        <div class="kpi-data">
            <h4>Puntos</h4>
            <div class="kpi-number"><?= number_format($data['summary']['puntos']) ?></div>
        </div>
    </a>
    <a href="<?= APP_URL ?>?route=customer-dashboard&section=pqrsf" class="customer-kpi kpi-pqrsf">
        <div class="kpi-icon-sm"><i class="fas fa-headset"></i></div>
        <div class="kpi-data">
            <h4>PQRSF Abiertas</h4>
            <div class="kpi-number"><?= $data['summary']['pqrsf_abiertas'] ?></div>
        </div>
    </a>
    <a href="<?= APP_URL ?>?route=customer-dashboard&section=reservas" class="customer-kpi kpi-reservas">
        <div class="kpi-icon-sm"><i class="fas fa-bookmark"></i></div>
        <div class="kpi-data">
            <h4>Reservas</h4>
            <div class="kpi-number"><?= $data['summary']['reservas_pendientes'] ?></div>
        </div>
    </a>
    <a href="<?= APP_URL ?>?route=customer-dashboard&section=domicilios" class="customer-kpi kpi-domicilios">
        <div class="kpi-icon-sm"><i class="fas fa-truck"></i></div>
        <div class="kpi-data">
            <h4>Domicilios</h4>
            <div class="kpi-number"><?= $data['summary']['domicilios_activos'] ?></div>
        </div>
    </a>
    <a href="<?= APP_URL ?>?route=customer-dashboard&section=notificaciones" class="customer-kpi kpi-devoluciones">
        <div class="kpi-icon-sm"><i class="fas fa-bell"></i></div>
        <div class="kpi-data">
            <h4>Notificaciones</h4>
            <div class="kpi-number"><?= $data['summary']['notificaciones'] ?></div>
        </div>
    </a>
</div>

<!-- Grids: Compras Recientes + Notificaciones -->
<div class="content-grid">
    <!-- Ultimas Compras -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-clock-rotate-left text-info"></i> Compras Recientes</h3>
            <a href="<?= APP_URL ?>?route=customer-dashboard&section=compras" class="btn btn-sm btn-secondary">Ver todas</a>
        </div>
        <?php if (empty($data['recentOrders'])): ?>
            <div class="empty-state-customer"><i class="fas fa-shopping-bag"></i><h3>Sin compras</h3><p>Aun no has realizado compras.</p></div>
        <?php else: ?>
        <div class="table-wrapper">
            <table>
                <thead><tr><th>Factura</th><th>Total</th><th>Items</th><th>Fecha</th></tr></thead>
                <tbody>
                <?php foreach ($data['recentOrders'] as $o): ?>
                <tr>
                    <td><span class="badge badge-primary"><?= $o['invoice_number'] ?></span></td>
                    <td style="font-weight:700;color:var(--customer-primary)"><?= formatCurrency($o['total']) ?></td>
                    <td><?= $o['total_items'] ?> items</td>
                    <td class="text-muted"><?= date('d/m/Y', strtotime($o['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Notificaciones Recientes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-bell text-warning"></i> Notificaciones</h3>
            <?php if ($data['unreadCount'] > 0): ?>
            <span class="badge badge-warning"><?= $data['unreadCount'] ?> nuevas</span>
            <?php endif; ?>
        </div>
        <?php if (empty($data['notifications'])): ?>
            <div class="empty-state-customer"><i class="fas fa-bell-slash"></i><h3>Sin notificaciones</h3><p>No tienes notificaciones.</p></div>
        <?php else: ?>
        <div class="notif-list">
            <?php
            $eventIcons = Notificacion::getEventIcons();
            foreach (array_slice($data['notifications'], 0, 5) as $n):
                $icon = $eventIcons[$n['evento']] ?? ['icon'=>'fa-bell','color'=>'info'];
            ?>
            <div class="notif-item <?= !$n['leida'] ? 'unread' : '' ?>">
                <div class="notif-icon notif-<?= $icon['color'] ?>"><i class="fas <?= $icon['icon'] ?>"></i></div>
                <div class="notif-content">
                    <h4><?= htmlspecialchars($n['titulo']) ?></h4>
                    <p><?= htmlspecialchars(mb_substr($n['mensaje'], 0, 80)) ?>...</p>
                    <div class="notif-time"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($n['created_at'])) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Grafico de Gasto Mensual -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-chart-area text-primary"></i> Gasto Mensual</h3>
    </div>
    <div class="mini-chart">
        <canvas id="spendingChart"></canvas>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const monthlyData = <?= json_encode($data['monthlySpending']) ?>;
    const canvas = document.getElementById('spendingChart');
    if (!canvas || !monthlyData.length) return;
    const ctx = canvas.getContext('2d');
    const rect = canvas.parentElement.getBoundingClientRect();
    canvas.width = rect.width; canvas.height = rect.height;
    const w = canvas.width, h = canvas.height;
    const pad = {top:20,right:20,bottom:35,left:70};
    const vals = monthlyData.map(d => parseFloat(d.total_gastado));
    const maxV = Math.max(...vals) * 1.15 || 1;
    const cW = w - pad.left - pad.right, cH = h - pad.top - pad.bottom;
    const stepX = cW / (vals.length - 1 || 1);

    // Grid
    ctx.strokeStyle = 'rgba(0,0,0,.05)'; ctx.lineWidth = 1;
    for (let i = 0; i <= 4; i++) {
        const y = pad.top + (cH/4)*i;
        ctx.beginPath(); ctx.moveTo(pad.left, y); ctx.lineTo(w-pad.right, y); ctx.stroke();
        ctx.fillStyle = '#9CA3AF'; ctx.font = '11px Inter'; ctx.textAlign = 'right';
        ctx.fillText('$'+Math.round((maxV-(maxV/4)*i)/1000)+'k', pad.left-10, y+4);
    }

    // Area
    const grad = ctx.createLinearGradient(0, pad.top, 0, h-pad.bottom);
    grad.addColorStop(0, 'rgba(37,99,235,.2)'); grad.addColorStop(1, 'rgba(37,99,235,0)');
    ctx.beginPath(); ctx.moveTo(pad.left, h-pad.bottom);
    vals.forEach((v,i) => ctx.lineTo(pad.left+i*stepX, pad.top+cH*(1-v/maxV)));
    ctx.lineTo(pad.left+(vals.length-1)*stepX, h-pad.bottom); ctx.closePath();
    ctx.fillStyle = grad; ctx.fill();

    // Line
    ctx.beginPath();
    vals.forEach((v,i) => { const x=pad.left+i*stepX, y=pad.top+cH*(1-v/maxV); i===0?ctx.moveTo(x,y):ctx.lineTo(x,y); });
    ctx.strokeStyle = '#2563EB'; ctx.lineWidth = 3; ctx.lineJoin='round'; ctx.lineCap='round'; ctx.stroke();

    // Points + Labels
    vals.forEach((v,i) => {
        const x=pad.left+i*stepX, y=pad.top+cH*(1-v/maxV);
        ctx.beginPath(); ctx.arc(x,y,5,0,Math.PI*2); ctx.fillStyle='#2563EB'; ctx.fill();
        ctx.beginPath(); ctx.arc(x,y,2.5,0,Math.PI*2); ctx.fillStyle='#fff'; ctx.fill();
        ctx.fillStyle='#9CA3AF'; ctx.font='10px Inter'; ctx.textAlign='center';
        ctx.fillText(monthlyData[i].mes, x, h-pad.bottom+18);
    });
});
</script>
