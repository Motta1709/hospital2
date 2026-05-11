<?php
require_once 'config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $db->exec('ALTER TABLE sales ADD COLUMN epayco_ref VARCHAR(100) DEFAULT NULL AFTER invoice_number');
    echo 'Columna epayco_ref añadida a sales\n';
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
