<?php

$host = 'localhost';
$db   = 'pharmacrm';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

$password_hash = '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi'; // Admin123

echo "Generando 10,000 usuarios basados en las dos piramides...\n";

// Piramide 1 (Sin clientes) - 5000 total
$p1_admins = 250;
$p1_supervisors = 750;
$p1_cashiers = 4000;

// Piramide 2 (Con clientes) - 5000 total
$p2_admins = 50;
$p2_supervisors = 200;
$p2_cashiers = 1250;
$p2_clients = 3500;

$pdo->beginTransaction();

try {
    $user_counter = 1;
    $client_counter = 1;

    $insertUser = $pdo->prepare("INSERT INTO users (role_id, username, email, password, full_name, phone) VALUES (?, ?, ?, ?, ?, ?)");
    $insertClient = $pdo->prepare("INSERT INTO clients (document_type, document_number, first_name, last_name, email, phone) VALUES ('CC', ?, ?, ?, ?, ?)");

    // Función auxiliar para insertar usuarios
    $addUser = function($role_id, $prefix) use ($insertUser, &$user_counter, $password_hash) {
        $username = "{$prefix}_{$user_counter}";
        $email = "{$username}@pharmacrm.local";
        $full_name = ucfirst($prefix) . " User {$user_counter}";
        $phone = '300' . str_pad($user_counter, 7, '0', STR_PAD_LEFT);
        
        $insertUser->execute([$role_id, $username, $email, $password_hash, $full_name, $phone]);
        $user_counter++;
    };

    // Pirámide 1
    echo "Insertando Pirámide Operativa (5000 usuarios)...\n";
    for ($i=0; $i<$p1_admins; $i++) $addUser(1, 'admin_p1');
    for ($i=0; $i<$p1_supervisors; $i++) $addUser(2, 'supervisor_p1');
    for ($i=0; $i<$p1_cashiers; $i++) $addUser(3, 'cajero_p1');

    // Pirámide 2 (Usuarios)
    echo "Insertando Pirámide Ecosistema (Usuarios)...\n";
    for ($i=0; $i<$p2_admins; $i++) $addUser(1, 'admin_p2');
    for ($i=0; $i<$p2_supervisors; $i++) $addUser(2, 'supervisor_p2');
    for ($i=0; $i<$p2_cashiers; $i++) $addUser(3, 'cajero_p2');

    // Pirámide 2 (Clientes)
    echo "Insertando Pirámide Ecosistema (Clientes)...\n";
    for ($i=0; $i<$p2_clients; $i++) {
        $doc_num = 1000000000 + $client_counter;
        $first = "ClienteP2";
        $last = "Num{$client_counter}";
        $email = "cliente{$client_counter}@email.local";
        $phone = '320' . str_pad($client_counter, 7, '0', STR_PAD_LEFT);
        
        $insertClient->execute([$doc_num, $first, $last, $email, $phone]);
        $client_counter++;
    }

    $pdo->commit();
    echo "Proceso completado exitosamente.\n";
    echo "- Total Usuarios insertados: " . ($user_counter - 1) . "\n";
    echo "- Total Clientes insertados: " . ($client_counter - 1) . "\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error durante la inserción: " . $e->getMessage() . "\n";
}
