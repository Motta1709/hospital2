<?php
$host = 'localhost';
$db   = 'pharmacrm';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Inyectando Pirámide Operativa...\n";
    $sql_op = file_get_contents('database/seeds/operative/operative_seeds.sql');
    $pdo->exec($sql_op);

    echo "Inyectando Pirámide Ecosistema...\n";
    $sql_eco = file_get_contents('database/seeds/ecosystem/ecosystem_seeds.sql');
    $pdo->exec($sql_eco);

    echo "Inyección completada exitosamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
