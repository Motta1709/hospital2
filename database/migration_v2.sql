-- =====================================================
-- PharmaCRM - Migración V2
-- Reestructuración de Productos, Categorías y Proveedores
-- =====================================================

USE pharmacrm;

-- 1. Modificar Categorías
ALTER TABLE categories ADD COLUMN characteristics JSON DEFAULT NULL AFTER description;

-- 2. Crear Clasificaciones
CREATE TABLE IF NOT EXISTS classifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 3. Crear Subclasificaciones
CREATE TABLE IF NOT EXISTS subclassifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classification_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (classification_id) REFERENCES classifications(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Crear Tabla Muchos a Muchos: Producto - Proveedor (con Precio)
CREATE TABLE IF NOT EXISTS product_suppliers (
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, supplier_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Agregar nuevos campos a Productos
ALTER TABLE products 
ADD COLUMN classification_id INT DEFAULT NULL AFTER category_id,
ADD COLUMN subclassification_id INT DEFAULT NULL AFTER classification_id,
ADD COLUMN usage_instructions TEXT DEFAULT NULL AFTER description,
ADD COLUMN recommendations TEXT DEFAULT NULL AFTER usage_instructions,
ADD COLUMN unit_of_measure VARCHAR(50) DEFAULT NULL AFTER concentration,
ADD COLUMN quantity_per_unit DECIMAL(10,2) DEFAULT NULL AFTER unit_of_measure;

-- 6. Migrar datos existentes de Proveedores y Precios de Compra
INSERT INTO product_suppliers (product_id, supplier_id, purchase_price, is_primary)
SELECT id, supplier_id, purchase_price, 1
FROM products
WHERE supplier_id IS NOT NULL;

-- 7. Eliminar columnas obsoletas de Productos (una vez migrados los datos)
ALTER TABLE products DROP FOREIGN KEY products_ibfk_2; -- Foreign key a suppliers
ALTER TABLE products DROP COLUMN supplier_id;
ALTER TABLE products DROP COLUMN purchase_price;

-- 8. Agregar nuevas llaves foráneas a Productos
ALTER TABLE products ADD CONSTRAINT fk_product_classification FOREIGN KEY (classification_id) REFERENCES classifications(id) ON DELETE SET NULL;
ALTER TABLE products ADD CONSTRAINT fk_product_subclassification FOREIGN KEY (subclassification_id) REFERENCES subclassifications(id) ON DELETE SET NULL;
