<div class="reports-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2 class="mb-1"><i class="fas fa-chart-line text-primary"></i> Reportes y Analítica</h2>
            <p class="text-muted mb-0">Visión general del desempeño del negocio</p>
        </div>
        <div class="report-filters card p-3">
            <form action="<?= APP_URL ?>" method="GET" class="d-flex align-items-center gap-2">
                <input type="hidden" name="route" value="reports">
                <div class="d-flex align-items-center gap-2">
                    <label for="date_from" class="small text-muted mb-0">Desde:</label>
                    <input type="date" name="date_from" id="date_from" class="form-control form-control-sm" value="<?= $data['dateFrom'] ?>">
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="date_to" class="small text-muted mb-0">Hasta:</label>
                    <input type="date" name="date_to" id="date_to" class="form-control form-control-sm" value="<?= $data['dateTo'] ?>">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync"></i> Actualizar
                </button>
            </form>
        </div>
    </div>
</div>

<!-- KPI Row -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-primary-light text-primary"><i class="fas fa-receipt"></i></div>
            <div class="stat-info">
                <h3><?= number_format($data['summary']['total_sales']) ?></h3>
                <span>Ventas Totales</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-success-light text-success"><i class="fas fa-dollar-sign"></i></div>
            <div class="stat-info">
                <h3>$<?= number_format($data['summary']['total_revenue'], 2) ?></h3>
                <span>Ingresos Netos</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-info-light text-info"><i class="fas fa-shopping-basket"></i></div>
            <div class="stat-info">
                <h3>$<?= number_format($data['summary']['avg_sale'], 2) ?></h3>
                <span>Ticket Promedio</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <div class="stat-icon bg-warning-light text-warning"><i class="fas fa-percent"></i></div>
            <div class="stat-info">
                <h3>$<?= number_format($data['summary']['total_discounts'], 2) ?></h3>
                <span>Descuentos</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <!-- Sales Chart -->
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Ventas Diarias</h5>
                <i class="fas fa-info-circle text-muted" title="Ventas completadas en el periodo seleccionado"></i>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="300"></canvas>
            </div>
        </div>
    </div>
    <!-- Expiration Summary -->
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Alertas de Vencimiento</h5>
            </div>
            <div class="card-body">
                <div class="expiration-list">
                    <div class="exp-item d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="dot bg-danger"></div>
                            <span>Ya vencidos</span>
                        </div>
                        <span class="badge badge-danger"><?= $data['expirationReport']['expired'] ?></span>
                    </div>
                    <div class="exp-item d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="dot bg-warning"></div>
                            <span>Vence en 7 días</span>
                        </div>
                        <span class="badge badge-warning"><?= $data['expirationReport']['exp_7'] ?></span>
                    </div>
                    <div class="exp-item d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <div class="dot bg-info"></div>
                            <span>Vence en 15 días</span>
                        </div>
                        <span class="badge badge-info"><?= $data['expirationReport']['exp_15'] ?></span>
                    </div>
                    <div class="exp-item d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <div class="dot bg-secondary"></div>
                            <span>Vence en 30 días</span>
                        </div>
                        <span class="badge badge-secondary"><?= $data['expirationReport']['exp_30'] ?></span>
                    </div>
                </div>
                <hr>
                <div class="text-center mt-3">
                    <a href="<?= APP_URL ?>?route=inventory&action=expiring" class="btn btn-outline-primary btn-sm w-100">Ver Productos Críticos</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Top Products -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Productos más vendidos</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Producto</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-right">Ingresos</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['topProducts'] as $p): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($p['category_name']) ?></small>
                                    </td>
                                    <td class="text-center"><?= $p['total_quantity'] ?></td>
                                    <td class="text-right">$<?= number_format($p['total_revenue'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- Inventory Value by Category -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header bg-white">
                <h5 class="mb-0">Valorización de Inventario</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th class="text-center">Stock</th>
                                <th class="text-right">Valor Venta</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($data['inventoryValue'] as $v): ?>
                                <tr>
                                    <td>
                                        <i class="fas fa-circle" style="color: <?= $v['color'] ?>; font-size: 8px;"></i>
                                        <?= htmlspecialchars($v['name']) ?>
                                    </td>
                                    <td class="text-center"><?= $v['total_stock'] ?></td>
                                    <td class="text-right">$<?= number_format($v['sale_value'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    const salesData = <?= json_encode($data['salesByDay']) ?>;
    const labels = salesData.map(d => d.date);
    const totals = salesData.map(d => d.total);
    const counts = salesData.map(d => d.count);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Ingresos ($)',
                data: totals,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                yAxisID: 'y'
            }, {
                label: 'Ventas (#)',
                data: counts,
                borderColor: '#10b981',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [5, 5],
                fill: false,
                tension: 0.4,
                yAxisID: 'y1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: value => '$' + value.toLocaleString()
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    beginAtZero: true,
                    grid: { drawOnChartArea: false }
                }
            },
            plugins: {
                legend: { position: 'top' }
            }
        }
    });
});
</script>

<style>
.stat-card {
    background: var(--bg-card);
    padding: 20px;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 15px;
    height: 100%;
}
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}
.stat-info h3 { font-size: 1.5rem; margin-bottom: 0; font-weight: 700; }
.stat-info span { font-size: 0.85rem; color: var(--text-muted); }

.bg-primary-light { background: #dbeafe; }
.bg-success-light { background: #dcfce7; }
.bg-info-light { background: #e0f2fe; }
.bg-warning-light { background: #fef3c7; }

.dot { width: 10px; height: 10px; border-radius: 50%; }
.text-right { text-align: right; }
</style>
