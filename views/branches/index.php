<div class="branches-header mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="h3 mb-0"><i class="fas fa-store text-primary me-2"></i>Gestión de Sucursales</h2>
        <p class="text-muted mb-0">Crea y administra las diferentes sedes de la farmacia</p>
    </div>
    <a href="<?= APP_URL ?>?route=branches&action=create" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Nueva Sucursal
    </a>
</div>

<div class="row">
    <?php foreach ($data['branches'] as $b): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="branch-icon bg-light rounded-3 p-3 text-primary">
                            <i class="fas fa-hospital fa-2x"></i>
                        </div>
                        <span class="badge rounded-pill <?= $b['is_active'] ? 'bg-success-light text-success' : 'bg-danger-light text-danger' ?> px-3 py-2">
                            <?= $b['is_active'] ? 'Activa' : 'Inactiva' ?>
                        </span>
                    </div>
                    
                    <h3 class="h5 fw-bold mb-1"><?= htmlspecialchars($b['name']) ?></h3>
                    <p class="text-muted small mb-3"><?= htmlspecialchars($b['city']) ?></p>
                    
                    <div class="branch-info small">
                        <div class="mb-2">
                            <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                            <?= htmlspecialchars($b['address'] ?? 'Sin dirección') ?>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone me-2 text-muted"></i>
                            <?= htmlspecialchars($b['phone'] ?? 'Sin teléfono') ?>
                        </div>
                        <div class="mb-0">
                            <i class="fas fa-envelope me-2 text-muted"></i>
                            <?= htmlspecialchars($b['email'] ?? 'Sin email') ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light border-0 p-3 d-flex gap-2">
                    <a href="<?= APP_URL ?>?route=branches&action=edit&id=<?= $b['id'] ?>" class="btn btn-outline-secondary btn-sm flex-grow-1">
                        <i class="fas fa-edit me-1"></i> Editar
                    </a>
                    <a href="<?= APP_URL ?>?route=users&branch_id=<?= $b['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1">
                        <i class="fas fa-users me-1"></i> Personal
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.bg-success-light { background: #dcfce7; }
.bg-danger-light { background: #fee2e2; }
.branch-icon { width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; }
</style>

