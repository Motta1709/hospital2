<div class="toolbar">
<div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
<input type="date" class="form-control" style="width:auto" id="dateFrom" value="<?= $data['dateFrom'] ?? '' ?>">
<input type="date" class="form-control" style="width:auto" id="dateTo" value="<?= $data['dateTo'] ?? '' ?>">
<button class="btn btn-secondary btn-sm" onclick="filterSales()"><i class="fas fa-filter"></i> Filtrar</button>
</div>
<a href="<?= APP_URL ?>?route=sales&action=pos" class="btn btn-primary"><i class="fas fa-cash-register"></i> Ir al POS</a>
</div>
<div class="card">
<div class="card-header"><h3 class="card-title"><i class="fas fa-receipt"></i> Historial de Ventas (<?= $data['totalSales'] ?>)</h3></div>
<div class="table-wrapper"><table>
<thead><tr><th>Factura</th><th>Cliente</th><th>Total</th><th>Pago</th><th>Estado</th><th>Fecha</th><th>Cajero</th><th></th></tr></thead>
<tbody>
<?php foreach($data['sales'] as $s): ?>
<tr>
<td><span class="badge badge-primary"><?= $s['invoice_number'] ?></span></td>
<td><?= $s['client_name'] ?? '<span class="text-muted">—</span>' ?></td>
<td style="font-weight:700;color:var(--accent)"><?= formatCurrency($s['total']) ?></td>
<td><span class="badge badge-info"><?= ucfirst($s['payment_method']) ?></span></td>
<td><?php if($s['status']==='completed'): ?><span class="badge badge-success">Completada</span><?php elseif($s['status']==='cancelled'): ?><span class="badge badge-danger">Anulada</span><?php else: ?><span class="badge badge-warning">Pendiente</span><?php endif; ?></td>
<td class="text-muted"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
<td class="text-muted"><?= $s['cashier_name'] ?></td>
<td><?php if($s['status']==='completed'): ?><a href="<?= APP_URL ?>?route=sales&action=cancel&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Anular esta venta?')"><i class="fas fa-ban"></i></a><?php endif; ?></td>
</tr>
<?php endforeach; ?>
<?php if(empty($data['sales'])): ?><tr><td colspan="8"><div class="empty-state"><i class="fas fa-receipt"></i><h3>Sin ventas</h3></div></td></tr><?php endif; ?>
</tbody></table></div>
<?php if(($data['totalPages'] ?? 1) > 1): ?>
<div class="pagination">
<?php for($i=1;$i<=$data['totalPages'];$i++): ?><a href="<?= APP_URL ?>?route=sales&page=<?= $i ?>" class="<?= $i==$data['page']?'active':'' ?>"><?= $i ?></a><?php endfor; ?>
</div>
<?php endif; ?>
</div>
<script>
function filterSales(){
const f=document.getElementById('dateFrom').value, t=document.getElementById('dateTo').value;
let url='<?= APP_URL ?>?route=sales';
if(f) url+='&date_from='+f; if(t) url+='&date_to='+t;
window.location.href=url;
}
</script>
