<?php /** Seccion PQRSF - RF-02 */ ?>
<div class="flex-between mb-2">
    <h3 class="card-title"><i class="fas fa-headset text-warning"></i> Mis PQRSF</h3>
    <button class="btn btn-primary btn-sm" onclick="document.getElementById('modalPqrsf').classList.add('active')">
        <i class="fas fa-plus"></i> Nueva Solicitud
    </button>
</div>

<?php if (empty($data['recentPqrsf'])): ?>
<div class="card"><div class="empty-state-customer"><i class="fas fa-inbox"></i><h3>Sin solicitudes</h3><p>No has creado solicitudes PQRSF.</p></div></div>
<?php else: ?>
<div class="card">
    <div class="timeline">
    <?php foreach ($data['recentPqrsf'] as $p): ?>
        <div class="timeline-item estado-<?= $p['estado'] ?>">
            <div class="timeline-date"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?> &mdash; <span class="estado-badge estado-<?= $p['estado'] ?>"><?= ucfirst(str_replace('_',' ',$p['estado'])) ?></span></div>
            <div class="timeline-title"><?= htmlspecialchars($p['asunto']) ?></div>
            <div class="timeline-desc">
                <span class="badge badge-info"><?= strtoupper($p['tipo']) ?></span>
                <span class="text-muted" style="margin-left:8px">Radicado: <?= $p['radicado'] ?></span>
            </div>
            <?php if ($p['respuesta']): ?>
            <div style="margin-top:8px;padding:10px;background:var(--bg-surface);border-radius:var(--radius-sm);font-size:13px">
                <strong>Respuesta:</strong> <?= htmlspecialchars($p['respuesta']) ?>
            </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Modal Nueva PQRSF -->
<div class="modal-overlay" id="modalPqrsf">
<div class="modal">
    <div class="modal-header">
        <h2>Nueva Solicitud PQRSF</h2>
        <button class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('active')">&times;</button>
    </div>
    <form method="POST" action="<?= APP_URL ?>?route=customer-dashboard&action=createPqrsf&client_id=<?= $data['profile']['id'] ?>">
        <div class="form-group">
            <label>Tipo de solicitud</label>
            <select name="tipo" class="form-control" required>
                <option value="">Seleccione...</option>
                <option value="peticion">Peticion</option>
                <option value="queja">Queja</option>
                <option value="reclamo">Reclamo</option>
                <option value="sugerencia">Sugerencia</option>
                <option value="felicitacion">Felicitacion</option>
            </select>
        </div>
        <div class="form-group">
            <label>Asunto</label>
            <input type="text" name="asunto" class="form-control" required maxlength="255" placeholder="Describa brevemente su solicitud">
        </div>
        <div class="form-group">
            <label>Descripcion detallada</label>
            <textarea name="descripcion" class="form-control" rows="4" required placeholder="Explique su solicitud con el mayor detalle posible..."></textarea>
        </div>
        <div class="form-group">
            <label>Prioridad</label>
            <select name="prioridad" class="form-control">
                <option value="baja">Baja</option>
                <option value="media" selected>Media</option>
                <option value="alta">Alta</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Enviar Solicitud</button>
    </form>
</div>
</div>
