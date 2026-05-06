-- =====================================================
-- PharmaCRM - Configuración de Compatibilidad UTF-8mb4
-- Garantiza soporte para caracteres internacionales y símbolos
-- =====================================================

USE pharmacrm;

-- 1. Configurar base de datos
ALTER DATABASE pharmacrm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 2. Convertir tablas existentes
ALTER TABLE roles CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE categories CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE classifications CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE subclassifications CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE suppliers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE products CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE product_suppliers CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE clients CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE sales CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE sale_items CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE loyalty_transactions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE promotions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE inventory_alerts CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE audit_log CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 3. Asegurar que las conexiones futuras usen este charset
SET NAMES 'utf8mb4';
SET CHARACTER SET 'utf8mb4';
