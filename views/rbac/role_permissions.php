<div class="card" style="max-width: 900px; margin: 0 auto;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-key text-primary"></i> 
            Asignar Permisos al Rol: <strong><?= ucfirst($data['role']['name']) ?></strong>
        </h3>
    </div>
    <form action="<?= APP_URL ?>?route=rbac&action=update-role" method="POST">
        <input type="hidden" name="role_id" value="<?= $data['role']['id'] ?>">
        
        <div class="card-body">
            <p class="text-muted mb-4">
                Seleccione las funciones que este rol tiene permitido realizar. 
                Los cambios se aplicarán en el próximo inicio de sesión del usuario.
            </p>

            <div class="row">
                <?php foreach ($data['modules'] as $module): ?>
                    <div class="col-md-6 mb-4">
                        <div class="p-3 border rounded h-100 bg-light">
                            <h4 class="h6 border-bottom pb-2 mb-3">
                                <i class="fas <?= $module['icon'] ?> me-2"></i> 
                                <?= htmlspecialchars($module['name']) ?>
                            </h4>
                            
                            <?php foreach ($module['permissions'] as $p): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="permissions[]" 
                                           value="<?= $p['id'] ?>" 
                                           id="perm-<?= $p['id'] ?>"
                                           <?= in_array($p['id'], $data['rolePermissions']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="perm-<?= $p['id'] ?>">
                                        <?= htmlspecialchars($p['display_name']) ?>
                                        <br><small class="text-muted"><code><?= $p['name'] ?></code></small>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card-footer d-flex gap-2 p-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>
            <a href="<?= APP_URL ?>?route=rbac" class="btn btn-outline-secondary">Volver</a>
        </div>
    </form>
</div>
