<!-- Barra de Herramientas -->
<div class="toolbar mb-4">
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" 
               id="clientSearchInput" 
               placeholder="Buscar por nombre, documento o teléfono..." 
               value="<?= htmlspecialchars($data['search'] ?? '') ?>" 
               onkeyup="if(event.key === 'Enter') searchClients()">
    </div>
    
    <a href="<?= APP_URL ?>?route=clients&action=create" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nuevo Cliente
    </a>
</div>

<!-- Listado de Clientes -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-users"></i> Clientes (<?= $data['totalClients'] ?>)
        </h3>
    </div>

    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Documento</th>
                    <th>Teléfono</th>
                    <th>Ciudad</th>
                    <th>Compras</th>
                    <th>Puntos</th>
                    <th>Visitas</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['clients'] as $c): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-3">
                                <!-- Avatar con Iniciales -->
                                <div class="avatar-circle">
                                    <?= strtoupper(substr($c['first_name'], 0, 1) . substr($c['last_name'], 0, 1)) ?>
                                </div>
                                
                                <div>
                                    <div class="client-name"><?= htmlspecialchars($c['first_name'] . ' ' . $c['last_name']) ?></div>
                                    <div class="client-email"><?= $c['email'] ?? '' ?></div>
                                    
                                    <?php if ($c['allergies']): ?>
                                        <div class="client-alert text-danger">
                                            <i class="fas fa-exclamation-triangle"></i> 
                                            <?= htmlspecialchars($c['allergies']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted"><?= $c['document_type'] ?></span> 
                            <strong><?= $c['document_number'] ?></strong>
                        </td>
                        <td><?= $c['phone'] ?? '—' ?></td>
                        <td class="text-muted"><?= $c['city'] ?? '—' ?></td>
                        <td class="text-accent font-weight-bold">
                            <?= formatCurrency($c['total_purchases']) ?>
                        </td>
                        <td>
                            <span class="badge badge-primary">
                                <i class="fas fa-star"></i> <?= $c['loyalty_points'] ?>
                            </span>
                        </td>
                        <td class="text-muted"><?= $c['visit_count'] ?></td>
                        <td class="text-right">
                            <div class="btn-group gap-2">
                                <a href="<?= APP_URL ?>?route=clients&action=profile&id=<?= $c['id'] ?>" 
                                   class="btn btn-sm btn-outline-secondary" 
                                   title="Ver Perfil">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= APP_URL ?>?route=clients&action=edit&id=<?= $c['id'] ?>" 
                                   class="btn btn-sm btn-outline-secondary" 
                                   title="Editar Información">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($data['clients'])): ?>
                    <tr>
                        <td colspan="8">
                            <div class="empty-state py-5 text-center">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h3>Sin clientes encontrados</h3>
                                <p class="text-muted">Intenta con otros términos de búsqueda.</p>
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
                    <a href="<?= APP_URL ?>?route=clients&page=<?= $i ?>&search=<?= urlencode($data['search'] ?? '') ?>" 
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
 * Realiza la búsqueda de clientes
 */
function searchClients() {
    const search = document.getElementById('clientSearchInput').value;
    window.location.href = '<?= APP_URL ?>?route=clients&search=' + encodeURIComponent(search);
}
</script>

