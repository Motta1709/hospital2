<?php
/**
 * PharmaCRM - Vista Dashboard del Cliente
 * Carga dinamica de secciones segun parametro GET
 */
$section = $_GET['section'] ?? 'tienda';
$validSections = ['tienda','resumen','compras','domicilios','reservas','devoluciones','pqrsf','notificaciones','perfil'];
if (!in_array($section, $validSections)) $section = 'tienda';

$sectionFile = APP_ROOT . '/views/customer/sections/' . $section . '.php';
if (file_exists($sectionFile)) {
    include $sectionFile;
} else {
    include APP_ROOT . '/views/customer/sections/tienda.php';
}
?>
