<?php
/**
 * PharmaCRM - Controlador Central de API
 */
class ApiController {
    
    public function index() {
        // Este método puede ser invocado si no se especifica una entidad
        jsonResponse(['message' => 'PharmaCRM API v1.0', 'status' => 'online']);
    }

    /**
     * El manejo de entidades se realiza actualmente en index.php por simplicidad,
     * pero se puede mover aquí en el futuro.
     */
}
