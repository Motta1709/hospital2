<!-- KPI Cards (Indicadores Clave de Desempeño) -->
<div class="kpi-grid mb-4">
    <!-- Ventas Hoy -->
    <div class="kpi-card kpi-sales">
        <div class="kpi-icon">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="kpi-info">
            <h3>Ventas Hoy</h3>
            <div class="kpi-value"><?= number_format($data['todaySales']['count']) ?></div>
            <div class="kpi-change positive">
                <i class="fas fa-arrow-up"></i> 
                <?= formatCurrency($data['todaySales']['total']) ?>
            </div>
        </div>
    </div>

    <!-- Ventas del Mes -->
    <div class="kpi-card kpi-revenue">
        <div class="kpi-icon">
            <i class="fas fa-dollar-sign"></i>
        </div>
        <div class="kpi-info">
            <h3>Ventas del Mes</h3>
            <div class="kpi-value"><?= formatCurrency($data['monthSales']['total']) ?></div>
            <div class="kpi-change positive">
                <i class="fas fa-chart-line"></i> 
                <?= number_format($data['monthSales']['count']) ?> transacciones
            </div>
        </div>
    </div>

    <!-- Total Productos -->
    <div class="kpi-card kpi-products">
        <div class="kpi-icon">
            <i class="fas fa-pills"></i>
        </div>
        <div class="kpi-info">
            <h3>Productos</h3>
            <div class="kpi-value"><?= number_format($data['totalProducts']) ?></div>
            <div class="kpi-change">
                <i class="fas fa-boxes-stacked"></i> 
                <?= formatCurrency($data['stockValue']) ?> valor stock
            </div>
        </div>
    </div>

    <!-- Total Clientes -->
    <div class="kpi-card kpi-clients">
        <div class="kpi-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="kpi-info">
            <h3>Clientes</h3>
            <div class="kpi-value"><?= number_format($data['totalClients']) ?></div>
            <div class="kpi-change positive">
                <i class="fas fa-heart"></i> Programa de fidelización
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y Alertas Críticas -->
<div class="row mb-4">
    <!-- Gráfico de Ventas Semanal -->
    <div class="col-lg-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-chart-area text-primary"></i> Ventas Últimos 7 Días
                </h3>
            </div>
            <div class="card-body">
                <div class="chart-container" style="position: relative; height:300px; width:100%">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de Inventario (Vencimientos y Stock Bajo) -->
    <div class="col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-triangle-exclamation text-warning"></i> Alertas de Inventario
                </h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($data['expiringProducts']) && empty($data['lowStockProducts'])): ?>
                    <div class="empty-state py-5 text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h3>Todo en orden</h3>
                        <p class="text-muted">No hay alertas pendientes de inventario.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush overflow-auto" style="max-height: 300px;">
                        <!-- Productos por Vencer -->
                        <?php 
                        $productModel = new Product();
                        foreach (array_slice($data['expiringProducts'], 0, 8) as $p): 
                            $status = $productModel->getTrafficLightStatus($p);
                        ?>
                            <div class="list-group-item d-flex align-items-center gap-3 py-3">
                                <div class="alert-icon" style="background: rgba(var(--<?= $status['expiration']['color'] ?>-rgb, 150, 150, 150), 0.1)">
                                    <i class="fas fa-clock text-<?= $status['expiration']['color'] ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small"><?= htmlspecialchars($p['name']) ?></div>
                                    <small class="text-muted"><?= $status['expiration']['label'] ?></small>
                                </div>
                                <span class="badge badge-<?= $status['expiration']['color'] ?>">
                                    <?= $p['days_to_expire'] ?>d
                                </span>
                            </div>
                        <?php endforeach; ?>

                        <!-- Stock Bajo -->
                        <?php foreach (array_slice($data['lowStockProducts'], 0, 3) as $p): ?>
                            <div class="list-group-item d-flex align-items-center gap-3 py-3">
                                <div class="alert-icon bg-soft-danger">
                                    <i class="fas fa-box-open text-danger"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small"><?= htmlspecialchars($p['name']) ?></div>
                                    <small class="text-muted">Stock: <?= $p['stock'] ?> / Mín: <?= $p['min_stock'] ?></small>
                                </div>
                                <span class="badge badge-danger">
                                    <?= $p['stock'] ?> uds
                                </span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Ventas Recientes y Top Productos -->
