-- =====================================================
-- PharmaCRM - Módulo de Sucursales (Locales)
-- =====================================================

USE pharmacrm;

-- 1. Crear tabla de sucursales
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    nit VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Insertar sucursal por defecto (Sede Principal)
INSERT INTO branches (name, address, city, phone) 
VALUES ('Sede Principal', 'Calle 1 #2-3', 'Florencia', '3001234567')
ON DUPLICATE KEY UPDATE name=name;

-- 3. Modificar tabla de usuarios para asociarlos a una sucursal
ALTER TABLE users ADD COLUMN branch_id INT DEFAULT 1 AFTER role_id;
ALTER TABLE users ADD CONSTRAINT fk_user_branch FOREIGN KEY (branch_id) REFERENCES branches(id);

-- 4. Modificar tabla de productos para segmentar inventario por sucursal
-- Nota: En un sistema más complejo usaríamos una tabla intermedia, 
-- pero para cumplimiento de requerimiento directo añadimos branch_id.
ALTER TABLE products ADD COLUMN branch_id INT DEFAULT 1 AFTER subclassification_id;
ALTER TABLE products ADD CONSTRAINT fk_product_branch FOREIGN KEY (branch_id) REFERENCES branches(id);

-- 5. Modificar tabla de ventas para rastrear origen
ALTER TABLE sales ADD COLUMN branch_id INT DEFAULT 1 AFTER user_id;
ALTER TABLE sales ADD CONSTRAINT fk_sale_branch FOREIGN KEY (branch_id) REFERENCES branches(id);

-- 6. Modificar tabla de movimientos de inventario
-- Si existe product_batches, también debería tener branch_id
ALTER TABLE product_batches ADD COLUMN branch_id INT DEFAULT 1 AFTER product_id;
ALTER TABLE product_batches ADD CONSTRAINT fk_batch_branch FOREIGN KEY (branch_id) REFERENCES branches(id);

-- 7. Crear el rol de Administrador de Sucursal si no existe
INSERT INTO roles (name, description) VALUES 
('branch_admin', 'Administrador de sucursal con permisos limitados a su sede')
ON DUPLICATE KEY UPDATE name=name;
