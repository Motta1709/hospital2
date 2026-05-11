<?php
/**
 * PharmaCRM - Gestor de Configuración Segura
 * Lee desde la BD y cachea en un archivo JSON para máxima velocidad
 */

class Settings {
    private static $cacheFile = APP_ROOT . '/config/settings_cache.json';

    /**
     * Carga las configuraciones del grupo especificado (ej. epayco)
     */
    public static function loadGroup($group) {
        $settings = [];
        $cacheData = [];

        // 1. Intentar leer desde el cache JSON primero
        if (file_exists(self::$cacheFile)) {
            $json = file_get_contents(self::$cacheFile);
            $cacheData = json_decode($json, true) ?: [];
            if (isset($cacheData[$group])) {
                return $cacheData[$group];
            }
        }

        // 2. Si no esta en cache, leer desde la base de datos (Consulta Preparada)
        try {
            require_once APP_ROOT . '/config/Database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("SELECT setting_key, setting_value FROM system_settings WHERE setting_group = :group");
            $stmt->execute([':group' => $group]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }

            // 3. Guardar en el cache JSON
            if (!empty($settings)) {
                $cacheData[$group] = $settings;
                // Escribir en el archivo de forma segura
                file_put_contents(self::$cacheFile, json_encode($cacheData, JSON_PRETTY_PRINT));
            }

        } catch (Exception $e) {
            // Manejo silencioso o log en caso de fallo crítico
            error_log("Error cargando settings del grupo $group: " . $e->getMessage());
        }

        return $settings;
    }

    /**
     * Limpia la cache de configuraciones para forzar recarga desde BD
     */
    public static function clearCache() {
        if (file_exists(self::$cacheFile)) {
            unlink(self::$cacheFile);
        }
    }
}
