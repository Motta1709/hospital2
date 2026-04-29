<?php
$user = $data['user'] ?? null;
$isEdit = !empty($user);
?>

<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-<?= $isEdit ? 'user-pen' : 'user-plus' ?>"></i>
            <?= $isEdit ? 'Editar' : 'Nuevo' ?> Usuario
        </h3>
    </div>
    <form action="<?= APP_URL ?>?route=users&action=<?= $isEdit ? 'update' : 'store' ?>" method="POST" class="p-4">
        <?php if ($isEdit): ?>
            <input type="hidden" name="id" value="<?= $user['id'] ?>">
        <?php endif; ?>

        <div class="form-group mb-3">
            <label for="full_name">Nombre Completo *</label>
            <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
        </div>

        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label for="username">Usuario *</label>
                <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username'] ?? '') ?>" required>
            </div>
            <div class="form-group col-md-6">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
            </div>
        </div>

        <div class="form-row mb-3">
            <div class="form-group col-md-6">
                <label for="role_id">Rol *</label>
                <select id="role_id" name="role_id" class="form-control" required>
                    <option value="">— Seleccione —</option>
                    <?php foreach ($data['roles'] as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= ($user['role_id'] ?? '') == $role['id'] ? 'selected' : '' ?>>
                            <?= ucfirst($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="is_active">Estado</label>
                <select id="is_active" name="is_active" class="form-control">
                    <option value="1" <?= ($user['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= ($user['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="form-group mb-4">
            <label for="password">Contraseña <?= $isEdit ? '(dejar en blanco para no cambiar)' : '*' ?></label>
            <input type="password" id="password" name="password" class="form-control" <?= $isEdit ? '' : 'required' ?>>
        </div>

        <div class="d-flex gap-2 border-top pt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> <?= $isEdit ? 'Actualizar' : 'Guardar' ?> Usuario
            </button>
            <a href="<?= APP_URL ?>?route=users" class="btn btn-outline-secondary">Cancelar</a>
        </div>
    </form>
</div>
