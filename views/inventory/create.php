<div class="card" style="max-width:800px">
<div class="card-header"><h3 class="card-title"><i class="fas fa-plus-circle"></i> Nuevo Producto</h3></div>
<form action="<?= APP_URL ?>?route=inventory&action=store" method="POST">
<div class="form-row">
<div class="form-group"><label>Nombre del Producto *</label><input type="text" name="name" class="form-control" required></div>
<div class="form-group"><label>Nombre Genérico</label><input type="text" name="generic_name" class="form-control"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Categoría *</label>
<select name="category_id" class="form-control" required>
<option value="">Seleccionar...</option>
<?php foreach($data['categories'] as $c): ?><option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option><?php endforeach; ?>
</select></div>
<div class="form-group"><label>Proveedor</label>
<select name="supplier_id" class="form-control">
<option value="">Seleccionar...</option>
<?php foreach($data['suppliers'] as $s): ?><option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option><?php endforeach; ?>
</select></div>
</div>
<div class="form-row">
<div class="form-group"><label>Código de Barras</label><input type="text" name="barcode" class="form-control"></div>
<div class="form-group"><label>Número de Lote</label><input type="text" name="lot_number" class="form-control"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Presentación</label><input type="text" name="presentation" class="form-control" placeholder="Ej: Caja x 20 tabletas"></div>
<div class="form-group"><label>Concentración</label><input type="text" name="concentration" class="form-control" placeholder="Ej: 500mg"></div>
</div>
<div class="form-row">
<div class="form-group"><label>Precio de Compra (COP) *</label><input type="number" name="purchase_price" class="form-control" step="0.01" required></div>
<div class="form-group"><label>Precio de Venta (COP) *</label><input type="number" name="sale_price" class="form-control" step="0.01" required></div>
</div>
<div class="form-row">
<div class="form-group"><label>Stock Inicial *</label><input type="number" name="stock" class="form-control" value="0" required></div>
<div class="form-group"><label>Fecha de Vencimiento *</label><input type="date" name="expiration_date" class="form-control" required></div>
</div>
<div class="form-row">
<div class="form-group"><label>Stock Mínimo</label><input type="number" name="min_stock" class="form-control" value="10"></div>
<div class="form-group"><label>Stock Máximo</label><input type="number" name="max_stock" class="form-control" value="500"></div>
</div>
<div class="form-group"><label><input type="checkbox" name="requires_prescription" value="1"> Requiere fórmula médica</label></div>
<div class="form-group"><label>Descripción</label><textarea name="description" class="form-control"></textarea></div>
<div style="display:flex;gap:12px;margin-top:20px">
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Producto</button>
<a href="<?= APP_URL ?>?route=inventory" class="btn btn-secondary">Cancelar</a>
</div>
</form>
</div>
