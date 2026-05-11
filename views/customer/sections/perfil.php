<?php /** Seccion Perfil - RF-01 */ ?>
<div class="content-grid">
    <!-- Tarjeta de Perfil -->
    <div class="card">
        <div class="profile-card-customer">
            <div class="avatar-lg"><?= strtoupper(substr($data['profile']['first_name'],0,1).substr($data['profile']['last_name'],0,1)) ?></div>
            <h3><?= htmlspecialchars($data['profile']['first_name'].' '.$data['profile']['last_name']) ?></h3>
            <div class="profile-doc"><?= $data['profile']['document_type'] ?> <?= $data['profile']['document_number'] ?></div>
            <div style="display:flex;flex-direction:column;gap:8px;text-align:left;margin-top:16px">
                <div style="font-size:13px"><i class="fas fa-envelope text-muted" style="width:20px"></i> <?= htmlspecialchars($data['profile']['email'] ?? 'No registrado') ?></div>
                <div style="font-size:13px"><i class="fas fa-phone text-muted" style="width:20px"></i> <?= htmlspecialchars($data['profile']['phone'] ?? 'No registrado') ?></div>
                <div style="font-size:13px"><i class="fas fa-city text-muted" style="width:20px"></i> <?= htmlspecialchars($data['profile']['city'] ?? 'No registrada') ?></div>
                <?php if ($data['profile']['date_of_birth']): ?>
                <div style="font-size:13px"><i class="fas fa-cake-candles text-muted" style="width:20px"></i> <?= date('d/m/Y', strtotime($data['profile']['date_of_birth'])) ?></div>
                <?php endif; ?>
                <?php if ($data['profile']['allergies']): ?>
                <div style="font-size:13px"><i class="fas fa-triangle-exclamation text-danger" style="width:20px"></i> Alergias: <?= htmlspecialchars($data['profile']['allergies']) ?></div>
                <?php endif; ?>
            </div>
            <div class="profile-meta">
                <div class="meta-item"><span class="meta-value"><?= number_format($data['summary']['puntos']) ?></span><span class="meta-label">Puntos</span></div>
                <div class="meta-item"><span class="meta-value"><?= $data['profile']['visit_count'] ?></span><span class="meta-label">Visitas</span></div>
                <div class="meta-item"><span class="meta-value"><?= formatCurrency($data['profile']['total_purchases']) ?></span><span class="meta-label">Total</span></div>
            </div>
            <button class="btn btn-secondary btn-block mt-2" onclick="document.getElementById('modalEditProfile').classList.add('active')">
                <i class="fas fa-user-edit"></i> Editar Mis Datos
            </button>
        </div>
    </div>

    <!-- Modal Editar Perfil -->
    <div class="modal-overlay" id="modalEditProfile">
    <div class="modal">
        <div class="modal-header">
            <h2>Editar Mis Datos</h2>
            <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
        </div>
        <form method="POST" action="<?= APP_URL ?>/?route=customer-dashboard&action=updateProfile&client_id=<?= $data['profile']['id'] ?>">
            <div class="form-row">
                <div class="form-group"><label>Nombres</label><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($data['profile']['first_name']) ?>" required></div>
                <div class="form-group"><label>Apellidos</label><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($data['profile']['last_name']) ?>" required></div>
            </div>
            <div class="form-group"><label>Correo Electronico</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['profile']['email']) ?>" required></div>
            <div class="form-group"><label>Telefono</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($data['profile']['phone']) ?>"></div>
            <div class="form-group"><label>Alergias Conocidas</label><textarea name="allergies" class="form-control" rows="2"><?= htmlspecialchars($data['profile']['allergies']) ?></textarea></div>
            <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Guardar Cambios</button>
        </form>
    </div>
    </div>

    <!-- Direcciones -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-location-dot text-primary"></i> Mis Direcciones</h3>
            <button class="btn btn-sm btn-primary" onclick="document.getElementById('modalAddress').classList.add('active')">
                <i class="fas fa-plus"></i> Agregar
            </button>
        </div>
        <?php if (empty($data['addresses'])): ?>
            <div class="empty-state-customer"><i class="fas fa-map-marker-alt"></i><h3>Sin direcciones</h3><p>Agrega tu primera direccion.</p></div>
        <?php else: ?>
        <?php foreach ($data['addresses'] as $addr): ?>
        <div style="padding:12px;border:1px solid var(--border-color);border-radius:var(--radius-sm);margin-bottom:8px;display:flex;align-items:center;gap:12px">
            <div style="flex:1">
                <span class="address-tag tag-<?= $addr['label'] ?>"><i class="fas fa-<?= $addr['label']==='casa'?'house':($addr['label']==='trabajo'?'briefcase':'location-dot') ?>"></i> <?= ucfirst($addr['label']) ?></span>
                <?php if ($addr['is_default']): ?><span class="badge badge-success" style="font-size:10px">Principal</span><?php endif; ?>
                <div style="font-size:13px;margin-top:6px;color:var(--text-primary)"><?= htmlspecialchars($addr['address_line']) ?></div>
                <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($addr['city']) ?><?= $addr['neighborhood'] ? ', '.$addr['neighborhood'] : '' ?></div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Agregar Direccion -->
<div class="modal-overlay" id="modalAddress">
<div class="modal">
    <div class="modal-header">
        <h2>Nueva Direccion</h2>
        <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>/?route=customer-dashboard&action=addAddress&client_id=<?= $data['profile']['id'] ?>">
        <div class="form-group">
            <label>Etiqueta</label>
            <select name="label" class="form-control"><option value="casa">Casa</option><option value="trabajo">Trabajo</option><option value="otro">Otro</option></select>
        </div>
        <div class="form-group"><label>Direccion</label><input type="text" name="address_line" class="form-control" required placeholder="Calle, Carrera, numero..."></div>
        <div class="form-row">
            <div class="form-group"><label>Ciudad</label><input type="text" name="city" class="form-control" required value="Florencia"></div>
            <div class="form-group"><label>Barrio</label><input type="text" name="neighborhood" class="form-control" placeholder="Barrio"></div>
        </div>
        <div class="form-group"><label>Instrucciones de entrega</label><textarea name="instructions" class="form-control" rows="2" placeholder="Referencias, piso, apartamento..."></textarea></div>
        <div class="form-group"><label><input type="checkbox" name="is_default" value="1"> Establecer como direccion principal</label></div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Guardar Direccion</button>
    </form>
</div>
</div>
