<div class="row">
    <!-- Columna Izquierda: Roles -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-shield"></i> Roles del Sistema</h3>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($data['roles'] as $role): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                        <div>
                            <div class="fw-bold"><?= ucfirst($role['name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($role['description']) ?></small>
                        </div>
                        <a href="<?= APP_URL ?>?route=rbac&action=edit-role&id=<?= $role['id'] ?>" class="btn btn-sm btn-outline-primary" title="Gestionar Permisos">
                            <i class="fas fa-key"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Funciones por Módulo -->
    <div class="col-md-8 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title"><i class="fas fa-list-check"></i> Funciones (Permisos)</h3>
                <button class="btn btn-sm btn-primary" onclick="showNewPermModal()">
                    <i class="fas fa-plus"></i> Nueva Función
                </button>
            </div>
            <div class="card-body p-0">
                <div class="accordion" id="modulesAccordion">
                    <?php foreach ($data['modules'] as $module): ?>
                        <div class="accordion-item border-bottom">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed py-3 px-4" type="button" data-bs-toggle="collapse" data-bs-target="#mod-<?= $module['id'] ?>">
                                    <i class="fas <?= $module['icon'] ?> me-3 text-primary"></i>
                                    <strong><?= htmlspecialchars($module['name']) ?></strong>
                                    <span class="ms-auto badge badge-outline-secondary"><?= count($module['permissions']) ?> funciones</span>
                                </button>
                            </h2>
                            <div id="mod-<?= $module['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#modulesAccordion">
                                <div class="accordion-body bg-light p-0">
                                    <table class="table mb-0 table-sm">
                                        <thead class="bg-white">
                                            <tr>
                                                <th class="ps-4">Nombre de la Función</th>
                                                <th>Slug (Identificador)</th>
                                                <th class="text-right pe-4">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($module['permissions'] as $p): ?>
                                                <tr>
                                                    <td class="ps-4 py-2"><?= htmlspecialchars($p['display_name']) ?></td>
                                                    <td><code><?= $p['name'] ?></code></td>
                                                    <td class="text-right pe-4">
                                                        <button class="btn btn-xs text-danger" title="Eliminar"><i class="fas fa-trash"></i></button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Nueva Función (Simulado para fines de demo) -->
<div id="newPermModal" class="modal-backdrop d-none" style="background: rgba(0,0,0,0.5); position:fixed; inset:0; z-index:1000; display:flex; align-items:center; justify-content:center;">
    <div class="card" style="width: 450px;">
        <div class="card-header d-flex justify-content-between">
            <h3 class="card-title">Nueva Función</h3>
            <button class="btn-close" onclick="hideNewPermModal()"></button>
        </div>
        <form action="<?= APP_URL ?>?route=rbac&action=store-perm" method="POST" class="p-3">
            <div class="form-group mb-3">
                <label>Módulo</label>
                <select name="module_id" class="form-control" required>
                    <?php foreach ($data['modules'] as $module): ?>
                        <option value="<?= $module['id'] ?>"><?= htmlspecialchars($module['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group mb-3">
                <label>Nombre a Mostrar</label>
                <input type="text" name="display_name" class="form-control" placeholder="Ej: Crear Factura" required>
            </div>
            <div class="form-group mb-3">
                <label>Identificador (Slug)</label>
                <input type="text" name="name" class="form-control" placeholder="Ej: create_invoice" required>
            </div>
            <div class="d-flex gap-2 justify-content-end mt-4">
                <button type="button" class="btn btn-outline-secondary" onclick="hideNewPermModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Función</button>
            </div>
        </form>
    </div>
</div>

<script>
function showNewPermModal() { document.getElementById('newPermModal').classList.remove('d-none'); }
function hideNewPermModal() { document.getElementById('newPermModal').classList.add('d-none'); }
</script>

