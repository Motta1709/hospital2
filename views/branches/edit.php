<?php $b = $data['branch']; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h2 class="h5 mb-0 fw-bold">Editar Sucursal: <?= htmlspecialchars($b['name']) ?></h2>
            </div>
            <div class="card-body p-4">
                <form action="<?= APP_URL ?>/?route=branches&action=update" method="POST">
                    <input type="hidden" name="id" value="<?= $b['id'] ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nombre de la Sucursal</label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($b['name']) ?>" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="is_active" class="form-select">
                                <option value="1" <?= $b['is_active'] ? 'selected' : '' ?>>Activa</option>
                                <option value="0" <?= !$b['is_active'] ? 'selected' : '' ?>>Inactiva</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label fw-bold">NIT / Identificación</label>
                            <input type="text" name="nit" class="form-control" value="<?= htmlspecialchars($b['nit']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ciudad</label>
                            <input type="text" name="city" class="form-control" value="<?= htmlspecialchars($b['city']) ?>">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Dirección Completa</label>
                            <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($b['address']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Teléfono de Contacto</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($b['phone']) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($b['email']) ?>">
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-3 border-top pt-4">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                        <a href="<?= APP_URL ?>?route=branches" class="btn btn-outline-secondary px-4">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

