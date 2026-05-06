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
                            <?php 
                                $productModel = new Product();
                                $status = $productModel->getTrafficLightStatus($p); 
                            ?>
                            <div class="d-flex flex-column align-items-center gap-1">
                                <span class="badge badge-<?= $status['stock']['color'] ?> px-3 w-100">
                                    <?= $p['stock'] ?> uds
                                </span>
                                <small class="text-<?= $status['stock']['color'] ?>-dark fw-bold" style="font-size: 10px;">
                                    <?= strtoupper($status['stock']['label']) ?>
                                </small>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex flex-column align-items-center gap-1">
                                <span class="badge badge-<?= $status['expiration']['color'] ?> px-3 w-100">
                                    <?= $p['days_to_expire'] ?>d
                                </span>
                                <small class="text-muted" style="font-size: 10px;">
                                    <?= date('d/m/Y', strtotime($p['expiration_date'])) ?>
                                </small>
                            </div>
                        </td>
                        <td class="text-right">
                            <div class="btn-group gap-2">
                                <a href="<?= APP_URL ?>?route=inventory&action=kardex&id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-info" 
                                   title="Ver Kardex">
                                    <i class="fas fa-history"></i>
                                </a>
                                <a href="<?= APP_URL ?>?route=inventory&action=edit&id=<?= $p['id'] ?>" 
                                   class="btn btn-sm btn-outline-secondary" 
                                   title="Editar">
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

