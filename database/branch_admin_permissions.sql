USE pharmacrm;

-- 1. Identificar el ID del rol branch_admin
SET @branch_admin_id = (SELECT id FROM roles WHERE name = 'branch_admin');

-- 2. Limpiar permisos previos si los hubiera (para re-ejecución segura)
DELETE FROM role_permissions WHERE role_id = @branch_admin_id;

-- 3. Asignar permisos al Administrador de Sucursal
-- Tiene permisos para casi todo en su sucursal, excepto gestión global de seguridad (RBAC) y gestión de sucursales.
INSERT INTO role_permissions (role_id, permission_id)
SELECT @branch_admin_id, id FROM permissions 
WHERE name IN (
    'view_dashboard',
    'view_inventory',
    'manage_inventory',
    'view_sales',
    'process_sales',
    'view_clients',
    'manage_clients',
    'view_reports',
    'export_reports',
    'view_users',
    'manage_users'
);

-- 4. Asegurarnos de que el Admin General (Role 1) tenga permiso para ver sucursales si creamos el permiso
-- Por ahora el Admin General entra por Auth::hasRole('admin') directamente en el controlador.