<div class="row">
    <!-- Ventas Recientes -->
    <div class="col-lg-7 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-clock-rotate-left text-info"></i> Últimas Transacciones
                </h3>
                <a href="<?= APP_URL ?>?route=sales" class="btn btn-sm btn-outline-secondary">
                    Ver todas las ventas
                </a>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Factura</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th class="text-right">Hora</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['recentSales'] as $s): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-primary"><?= $s['invoice_number'] ?></span>
                                </td>
                                <td>
                                    <?= $s['client_name'] ?? '<span class="text-muted">Consumidor Final</span>' ?>
                                </td>
                                <td class="font-weight-bold text-accent">
                                    <?= formatCurrency($s['total']) ?>
                                </td>
                                <td class="text-right text-muted">
                                    <?= date('h:i A', strtotime($s['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($data['recentSales'])): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    Aún no se han registrado ventas el día de hoy.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ranking de Productos -->
    <div class="col-lg-5 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-ranking-star text-warning"></i> Productos Más Vendidos
                </h3>
            </div>
            <div class="table-wrapper">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['topProducts'] as $index => $p): ?>
                            <tr>
                                <td>
                                    <span class="text-primary fw-bold me-2">#<?= $index + 1 ?></span>
                                    <?= htmlspecialchars($p['name']) ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?= $p['total_sold'] ?></span>
                                </td>
                                <td class="text-right font-weight-bold text-accent">
                                    <?= formatCurrency($p['total_revenue']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($data['topProducts'])): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    Sin datos de ventas disponibles.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
/**
 * Inicialización del Gráfico de Ventas
 */
document.addEventListener('DOMContentLoaded', function() {
    const salesData = <?= json_encode($data['dailySales']) ?>;
    const canvas = document.getElementById('salesChart');
    
    if (canvas && salesData.length > 0) {
        const ctx = canvas.getContext('2d');
        
        // Ajustar resolución del canvas al contenedor
        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;
        
        const w = canvas.width;
        const h = canvas.height;
        const padding = { top: 20, right: 20, bottom: 40, left: 70 };
        
        const values = salesData.map(d => parseFloat(d.total));
        const maxVal = Math.max(...values) * 1.1 || 1;
        
        const chartW = w - padding.left - padding.right;
        const chartH = h - padding.top - padding.bottom;
        const stepX = chartW / (values.length - 1 || 1);
        
        // --- Dibujar Rejilla (Grid) ---
        ctx.strokeStyle = 'rgba(16, 42, 67, 0.08)'; // Using midnight blue with very low opacity
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = padding.top + (chartH / 4) * i;
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(w - padding.right, y);
            ctx.stroke();
            
            // Etiquetas Eje Y
            ctx.fillStyle = '#64748B';
            ctx.font = '11px Inter';
            ctx.textAlign = 'right';
            const labelValue = maxVal - (maxVal / 4) * i;
            ctx.fillText('$' + Math.round(labelValue / 1000) + 'k', padding.left - 10, y + 4);
        }
        
        // --- Dibujar Área de Gráfico (Gradiente) ---
        const gradient = ctx.createLinearGradient(0, padding.top, 0, h - padding.bottom);
        gradient.addColorStop(0, 'rgba(0, 168, 150, 0.25)'); // Emerald Vital
        gradient.addColorStop(1, 'rgba(0, 168, 150, 0)');
        
        ctx.beginPath();
        ctx.moveTo(padding.left, h - padding.bottom);
        values.forEach((v, i) => {
            const x = padding.left + i * stepX;
            const y = padding.top + chartH * (1 - v / maxVal);
            ctx.lineTo(x, y);
        });
        ctx.lineTo(padding.left + (values.length - 1) * stepX, h - padding.bottom);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();
        
        // --- Dibujar Línea Principal ---
        ctx.beginPath();
        values.forEach((v, i) => {
            const x = padding.left + i * stepX;
            const y = padding.top + chartH * (1 - v / maxVal);
            if (i === 0) ctx.moveTo(x, y);
            else ctx.lineTo(x, y);
        });
        ctx.strokeStyle = '#00A896'; // Emerald Vital
        ctx.lineWidth = 3;
        ctx.lineJoin = 'round';
        ctx.lineCap = 'round';
        ctx.stroke();
        
        // --- Dibujar Puntos y Etiquetas Eje X ---
        values.forEach((v, i) => {
            const x = padding.left + i * stepX;
            const y = padding.top + chartH * (1 - v / maxVal);
            
            // Punto
            ctx.beginPath();
            ctx.arc(x, y, 5, 0, Math.PI * 2);
            ctx.fillStyle = '#00A896'; // Emerald Vital
            ctx.fill();
            
            ctx.beginPath();
            ctx.arc(x, y, 2.5, 0, Math.PI * 2);
            ctx.fillStyle = '#fff';
            ctx.fill();
            
            // Etiqueta Fecha (MM-DD)
            ctx.fillStyle = '#94A3B8';
            ctx.font = '10px Inter';
            ctx.textAlign = 'center';
            const dateLabel = salesData[i].date.substring(5);
            ctx.fillText(dateLabel, x, h - padding.bottom + 20);
        });
    }
});
</script>

