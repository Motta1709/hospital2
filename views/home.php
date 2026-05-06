<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a PharmaCRM - Tu Farmacia de Confianza</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/home.css">
</head>
<body class="home-page">
    <nav class="home-nav">
        <div class="container">
            <div class="nav-brand">
                <div class="brand-icon">
                    <i class="fas fa-prescription-bottle-medical"></i>
                </div>
                <div class="brand-text">
                    <h2>PharmaCRM</h2>
                    <small>Gestión Farmacéutica Profesional</small>
                </div>
            </div>
            <div class="nav-links">
                <a href="<?= APP_URL ?>?route=cart" class="nav-item cart-badge-container">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if (!empty($_SESSION['cart'])): ?>
                        <span class="cart-badge"><?= count($_SESSION['cart']) ?></span>
                    <?php endif; ?>
                </a>
                <a href="#products" class="nav-item">Productos</a>
                <a href="#categories" class="nav-item">Categorías</a>
                <?php if (Auth::check()): ?>
                    <a href="<?= APP_URL ?>?route=dashboard" class="btn btn-primary btn-sm">Dashboard</a>
                <?php else: ?>
                    <a href="<?= APP_URL ?>?route=login" class="btn btn-secondary btn-sm">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header class="hero">
        <div class="hero-content container">
            <div class="hero-text">
                <h1>Cuida tu salud con <span>PharmaCRM</span></h1>
                <p>Encuentra los mejores productos farmacéuticos con la garantía y profesionalismo que tú y tu familia merecen. Calidad certificada y atención personalizada.</p>
                <div class="hero-actions">
                    <a href="#products" class="btn btn-primary">Ver Catálogo</a>
                    <a href="#categories" class="btn btn-secondary">Explorar Categorías</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="glass-card">
                    <i class="fas fa-shield-heart"></i>
                    <h3>Calidad Garantizada</h3>
                    <p>Todos nuestros productos cuentan con registro INVIMA vigente.</p>
                </div>
                <div class="glass-card floating">
                    <i class="fas fa-truck-fast"></i>
                    <h3>Domicilios Rápidos</h3>
                    <p>Recibe tus medicamentos en la puerta de tu casa.</p>
                </div>
            </div>
        </div>
        <div class="hero-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
        </div>
    </header>

    <main class="container">
        <section id="categories" class="section">
            <div class="section-header">
                <h2>Categorías Populares</h2>
                <p>Explora nuestra amplia gama de productos por especialidad</p>
            </div>
            <div class="category-scroll">
                <a href="<?= APP_URL ?>?route=home" class="category-chip <?= !isset($_GET['category']) ? 'active' : '' ?>">
                    <i class="fas fa-th-large"></i> Todos
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= APP_URL ?>?route=home&category=<?= $cat['id'] ?>#products" 
                       class="category-chip <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'active' : '' ?>"
                       style="--chip-color: <?= $cat['color'] ?? 'var(--primary)' ?>">
                        <i class="fas fa-folder"></i> <?= $cat['name'] ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="products" class="section">
            <div class="section-header flex-between">
                <div>
                    <h2>Nuestro Catálogo</h2>
                    <p>Mostrando <?= count($products) ?> productos disponibles</p>
                </div>
                <form action="<?= APP_URL ?>" method="GET" class="search-bar">
                    <input type="hidden" name="route" value="home">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Buscar medicamento..." value="<?= htmlspecialchars($search) ?>">
                </form>
            </div>

            <?php if (empty($products)): ?>
                <div class="empty-state">
                    <i class="fas fa-pills"></i>
                    <h3>No se encontraron productos</h3>
                    <p>Intenta con otros términos de búsqueda o selecciona una categoría diferente.</p>
                    <a href="<?= APP_URL ?>?route=home" class="btn btn-primary mt-2">Limpiar Búsqueda</a>
                </div>
            <?php else: ?>
                <div class="home-product-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="home-product-card">
                            <div class="p-badge" style="background: <?= $product['category_color'] ?? 'var(--primary)' ?>">
                                <?= $product['category_name'] ?>
                            </div>
                            <div class="p-icon">
                                <i class="fas fa-box-medical"></i>
                            </div>
                            <div class="p-details">
                                <h3><?= $product['name'] ?></h3>
                                <p class="generic"><?= $product['generic_name'] ?></p>
                                <div class="specs">
                                    <span><i class="fas fa-flask"></i> <?= $product['concentration'] ?></span>
                                    <span><i class="fas fa-tablets"></i> <?= $product['presentation'] ?></span>
                                </div>
                                <div class="p-footer">
                                    <span class="price"><?= formatCurrency($product['sale_price']) ?></span>
                                    <div class="d-flex gap-2">
                                        <a href="<?= APP_URL ?>?route=cart&action=add&id=<?= $product['id'] ?>" class="btn btn-icon btn-primary" title="Añadir al Carrito" style="display: flex; align-items: center; justify-content: center; width: 35px; height: 35px;">
                                            <i class="fas fa-cart-plus"></i>
                                        </a>
                                        <button class="btn btn-icon" title="Ver Detalles" style="display: flex; align-items: center; justify-content: center; width: 35px; height: 35px;">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= APP_URL ?>?route=home&page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>#products" 
                               class="<?= $page == $i ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>
    </main>

    <footer class="home-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-info">
                    <h3>PharmaCRM</h3>
                    <p>Líderes en gestión farmacéutica y cuidado de la salud en Colombia.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-contact">
                    <h4>Contacto</h4>
                    <p><i class="fas fa-phone"></i> +57 300 000 0000</p>
                    <p><i class="fas fa-envelope"></i> contacto@pharmacrm.com</p>
                    <p><i class="fas fa-location-dot"></i> Bogotá, Colombia</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> PharmaCRM. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>
</body>
</html>

