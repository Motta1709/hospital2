SET NAMES utf8mb4;
-- =====================================================
-- PharmaCRM - Sistema de Kardex y FIFO/PEPS
-- Implementación de Lotes y Movimientos de Inventario
-- =====================================================

USE pharmacrm;

-- 1. Tabla de Lotes de Productos (Batches)
-- Permite manejar múltiples entradas de un mismo producto con diferentes vencimientos/costos
CREATE TABLE IF NOT EXISTS product_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    lot_number VARCHAR(50) NOT NULL,
    expiration_date DATE NOT NULL,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    initial_quantity INT NOT NULL,
    current_quantity INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_expiration (expiration_date),
    INDEX idx_product_batch (product_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla de Movimientos de Inventario (Kardex)
-- Registro histórico de todas las entradas y salidas
CREATE TABLE IF NOT EXISTS inventory_movements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    type ENUM('ENTRY', 'EXIT', 'ADJUSTMENT', 'RETURN') NOT NULL,
    quantity INT NOT NULL,
    balance_after INT NOT NULL,
    reference_type ENUM('SALE', 'PURCHASE', 'ADJUSTMENT', 'INITIAL') DEFAULT 'ADJUSTMENT',
    reference_id INT DEFAULT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (batch_id) REFERENCES product_batches(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_product_kardex (product_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Migración de datos existentes
-- Crear un lote inicial para los productos que ya tienen stock
INSERT INTO product_batches (product_id, lot_number, expiration_date, initial_quantity, current_quantity)
SELECT id, lot_number, expiration_date, stock, stock 
FROM products 
WHERE stock > 0;

-- Registrar el movimiento inicial en el Kardex
INSERT INTO inventory_movements (product_id, batch_id, user_id, type, quantity, balance_after, reference_type, notes)
SELECT p.id, b.id, 1, 'ENTRY', p.stock, p.stock, 'INITIAL', 'Carga inicial de inventario heredado'
FROM products p
JOIN product_batches b ON p.id = b.product_id
WHERE p.stock > 0;
