<div class="section-card">
    <div class="catalog-header">
        <div class="catalog-title-box">
            <h2 style="margin:0;font-size:24px;color:var(--text-color)">Catálogo de Productos</h2>
            <p style="margin:4px 0 0;color:var(--text-muted);font-size:14px">Encuentra y agrega productos a tu carrito fácilmente.</p>
        </div>
        <form action="<?= APP_URL ?>" method="GET" class="catalog-search-form">
            <input type="hidden" name="route" value="customer-dashboard">
            <input type="hidden" name="section" value="tienda">
            <?php if(isset($_GET['category'])): ?>
                <input type="hidden" name="category" value="<?= htmlspecialchars($_GET['category']) ?>">
            <?php endif; ?>
            <div style="position:relative;flex:1">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted)"></i>
                <input type="text" name="search" placeholder="Buscar producto..." value="<?= htmlspecialchars($data['search'] ?? '') ?>" 
                       style="width:100%;padding:10px 10px 10px 36px;border:1px solid var(--border-color);border-radius:8px;outline:none;transition:all 0.2s">
            </div>
            <button type="submit" class="btn btn-primary" style="padding:10px 16px;border-radius:8px;flex-shrink:0">Buscar</button>
        </form>
    </div>

    <!-- Categorías -->
    <div style="margin-bottom:24px;display:flex;gap:12px;overflow-x:auto;padding-bottom:8px">
        <a href="<?= APP_URL ?>?route=customer-dashboard&section=tienda" 
           style="white-space:nowrap;padding:8px 16px;border-radius:20px;text-decoration:none;font-weight:600;font-size:14px;
                  <?= empty($data['currentCategory']) ? 'background:var(--primary-color);color:#fff' : 'background:#f1f5f9;color:var(--text-color)' ?>">
            Todos
        </a>
        <?php foreach ($data['categories'] ?? [] as $cat): ?>
            <a href="<?= APP_URL ?>?route=customer-dashboard&section=tienda&category=<?= $cat['id'] ?><?= !empty($data['search']) ? '&search='.urlencode($data['search']) : '' ?>" 
               style="white-space:nowrap;padding:8px 16px;border-radius:20px;text-decoration:none;font-weight:600;font-size:14px;
                      <?= ($data['currentCategory'] == $cat['id']) ? 'background:var(--primary-color);color:#fff' : 'background:#f1f5f9;color:var(--text-color)' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Grid de Productos -->
    <?php if (empty($data['products'])): ?>
        <div style="text-align:center;padding:48px 0;background:#f8fafc;border-radius:12px;border:1px dashed #cbd5e1">
            <i class="fas fa-box-open" style="font-size:48px;color:#cbd5e1;margin-bottom:16px"></i>
            <h3 style="color:var(--text-color);margin:0 0 8px">No hay productos</h3>
            <p style="color:var(--text-muted);margin:0">No se encontraron productos con tu búsqueda.</p>
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px">
            <?php foreach ($data['products'] as $product): ?>
                <div style="background:#fff;border:1px solid var(--border-color);border-radius:12px;padding:20px;display:flex;flex-direction:column;position:relative;transition:all 0.3s"
                     onmouseover="this.style.boxShadow='0 10px 25px rgba(0,0,0,0.05)';this.style.transform='translateY(-2px)'"
                     onmouseout="this.style.boxShadow='none';this.style.transform='none'">
                    
                    <div style="position:absolute;top:12px;left:12px;background:<?= $product['category_color'] ?? 'var(--primary-color)' ?>;color:#fff;font-size:11px;font-weight:600;padding:4px 8px;border-radius:6px">
                        <?= htmlspecialchars($product['category_name']) ?>
                    </div>
                    
                    <div style="height:120px;display:flex;align-items:center;justify-content:center;background:#f8fafc;border-radius:8px;margin-bottom:16px;color:<?= $product['category_color'] ?? 'var(--primary-color)' ?>;font-size:48px;opacity:0.8">
                        <i class="fas fa-pills"></i>
                    </div>
                    
                    <h3 style="font-size:16px;margin:0 0 4px;color:var(--text-color)"><?= htmlspecialchars($product['name']) ?></h3>
                    <p style="font-size:13px;color:var(--text-muted);margin:0 0 12px;flex:1"><?= htmlspecialchars($product['generic_name'] ?? '') ?></p>
                    
                    <div style="display:flex;gap:12px;font-size:12px;color:var(--text-muted);margin-bottom:16px;background:#f8fafc;padding:8px;border-radius:6px">
                        <span><i class="fas fa-flask"></i> <?= htmlspecialchars($product['concentration'] ?? '') ?></span>
                        <span><i class="fas fa-tablets"></i> <?= htmlspecialchars($product['presentation'] ?? '') ?></span>
                    </div>
                    
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:auto">
                        <span style="font-size:18px;font-weight:700;color:var(--primary-color)"><?= formatCurrency($product['sale_price']) ?></span>
                        <a href="<?= APP_URL ?>?route=cart&action=add&id=<?= $product['id'] ?>" class="btn btn-primary" style="padding:8px 12px;border-radius:8px;display:flex;align-items:center;gap:6px">
                            <i class="fas fa-cart-plus"></i> <span style="font-size:13px">Añadir</span>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Paginación -->
        <?php if (($data['totalPages'] ?? 0) > 1): ?>
            <div style="display:flex;justify-content:center;gap:8px;margin-top:32px">
                <?php for ($i = 1; $i <= $data['totalPages']; $i++): ?>
                    <a href="<?= APP_URL ?>?route=customer-dashboard&section=tienda&page=<?= $i ?><?= !empty($data['search']) ? '&search='.urlencode($data['search']) : '' ?><?= !empty($data['currentCategory']) ? '&category='.$data['currentCategory'] : '' ?>" 
                       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;text-decoration:none;font-weight:600;
                              <?= ($data['currentPage'] == $i) ? 'background:var(--primary-color);color:#fff' : 'background:#fff;border:1px solid var(--border-color);color:var(--text-color)' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
