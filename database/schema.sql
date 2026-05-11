SET NAMES utf8mb4;
-- =====================================================
-- PharmaCRM - CRM Farmacéutico para Colombia
-- Base de Datos: pharmacrm
-- Normalización: 3NF | Charset: UTF-8
-- =====================================================

CREATE DATABASE IF NOT EXISTS pharmacrm
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE pharmacrm;

-- =====================================================
-- TABLA: roles
-- =====================================================
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: users
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    avatar VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: categories (categorías de productos)
-- =====================================================
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    characteristics JSON DEFAULT NULL,
    icon VARCHAR(50) DEFAULT 'fa-pills',
    color VARCHAR(7) DEFAULT '#0D9488',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: classifications
-- =====================================================
CREATE TABLE IF NOT EXISTS classifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: subclassifications
-- =====================================================
CREATE TABLE IF NOT EXISTS subclassifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    classification_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (classification_id) REFERENCES classifications(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: suppliers (proveedores)
-- =====================================================
CREATE TABLE IF NOT EXISTS suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    nit VARCHAR(20) UNIQUE,
    contact_name VARCHAR(150),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: products (productos farmacéuticos)
-- =====================================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    classification_id INT DEFAULT NULL,
    subclassification_id INT DEFAULT NULL,
    barcode VARCHAR(50) UNIQUE,
    name VARCHAR(200) NOT NULL,
    generic_name VARCHAR(200),
    description TEXT,
    usage_instructions TEXT,
    recommendations TEXT,
    presentation VARCHAR(100),
    concentration VARCHAR(100),
    unit_of_measure VARCHAR(50),
    quantity_per_unit DECIMAL(10,2),
    lot_number VARCHAR(50),
    expiration_date DATE NOT NULL,
    sale_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    min_stock INT DEFAULT 10,
    max_stock INT DEFAULT 500,
    requires_prescription TINYINT(1) DEFAULT 0,
    supplier_id INT DEFAULT NULL,
    purchase_price DECIMAL(12,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL,
    FOREIGN KEY (classification_id) REFERENCES classifications(id) ON DELETE SET NULL,
    FOREIGN KEY (subclassification_id) REFERENCES subclassifications(id) ON DELETE SET NULL,
    INDEX idx_barcode (barcode),
    INDEX idx_expiration (expiration_date),
    INDEX idx_stock (stock),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: product_suppliers (Relación Muchos a Muchos)
-- =====================================================
CREATE TABLE IF NOT EXISTS product_suppliers (
    product_id INT NOT NULL,
    supplier_id INT NOT NULL,
    purchase_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    is_primary TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (product_id, supplier_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: clients (clientes / pacientes)
-- =====================================================
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    document_type ENUM('CC', 'TI', 'CE', 'PA', 'NIT') DEFAULT 'CC',
    document_number VARCHAR(20) UNIQUE,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    city VARCHAR(100),
    date_of_birth DATE,
    gender ENUM('M', 'F', 'O') DEFAULT NULL,
    allergies TEXT,
    medical_notes TEXT,
    loyalty_points INT DEFAULT 0,
    total_purchases DECIMAL(14,2) DEFAULT 0,
    visit_count INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_document (document_number),
    INDEX idx_name (first_name, last_name),
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: sales (encabezado de ventas)
-- =====================================================
CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT DEFAULT NULL,
    user_id INT NOT NULL,
    invoice_number VARCHAR(30) UNIQUE,
    subtotal DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(14,2) DEFAULT 0,
    tax_amount DECIMAL(14,2) DEFAULT 0,
    total DECIMAL(14,2) NOT NULL DEFAULT 0,
    payment_method ENUM('cash', 'card', 'transfer', 'mixed') DEFAULT 'cash',
    cash_received DECIMAL(14,2) DEFAULT 0,
    change_amount DECIMAL(14,2) DEFAULT 0,
    loyalty_points_earned INT DEFAULT 0,
    loyalty_points_used INT DEFAULT 0,
    status ENUM('completed', 'cancelled', 'pending') DEFAULT 'completed',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_invoice (invoice_number),
    INDEX idx_date (created_at),
    INDEX idx_client (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: sale_items (detalle de ventas)
-- =====================================================
CREATE TABLE IF NOT EXISTS sale_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    discount_percent DECIMAL(5,2) DEFAULT 0,
    subtotal DECIMAL(14,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id),
    INDEX idx_sale (sale_id),
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: loyalty_transactions (movimientos de puntos)
-- =====================================================
CREATE TABLE IF NOT EXISTS loyalty_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    sale_id INT DEFAULT NULL,
    points INT NOT NULL,
    type ENUM('earned', 'redeemed', 'expired', 'bonus', 'adjustment') NOT NULL,
    description VARCHAR(255),
    balance_after INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE SET NULL,
    INDEX idx_client (client_id),
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: promotions (promociones y campañas)
-- =====================================================
CREATE TABLE IF NOT EXISTS promotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed') NOT NULL DEFAULT 'percentage',
    discount_value DECIMAL(10,2) NOT NULL,
    category_id INT DEFAULT NULL,
    product_id INT DEFAULT NULL,
    min_purchase DECIMAL(12,2) DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    auto_apply TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: inventory_alerts (alertas de inventario)
-- =====================================================
CREATE TABLE IF NOT EXISTS inventory_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    alert_type ENUM('expiration_30', 'expiration_15', 'expiration_7', 'expired', 'low_stock', 'out_of_stock', 'overstock') NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    is_resolved TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_type (alert_type),
    INDEX idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABLA: audit_log (log de auditoría)
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT DEFAULT NULL,
    old_values JSON DEFAULT NULL,
    new_values JSON DEFAULT NULL,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_action (action),
    INDEX idx_entity (entity_type, entity_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATOS INICIALES (SEED)
-- =====================================================

-- Roles
INSERT INTO roles (name, description) VALUES
('admin', 'Administrador del sistema con acceso total'),
('supervisor', X'53757065727669736F7220636F6E2061636365736F2061207265706F727465732079206765737469C3B36E'),
('cashier', 'Cajero con acceso al POS y ventas');

-- Usuario admin por defecto (password: Admin123)
INSERT INTO users (role_id, username, email, password, full_name, phone) VALUES
(1, 'admin', 'admin@pharmacrm.local', '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi', 'Administrador General', '3001234567'),
(2, 'supervisor', 'supervisor@pharmacrm.local', '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi', X'4D6172C3AD612047617263C3AD61204CC3B370657A', '3009876543'),
(3, 'cajero1', 'cajero@pharmacrm.local', '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi', X'4361726C6F732050C3A972657A204D6F747461', '3005551234');

-- Categorías de productos farmacéuticos
INSERT INTO categories (name, description, icon, color) VALUES
(X'416E616C67C3A97369636F73', 'Medicamentos para el alivio del dolor', 'fa-capsules', '#EF4444'),
(X'416E74696269C3B37469636F73', 'Medicamentos contra infecciones bacterianas', 'fa-shield-virus', '#F59E0B'),
('Antiinflamatorios', X'4D65646963616D656E746F732070617261207265647563697220696E666C616D616369C3B36E', 'fa-fire', '#F97316'),
('Antihipertensivos', X'4D65646963616D656E746F732070617261206C61207072657369C3B36E20617274657269616C', 'fa-heart-pulse', '#EC4899'),
(X'416E746964696162C3A97469636F73', 'Medicamentos para el control de diabetes', 'fa-droplet', '#8B5CF6'),
('Vitaminas y Suplementos', 'Suplementos nutricionales y vitaminas', 'fa-leaf', '#22C55E'),
(X'4465726D61746F6CC3B36769636F73', 'Productos para cuidado de la piel', 'fa-hand-dots', '#06B6D4'),
('Gastrointestinales', 'Medicamentos para el sistema digestivo', 'fa-stomach', '#0D9488'),
('Respiratorios', X'4D65646963616D656E746F7320706172612076C3AD61732072657370697261746F72696173', 'fa-lungs', '#3B82F6'),
(X'4F6674616C6D6F6CC3B36769636F73', 'Productos para la salud visual', 'fa-eye', '#6366F1'),
('Productos de Aseo', 'Higiene personal y limpieza', 'fa-soap', '#14B8A6'),
(X'446973706F73697469766F73204DC3A96469636F73', X'45717569706F73207920646973706F73697469766F732064652075736F206DC3A96469636F', 'fa-stethoscope', '#64748B');

-- Proveedores colombianos
INSERT INTO suppliers (name, nit, contact_name, phone, email, city) VALUES
(X'446973747269627563696F6E6573204661726D6163C3A97574696361732064656C20537572', '900123456-1', X'4A75616E204865726EC3A16E64657A', '3101234567', 'ventas@distrifarsur.co', 'Florencia'),
(X'44726F67756572C3AD6120436F6E74696E656E74616C', '800654321-2', X'416E61204D6172C3AD61205275697A', '3209876543', 'pedidos@continental.co', X'426F676F74C3A1'),
('Laboratorios Genfar Colombia', '860012345-3', X'506564726F204D617274C3AD6E657A', '3115556789', 'comercial@genfar.co', 'Cali'),
(X'5465636E6F7175C3AD6D6963617320532E412E', '890100123-4', X'4C617572612053C3A16E6368657A', '3178889999', 'distribuidores@tq.co', 'Cali'),
('Audifarma S.A.', '816001182-5', X'526F626572746F2047C3B36D657A', '3164445555', 'logistica@audifarma.co', 'Pereira');

-- Productos farmacéuticos de ejemplo
INSERT INTO products (category_id, supplier_id, barcode, name, generic_name, presentation, concentration, lot_number, expiration_date, purchase_price, sale_price, stock, min_stock, requires_prescription) VALUES
(1, 3, '7702605161430', X'41636574616D696E6F66C3A96E204D4B', X'41636574616D696E6F66C3A96E', 'Caja x 100 tabletas', '500mg', 'LOT-2025-001', '2027-06-15', 8500.00, 12500.00, 150, 20, 0),
(1, 4, '7702605161447', 'Ibuprofeno Genfar', 'Ibuprofeno', 'Caja x 50 tabletas', '400mg', 'LOT-2025-002', '2027-03-20', 6200.00, 9800.00, 85, 15, 0),
(2, 3, '7702605161454', 'Amoxicilina MK', 'Amoxicilina', X'43616A6120782032312063C3A17073756C6173', '500mg', 'LOT-2025-003', '2026-12-10', 12000.00, 18500.00, 40, 10, 1),
(2, 4, '7702605161461', 'Azitromicina Genfar', 'Azitromicina', 'Caja x 3 tabletas', '500mg', 'LOT-2025-004', '2026-11-25', 15000.00, 22000.00, 30, 8, 1),
(3, 3, '7702605161478', 'Diclofenaco MK', 'Diclofenaco', 'Caja x 30 tabletas', '50mg', 'LOT-2025-005', '2027-08-30', 5500.00, 8500.00, 60, 10, 0),
(3, 4, '7702605161485', 'Naproxeno Genfar', 'Naproxeno', 'Caja x 20 tabletas', '250mg', 'LOT-2025-006', '2026-09-15', 7000.00, 11000.00, 45, 10, 0),
(4, 5, '7702605161492', X'4C6F73617274C3A16E204D4B', X'4C6F73617274C3A16E', 'Caja x 30 tabletas', '50mg', 'LOT-2025-007', '2027-05-12', 9500.00, 15000.00, 70, 15, 1),
(4, 3, '7702605161508', 'Enalapril Genfar', 'Enalapril', 'Caja x 30 tabletas', '20mg', 'LOT-2025-008', '2027-01-20', 8000.00, 12800.00, 55, 12, 1),
(5, 4, '7702605161515', 'Metformina MK', 'Metformina', 'Caja x 30 tabletas', '850mg', 'LOT-2025-009', '2027-04-18', 7500.00, 11500.00, 90, 20, 1),
(5, 5, '7702605161522', 'Glibenclamida Genfar', 'Glibenclamida', 'Caja x 30 tabletas', '5mg', 'LOT-2025-010', '2026-10-05', 6000.00, 9200.00, 35, 8, 1),
(6, 1, '7702605161539', 'Vitamina C MK', X'C3816369646F20417363C3B3726269636F', 'Frasco x 100 tabletas', '500mg', 'LOT-2025-011', '2028-02-28', 10000.00, 16500.00, 120, 25, 0),
(6, 4, '7702605161546', 'Ensure Advance', 'Suplemento Nutricional', 'Lata 400g', 'Polvo', 'LOT-2025-012', '2027-07-10', 45000.00, 62000.00, 25, 5, 0),
(7, 2, '7702605161553', 'Betametasona Crema', 'Betametasona', 'Tubo x 40g', '0.05%', 'LOT-2025-013', '2026-08-20', 11000.00, 17000.00, 30, 5, 1),
(8, 3, '7702605161560', 'Omeprazol MK', 'Omeprazol', X'43616A6120782031342063C3A17073756C6173', '20mg', 'LOT-2025-014', '2027-09-15', 6500.00, 10200.00, 100, 20, 0),
(9, 4, '7702605161577', 'Loratadina Genfar', 'Loratadina', 'Caja x 10 tabletas', '10mg', 'LOT-2025-015', '2027-11-30', 4500.00, 7200.00, 80, 15, 0),
(10, 2, '7702605161584', X'4CC3A16772696D6173204172746966696369616C6573', 'Carboximetilcelulosa', 'Frasco x 15ml', '0.5%', 'LOT-2025-016', '2026-07-01', 13000.00, 19500.00, 20, 5, 0),
(11, 1, '7702605161591', X'4A6162C3B36E20416E746962616374657269616C', X'547269636C6F73C3A16E', 'Barra x 120g', '1%', 'LOT-2025-017', '2028-01-15', 3500.00, 5800.00, 200, 30, 0),
(12, 5, '7702605161607', X'54656E7369C3B36D6574726F204469676974616C', 'N/A', 'Unidad', 'Digital de brazo', 'LOT-2025-018', '2030-12-31', 55000.00, 89000.00, 8, 3, 0),
-- Productos próximos a vencer (para demo de alertas)
(1, 3, '7702605161614', X'41636574616D696E6F66C3A96E2047656EC3A97269636F', X'41636574616D696E6F66C3A96E', 'Caja x 20 tabletas', '500mg', 'LOT-2024-099', '2026-05-10', 3000.00, 4500.00, 25, 10, 0),
(8, 4, '7702605161621', 'Ranitidina Genfar', 'Ranitidina', 'Caja x 20 tabletas', '150mg', 'LOT-2024-100', '2026-05-25', 5000.00, 7800.00, 18, 5, 0);

-- Clientes de ejemplo
INSERT INTO clients (document_type, document_number, first_name, last_name, email, phone, city, date_of_birth, gender, allergies, loyalty_points, total_purchases, visit_count) VALUES
('CC', '1006789012', X'416E61204D6172C3AD61', X'476F6E7AC3A16C657A205275697A', 'ana.gonzalez@email.com', '3201234567', 'Florencia', '1985-03-15', 'F', 'Penicilina', 450, 1250000.00, 28),
('CC', '1007890123', 'Carlos Eduardo', X'4D617274C3AD6E657A204CC3B370657A', 'carlos.martinez@email.com', '3109876543', 'Florencia', '1972-08-22', 'M', NULL, 780, 2100000.00, 45),
('CC', '1008901234', X'4D6172C3AD61204665726E616E6461', X'50C3A972657A2053C3A16E6368657A', 'maria.perez@email.com', '3155554321', 'Florencia', '1990-11-10', 'F', 'Sulfonamidas, Aspirina', 320, 890000.00, 18),
('CC', '1009012345', 'Jorge Luis', X'52616DC3AD72657A204D6F747461', 'jorge.ramirez@email.com', '3181112233', 'Florencia', '1968-05-03', 'M', NULL, 1200, 3500000.00, 62),
('TI', '1010123456', 'Valentina', X'4865726EC3A16E64657A2043617374726F', NULL, '3177778899', 'Florencia', '2010-07-19', 'F', 'Ibuprofeno', 50, 120000.00, 4),
('CC', '1011234567', 'Roberto', X'47C3B36D657A20566172676173', 'roberto.gomez@email.com', '3146665544', X'53616E20566963656E74652064656C2043616775C3A16E', '1978-12-01', 'M', NULL, 600, 1800000.00, 35),
('CE', '1012345678', X'4C7563C3AD6120456C656E61', X'546F727265732044C3AD617A', 'lucia.torres@email.com', '3133332211', 'Florencia', '1995-09-28', 'F', 'Metamizol', 190, 560000.00, 12);

-- Ventas de ejemplo
INSERT INTO sales (client_id, user_id, invoice_number, subtotal, discount_amount, tax_amount, total, payment_method, cash_received, change_amount, loyalty_points_earned, status, created_at) VALUES
(1, 3, 'FV-2026-0001', 31000.00, 0, 0, 31000.00, 'cash', 50000.00, 19000.00, 31, 'completed', '2026-04-20 09:15:00'),
(2, 3, 'FV-2026-0002', 48500.00, 2425.00, 0, 46075.00, 'card', 46075.00, 0, 46, 'completed', '2026-04-20 10:30:00'),
(4, 3, 'FV-2026-0003', 89000.00, 0, 0, 89000.00, 'transfer', 89000.00, 0, 89, 'completed', '2026-04-21 14:20:00'),
(NULL, 3, 'FV-2026-0004', 12500.00, 0, 0, 12500.00, 'cash', 15000.00, 2500.00, 0, 'completed', '2026-04-22 08:45:00'),
(3, 3, 'FV-2026-0005', 28700.00, 0, 0, 28700.00, 'cash', 30000.00, 1300.00, 29, 'completed', '2026-04-22 16:10:00'),
(1, 3, 'FV-2026-0006', 62000.00, 3100.00, 0, 58900.00, 'mixed', 58900.00, 0, 59, 'completed', '2026-04-23 09:00:00');

-- Items de venta
INSERT INTO sale_items (sale_id, product_id, quantity, unit_price, discount_percent, subtotal) VALUES
(1, 1, 1, 12500.00, 0, 12500.00),
(1, 15, 1, 7200.00, 0, 7200.00),
(1, 14, 1, 10200.00, 0, 10200.00),
(2, 7, 2, 15000.00, 0, 30000.00),
(2, 9, 1, 11500.00, 0, 11500.00),
(2, 11, 1, 16500.00, 5, 7000.00),
(3, 18, 1, 89000.00, 0, 89000.00),
(4, 1, 1, 12500.00, 0, 12500.00),
(5, 5, 2, 8500.00, 0, 17000.00),
(5, 15, 1, 7200.00, 0, 7200.00),
(5, 19, 1, 4500.00, 0, 4500.00),
(6, 12, 1, 62000.00, 5, 58900.00);

-- Transacciones de lealtad
INSERT INTO loyalty_transactions (client_id, sale_id, points, type, description, balance_after) VALUES
(1, 1, 31, 'earned', 'Compra FV-2026-0001', 481),
(2, 2, 46, 'earned', 'Compra FV-2026-0002', 826),
(4, 3, 89, 'earned', 'Compra FV-2026-0003', 1289),
(3, 5, 29, 'earned', 'Compra FV-2026-0005', 349),
(1, 6, 59, 'earned', 'Compra FV-2026-0006', 540),
(2, NULL, 100, 'bonus', 'Bono de bienvenida programa VIP', 926);

-- Promociones activas
INSERT INTO promotions (name, description, discount_type, discount_value, category_id, start_date, end_date, is_active, auto_apply) VALUES
('Mes de la Salud', 'Descuento en vitaminas y suplementos durante abril', 'percentage', 10.00, 6, '2026-04-01', '2026-04-30', 1, 1),
(X'44C3AD612064656C20486970657274656E736F', 'Descuento especial en antihipertensivos', 'percentage', 15.00, 4, '2026-04-17', '2026-05-17', 1, 0),
(X'4C6971756964616369C3B36E205072C3B378696D6F2056656E63696D69656E746F', X'4465736375656E746F206175746F6DC3A17469636F20706F722063657263616EC3AD6120612066656368612064652076656E63696D69656E746F', 'percentage', 25.00, NULL, '2026-01-01', '2026-12-31', 1, 1);
