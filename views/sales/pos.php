<div class="pos-layout">
<!-- Left: Product Search -->
<div class="pos-products">
<div class="card">
<div class="search-bar" style="max-width:100%;margin-bottom:16px">
<i class="fas fa-search"></i>
<input type="text" id="posSearch" placeholder="Buscar producto por nombre o código..." oninput="searchPosProduct(this.value)" autofocus>
</div>
<div id="posProductGrid" class="product-grid">
<?php if (!empty($data['products'])): ?>
<?php foreach ($data['products'] as $p): ?>
<div class="product-card" onclick='addToCart(<?= json_encode($p) ?>)'>
    <div class="p-name"><?= htmlspecialchars($p['name']) ?></div>
    <div class="p-generic"><?= htmlspecialchars($p['generic_name'] ?? '') ?><?= !empty($p['concentration']) ? ' · ' . htmlspecialchars($p['concentration']) : '' ?></div>
    <div class="p-price">$ <?= number_format($p['sale_price'], 0, ',', '.') ?></div>
    <div class="p-stock">Stock: <?= (int)$p['stock'] ?><?= ($p['requires_prescription'] ?? 0) == 1 ? ' · <i class="fas fa-prescription" style="color:var(--warning)"></i>' : '' ?></div>
</div>
<?php endforeach; ?>
<?php else: ?>
<div class="empty-state"><i class="fas fa-box-open"></i><h3>Sin productos disponibles</h3><p>No hay productos con stock en este momento</p></div>
<?php endif; ?>
</div>
</div>
</div>
<!-- Right: Cart -->
<div class="pos-cart">
<div class="pos-cart-header">
<h3 style="font-size:16px;font-weight:700"><i class="fas fa-shopping-cart"></i> Carrito de Venta</h3>
<div style="font-size:12px;color:var(--text-muted);margin-top:4px" id="posInvoice"><?= $data['invoiceNumber'] ?></div>
</div>
<!-- Client selector -->
<div style="padding:12px;border-bottom:1px solid rgba(255,255,255,.06)">
<div class="search-bar" style="max-width:100%">
<i class="fas fa-user"></i>
<input type="text" id="clientSearch" placeholder="Buscar cliente (opcional)..." oninput="searchPosClient(this.value)">
</div>
<div id="clientResult" style="margin-top:8px;display:none;padding:8px;background:var(--bg-surface);border-radius:8px;font-size:13px"></div>
</div>
<div class="pos-cart-items" id="cartItems">
<div class="empty-state"><i class="fas fa-cart-plus"></i><h3>Carrito vacío</h3><p>Agrega productos</p></div>
</div>
<div class="pos-cart-footer">
<div class="pos-totals">
<div class="total-row"><span>Subtotal</span><span id="posSubtotal">$ 0</span></div>
<div class="total-row"><span>Descuento</span><span id="posDiscount">$ 0</span></div>
<div class="total-row grand-total"><span>TOTAL</span><span id="posTotal">$ 0</span></div>
</div>
<div style="margin-top:12px">
<select id="paymentMethod" class="form-control" style="margin-bottom:8px">
<option value="cash">Efectivo</option><option value="card">Tarjeta</option><option value="transfer">Transferencia</option>
</select>
<div id="cashSection" style="margin-bottom:8px">
<input type="number" id="cashReceived" class="form-control" placeholder="Efectivo recibido" oninput="calcChange()">
<div style="font-size:13px;margin-top:4px;color:var(--accent)" id="changeAmount"></div>
</div>
<button class="btn btn-primary btn-block" onclick="processSale()" id="btnSale" disabled>
<i class="fas fa-check-circle"></i> Finalizar Venta
</button>
</div>
</div>
</div>
</div>

<script>
let cart = [];
let selectedClient = null;
const BASE = '<?= APP_URL ?>';

// Catálogo completo pre-cargado desde PHP
const ALL_PRODUCTS = <?= json_encode(array_values($data['products'] ?? []), JSON_UNESCAPED_UNICODE) ?>;

function renderProductGrid(products) {
    const grid = document.getElementById('posProductGrid');
    if (!products.length) {
        grid.innerHTML = '<div class="empty-state"><i class="fas fa-search"></i><h3>Sin resultados</h3><p>Intenta con otro término</p></div>';
        return;
    }
    grid.innerHTML = products.map(p => `
        <div class="product-card" onclick='addToCart(${JSON.stringify(p)})'>
            <div class="p-name">${p.name}</div>
            <div class="p-generic">${p.generic_name||''} ${p.concentration ? '· '+p.concentration : ''}</div>
            <div class="p-price">$ ${Number(p.sale_price).toLocaleString('es-CO')}</div>
            <div class="p-stock">Stock: ${p.stock} ${p.requires_prescription==1?'· <i class="fas fa-prescription" style="color:var(--warning)"></i>':''}</div>
        </div>`).join('');
}

function searchPosProduct(q) {
    if (q.length < 2) {
        // Al limpiar la búsqueda, mostrar todos los productos
        renderProductGrid(ALL_PRODUCTS);
        return;
    }
    fetch(BASE + '?route=sales&action=search-product&q=' + encodeURIComponent(q), {headers: {'X-Requested-With': 'XMLHttpRequest'}})
        .then(r => r.json())
        .then(products => renderProductGrid(products));
}

