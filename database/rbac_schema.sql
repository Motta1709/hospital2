USE pharmacrm;

-- =====================================================
-- TABLA: modules (Agrupación de permisos)
-- =====================================================
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(50) DEFAULT 'fa-folder',
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABLA: permissions (Funciones específicas)
-- =====================================================
CREATE TABLE IF NOT EXISTS permissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    name VARCHAR(100) NOT NULL UNIQUE, -- Slug: 'create_user'
    display_name VARCHAR(150) NOT NULL, -- 'Crear Usuario'
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABLA: role_permissions (Relación N:N)
-- =====================================================
CREATE TABLE IF NOT EXISTS role_permissions (
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- SEED: Módulos y Permisos Iniciales
-- =====================================================

-- Insertar Módulos
INSERT INTO modules (name, icon, sort_order) VALUES
('Dashboard', 'fa-gauge', 1),
('Inventario', 'fa-boxes-stacked', 2),
('Ventas', 'fa-cash-register', 3),
('Clientes', 'fa-users', 4),
('Reportes', 'fa-chart-pie', 5),
('Usuarios y Seguridad', 'fa-shield-halved', 6);

-- Insertar Permisos
-- Dashboard (ID 1)
INSERT INTO permissions (module_id, name, display_name) VALUES
(1, 'view_dashboard', 'Ver Dashboard');

-- Inventario (ID 2)
INSERT INTO permissions (module_id, name, display_name) VALUES
(2, 'view_inventory', 'Ver Inventario'),
(2, 'manage_inventory', 'Gestionar Inventario (Crear/Editar)'),
(2, 'delete_products', 'Eliminar Productos');

-- Ventas (ID 3)
INSERT INTO permissions (module_id, name, display_name) VALUES
(3, 'view_sales', 'Ver Historial de Ventas'),
(3, 'process_sales', 'Realizar Ventas (POS)'),
(3, 'cancel_sales', 'Anular Ventas');

-- Clientes (ID 4)
INSERT INTO permissions (module_id, name, display_name) VALUES
(4, 'view_clients', 'Ver Clientes'),
(4, 'manage_clients', 'Gestionar Clientes (Crear/Editar)'),
(4, 'delete_clients', 'Eliminar Clientes');

-- Reportes (ID 5)
INSERT INTO permissions (module_id, name, display_name) VALUES
(5, 'view_reports', 'Ver Reportes Generales'),
(5, 'export_reports', 'Exportar Reportes (Excel/PDF)');

-- Usuarios y Seguridad (ID 6)
INSERT INTO permissions (module_id, name, display_name) VALUES
(6, 'view_users', 'Ver Usuarios'),
(6, 'manage_users', 'Gestionar Usuarios (Crear/Editar)'),
(6, 'manage_rbac', 'Gestionar Roles y Permisos');

-- =====================================================
-- ASIGNACIÓN INICIAL DE PERMISOS
-- =====================================================

-- Admin (Role 1): Todos los permisos
INSERT INTO role_permissions (role_id, permission_id)
SELECT 1, id FROM permissions;

-- Supervisor (Role 2): Casi todos excepto gestión de seguridad
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions WHERE name NOT IN ('manage_rbac', 'delete_products', 'delete_clients');

-- Cajero (Role 3): Solo ventas y vista básica de productos/clientes
INSERT INTO role_permissions (role_id, permission_id)
SELECT 3, id FROM permissions WHERE name IN ('view_dashboard', 'view_inventory', 'process_sales', 'view_sales', 'view_clients', 'manage_clients');
