<?php /** Seccion Compras - Historial de compras del cliente */ ?>
<div class="card-header mb-2">
    <h3 class="card-title"><i class="fas fa-shopping-bag text-primary"></i> Mis Compras</h3>
</div>

<?php if (empty($data['recentOrders'])): ?>
    <div class="card"><div class="empty-state-customer"><i class="fas fa-shopping-cart"></i><h3>Sin compras registradas</h3><p>Tu historial de compras aparecera aqui.</p></div></div>
<?php else: ?>
<div class="card">
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Factura</th>
                    <th>Fecha</th>
                    <th>Items</th>
                    <th>Metodo</th>
                    <th>Puntos</th>
                    <th class="text-right">Total</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($data['recentOrders'] as $order): ?>
                <tr>
                    <td><span class="badge badge-primary"><?= $order['invoice_number'] ?></span></td>
                    <td><?= date('d/m/Y h:i A', strtotime($order['created_at'])) ?></td>
                    <td><span class="badge badge-info"><?= $order['total_items'] ?></span></td>
                    <td>
                        <?php
                        $methods = ['cash'=>'Efectivo','card'=>'Tarjeta','transfer'=>'Transferencia','mixed'=>'Mixto'];
                        echo $methods[$order['payment_method']] ?? $order['payment_method'];
                        ?>
                    </td>
                    <td><span class="text-success"><i class="fas fa-star"></i> +<?= $order['loyalty_points_earned'] ?></span></td>
                    <td class="text-right" style="font-weight:700;color:var(--customer-primary)"><?= formatCurrency($order['total']) ?></td>
                    <td><span class="estado-badge estado-<?= $order['status'] === 'completed' ? 'aprobada' : 'pendiente' ?>">
                        <?= $order['status'] === 'completed' ? 'Completada' : ucfirst($order['status']) ?>
                    </span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>