function searchPosClient(q){
if(q.length < 2){document.getElementById('clientResult').style.display='none';return;}
fetch(BASE+'?route=api&entity=clients&q='+encodeURIComponent(q),{headers:{'X-Requested-With':'XMLHttpRequest'}})
.then(r=>r.json()).then(clients=>{
const div = document.getElementById('clientResult');
if(!clients.length){div.style.display='none';return;}
div.style.display='block';
div.innerHTML = clients.map(c=>`<div style="padding:6px;cursor:pointer;border-radius:4px" onmouseover="this.style.background='var(--bg-hover)'" onmouseout="this.style.background=''" onclick="selectClient(${c.id},'${c.first_name} ${c.last_name}',${c.loyalty_points},'${c.allergies||''}')">
<strong>${c.first_name} ${c.last_name}</strong> <span style="color:var(--text-muted)">· ${c.document_number}</span>
${c.allergies?'<br><span style="color:var(--danger);font-size:11px"><i class="fas fa-exclamation-triangle"></i> Alergias: '+c.allergies+'</span>':''}
</div>`).join('');
});
}

function selectClient(id,name,pts,allergies){
selectedClient = {id,name,pts};
document.getElementById('clientSearch').value = name;
document.getElementById('clientResult').innerHTML = '<div style="display:flex;justify-content:space-between;align-items:center"><span><i class="fas fa-user-check" style="color:var(--success)"></i> '+name+'</span><span class="badge badge-primary"><i class="fas fa-star"></i> '+pts+' pts</span></div>'+(allergies?'<div style="color:var(--danger);font-size:11px;margin-top:4px"><i class="fas fa-exclamation-triangle"></i> Alergias: '+allergies+'</div>':'');
}

function addToCart(product){
const existing = cart.find(i=>i.product_id===product.id);
if(existing){
if(existing.quantity >= product.stock){alert('Stock insuficiente');return;}
existing.quantity++;
existing.subtotal = existing.quantity * existing.unit_price;
} else {
cart.push({product_id:product.id, name:product.name, unit_price:parseFloat(product.sale_price), quantity:1, subtotal:parseFloat(product.sale_price), stock:product.stock, discount_percent:0});
}
renderCart();
}

function updateQty(idx, delta){
cart[idx].quantity += delta;
if(cart[idx].quantity <= 0) cart.splice(idx,1);
else if(cart[idx].quantity > cart[idx].stock){cart[idx].quantity = cart[idx].stock; alert('Stock máximo alcanzado');}
else cart[idx].subtotal = cart[idx].quantity * cart[idx].unit_price;
renderCart();
}

function removeItem(idx){cart.splice(idx,1);renderCart();}

function renderCart(){
const container = document.getElementById('cartItems');
if(!cart.length){container.innerHTML='<div class="empty-state"><i class="fas fa-cart-plus"></i><h3>Carrito vacío</h3></div>';updateTotals();return;}
container.innerHTML = cart.map((item,i)=>`
<div class="pos-cart-item">
<div class="item-info"><div class="item-name">${item.name}</div><div class="item-price">$ ${Number(item.unit_price).toLocaleString('es-CO')} c/u</div></div>
<div class="item-qty"><button onclick="updateQty(${i},-1)">−</button><span>${item.quantity}</span><button onclick="updateQty(${i},1)">+</button></div>
<div class="item-total">$ ${Number(item.subtotal).toLocaleString('es-CO')}</div>
<button class="remove-btn" onclick="removeItem(${i})"><i class="fas fa-xmark"></i></button>
</div>`).join('');
updateTotals();
}

function updateTotals(){
const sub = cart.reduce((s,i)=>s+i.subtotal,0);
document.getElementById('posSubtotal').textContent = '$ '+sub.toLocaleString('es-CO');
document.getElementById('posTotal').textContent = '$ '+sub.toLocaleString('es-CO');
document.getElementById('posDiscount').textContent = '$ 0';
document.getElementById('btnSale').disabled = cart.length===0;
}

function calcChange(){
const total = cart.reduce((s,i)=>s+i.subtotal,0);
const cash = parseFloat(document.getElementById('cashReceived').value)||0;
const change = cash - total;
document.getElementById('changeAmount').textContent = change >= 0 ? 'Cambio: $ '+change.toLocaleString('es-CO') : '';
}

function processSale(){
if(!cart.length) return;
const total = cart.reduce((s,i)=>s+i.subtotal,0);
const cashReceived = parseFloat(document.getElementById('cashReceived').value)||total;
const payload = {
client_id: selectedClient?.id || null,
items: cart.map(i=>({product_id:i.product_id,quantity:i.quantity,unit_price:i.unit_price,discount_percent:0,subtotal:i.subtotal})),
subtotal: total, discount_amount: 0, total: total,
payment_method: document.getElementById('paymentMethod').value,
cash_received: cashReceived, change_amount: Math.max(0, cashReceived - total)
};

fetch(BASE+'?route=sales&action=process',{method:'POST',headers:{'Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(payload)})
.then(r=>r.json()).then(res=>{
if(res.success){
alert('¡Venta exitosa! Factura: '+res.invoice);
cart=[];selectedClient=null;
document.getElementById('clientSearch').value='';
document.getElementById('clientResult').style.display='none';
document.getElementById('cashReceived').value='';
document.getElementById('changeAmount').textContent='';
renderCart();
document.getElementById('posSearch').value='';
// Volver a mostrar el catálogo completo tras finalizar la venta
renderProductGrid(ALL_PRODUCTS);
} else alert('Error: '+(res.message||'Desconocido'));
}).catch(e=>alert('Error de conexión'));
}

document.getElementById('paymentMethod').addEventListener('change',function(){
document.getElementById('cashSection').style.display = this.value==='cash'?'block':'none';
});
</script>

