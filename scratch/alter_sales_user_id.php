<?php
require_once 'config/database.php';
try {
    $db = Database::getInstance()->getConnection();
    $db->exec('ALTER TABLE sales MODIFY user_id INT NULL');
    echo 'Columna user_id modificada para permitir NULL en sales\n';
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
