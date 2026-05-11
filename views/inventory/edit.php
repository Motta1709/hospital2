<?php $p = $data['product']; ?>
<div class="card" style="max-width:800px">
<div class="card-header"><h3 class="card-title"><i class="fas fa-edit"></i> Editar: <?= htmlspecialchars($p['name']) ?></h3></div>
<form action="<?= APP_URL ?>/?route=inventory&action=update" method="POST">
<input type="hidden" name="id" value="<?= $p['id'] ?>">
<div class="form-row">
<div class="form-group"><label>Nombre *</label><input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required></div>
<div class="form-group"><label>Nombre Genérico</label><input type="text" name="generic_name" class="form-control" value="<?= htmlspecialchars($p['generic_name'] ?? '') ?>"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Categoría *</label>
<select name="category_id" class="form-control" required>
<?php foreach($data['categories'] as $c): ?><option value="<?= $c['id'] ?>" <?= $c['id']==$p['category_id']?'selected':'' ?>><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
</select></div>
<div class="form-group"><label>Proveedor</label>
<select name="supplier_id" class="form-control">
<option value="">Ninguno</option>
<?php foreach($data['suppliers'] as $s): ?><option value="<?= $s['id'] ?>" <?= $s['id']==$p['supplier_id']?'selected':'' ?>><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
</select></div>
</div>
<div class="form-row">
<div class="form-group"><label>Código de Barras</label><input type="text" name="barcode" class="form-control" value="<?= htmlspecialchars($p['barcode'] ?? '') ?>"></div>
<div class="form-group"><label>Lote</label><input type="text" name="lot_number" class="form-control" value="<?= htmlspecialchars($p['lot_number'] ?? '') ?>"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Presentación</label><input type="text" name="presentation" class="form-control" value="<?= htmlspecialchars($p['presentation'] ?? '') ?>"></div>
<div class="form-group"><label>Concentración</label><input type="text" name="concentration" class="form-control" value="<?= htmlspecialchars($p['concentration'] ?? '') ?>"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Precio Compra *</label><input type="number" name="purchase_price" class="form-control" step="0.01" value="<?= $p['purchase_price'] ?>" required></div>
<div class="form-group"><label>Precio Venta *</label><input type="number" name="sale_price" class="form-control" step="0.01" value="<?= $p['sale_price'] ?>" required></div>
</div>
<div class="form-row">
<div class="form-group"><label>Stock *</label><input type="number" name="stock" class="form-control" value="<?= $p['stock'] ?>" required></div>
<div class="form-group"><label>Vencimiento *</label><input type="date" name="expiration_date" class="form-control" value="<?= $p['expiration_date'] ?>" required></div>
</div>
<div class="form-row">
<div class="form-group"><label>Stock Mínimo</label><input type="number" name="min_stock" class="form-control" value="<?= $p['min_stock'] ?>"></div>
<div class="form-group"><label>Stock Máximo</label><input type="number" name="max_stock" class="form-control" value="<?= $p['max_stock'] ?>"></div>
</div>
<div class="form-group"><label><input type="checkbox" name="requires_prescription" value="1" <?= $p['requires_prescription']?'checked':'' ?>> Requiere fórmula médica</label></div>
<div class="form-group"><label>Descripción</label><textarea name="description" class="form-control"><?= htmlspecialchars($p['description'] ?? '') ?></textarea></div>
<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar</button>
<a href="<?= APP_URL ?>?route=inventory" class="btn btn-secondary">Cancelar</a>
</div>
</form>
</div>

