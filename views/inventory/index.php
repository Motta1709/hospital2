<!-- Barra de Herramientas de Inventario -->
<div class="toolbar mb-4">
    <div class="d-flex gap-3 align-items-center flex-wrap">
        <!-- Buscador -->
        <div class="search-bar">
            <i class="fas fa-search"></i>
            <input type="text" 
                   id="searchInput" 
                   placeholder="Buscar producto, código o genérico..." 
                   value="<?= htmlspecialchars($data['search'] ?? '') ?>" 
                   onkeyup="if(event.key === 'Enter') searchInventory()">
        </div>
        
        <!-- Filtro por Categoría -->
        <select class="form-control" style="width: auto;" id="categoryFilter" onchange="searchInventory()">
            <option value="">Todas las categorías</option>
            <?php foreach ($data['categories'] as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($data['category'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <!-- Acciones -->
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>?route=inventory&action=create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nuevo Producto
        </a>
    </div>
</div>

<!-- Listado de Productos -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-boxes-stacked"></i> Productos en Inventario (<?= $data['totalProducts'] ?>)
        </h3>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Presentación</th>
                    <th>Precio</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Vencimiento</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['products'] as $p): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                            <small class="text-muted">
                                <?= htmlspecialchars($p['generic_name'] ?? '') ?> 
                                <span class="mx-1">·</span> 
                                <code class="text-xs"><?= $p['barcode'] ?></code>
                            </small>
                        </td>
                        <td>
                            <span class="badge" style="background: <?= $p['category_color'] ?>15; color: <?= $p['category_color'] ?>; border: 1px solid <?= $p['category_color'] ?>30;">
                                <?= htmlspecialchars($p['category_name']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="text-muted small">
                                <?= htmlspecialchars($p['presentation'] ?? '') ?> 
                                <?= $p['concentration'] ?? '' ?>
                            </span>
                        </td>
                        <td class="font-weight-bold text-accent">
                            <?= formatCurrency($p['sale_price']) ?>
                        </td>
                        <td class="text-center">
                            <?php if ($p['stock'] <= 0): ?>
                                <span class="badge badge-danger px-3">Agotado</span>
                            <?php elseif ($p['stock'] <= $p['min_stock']): ?>
                                <span class="badge badge-warning px-3" title="Stock por debajo del mínimo (<?= $p['min_stock'] ?>)">
                                    <?= $p['stock'] ?> uds
                                </span>
                            <?php else: ?>
                                <span class="badge badge-success px-3">
                                    <?= $p['stock'] ?> uds
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php
                            $days = $p['days_to_expire'];
                            $cls = 'expiry-ok';
                            $label = $days . 'd';

                            if ($days < 0) {
                                $cls = 'expiry-expired';
                                $label = 'Vencido';
                            } elseif ($days == 0) {
                                $cls = 'expiry-7';
                                $label = 'Hoy';
                            } elseif ($days <= 7) {
                                $cls = 'expiry-7';
                            } elseif ($days <= 15) {
                                $cls = 'expiry-15';
                            } elseif ($days <= 30) {
                                $cls = 'expiry-30';
                            }
                            ?>
                            <span class="expiry-badge <?= $cls ?> d-block mb-1">
                                <?= $label ?>
                            </span>
                            <small class="text-muted d-block" style="font-size: 10px;">
                                <?= date('d/m/Y', strtotime($p['expiration_date'])) ?>
                            </small>
                        </td>
                        <td class="text-right">
                            <div class="btn-group gap-2">
                                <a href="<?= APP_URL ?>?route=inventory&action=edit&id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-secondary" 
                                   title="Editar Producto">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= APP_URL ?>?route=inventory&action=delete&id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   title="Eliminar" 
                                   onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($data['products'])): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state py-5 text-center">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <h3>Sin productos en el inventario</h3>
                                <p class="text-muted">Empieza agregando tu primer producto farmacéutico.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <?php if (($data['totalPages'] ?? 1) > 1): ?>
        <div class="pagination-container p-3 border-top">
            <div class="pagination">
                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                    <a href="<?= APP_URL ?>?route=inventory&page=<?= $i ?>&search=<?= urlencode($data['search'] ?? '') ?>&category=<?= $data['category'] ?? '' ?>" 
                       class="<?= $i == $data['page'] ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
/**
 * Realiza la búsqueda y filtrado de inventario
 */
function searchInventory() {
    const search = document.getElementById('searchInput').value;
    const category = document.getElementById('categoryFilter').value;
    window.location.href = '<?= APP_URL ?>?route=inventory&search=' + encodeURIComponent(search) + '&category=' + category;
}
</script>
