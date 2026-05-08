<?php

$operative_dir = 'database/seeds/operative';
$ecosystem_dir = 'database/seeds/ecosystem';

if (!is_dir($operative_dir)) mkdir($operative_dir, 0777, true);
if (!is_dir($ecosystem_dir)) mkdir($ecosystem_dir, 0777, true);

$password_hash = '$2y$12$l2fAUJkATgkhvrxJHLsWqeOEbl/TizUEgTUlVsomEX50R8geI2lEi'; // Admin123

function save_sql_file($path, $sql) {
    file_put_contents($path, "USE pharmacrm;\n" . $sql);
}

function generate_users_sql_chunk($count, $role_id, $prefix, $start_index) {
    global $password_hash;
    $sql = "INSERT INTO users (role_id, username, email, password, full_name, phone) VALUES\n";
    $values = [];
    for ($i = 0; $i < $count; $i++) {
        $idx = $start_index + $i;
        $username = "{$prefix}_{$idx}";
        $email = "{$username}@pharmacrm.local";
        $full_name = ucfirst($prefix) . " User {$idx}";
        $phone = '300' . str_pad($idx, 7, '0', STR_PAD_LEFT);
        $values[] = "($role_id, '$username', '$email', '$password_hash', '$full_name', '$phone')";
    }
    return $sql . implode(",\n", $values) . ";\n";
}

function generate_clients_sql_chunk($count, $start_index) {
    $sql = "INSERT INTO clients (document_type, document_number, first_name, last_name, email, phone) VALUES\n";
    $values = [];
    for ($i = 0; $i < $count; $i++) {
        $idx = $start_index + $i;
        $doc_num = 3000000000 + $idx; // Different range to avoid collisions
        $first = "ClienteEco";
        $last = "Gen{$idx}";
        $email = "eco_client_{$idx}@email.local";
        $phone = '315' . str_pad($idx, 7, '0', STR_PAD_LEFT);
        $values[] = "('CC', '$doc_num', '$first', '$last', '$email', '$phone')";
    }
    return $sql . implode(",\n", $values) . ";\n";
}

// Generar Pirámide Operativa (Separada)
echo "Dividiendo SQL de Pirámide Operativa...\n";
save_sql_file("$operative_dir/1_admins.sql", generate_users_sql_chunk(250, 1, 'op_admin', 1));
save_sql_file("$operative_dir/2_supervisors.sql", generate_users_sql_chunk(750, 2, 'op_super', 1));
save_sql_file("$operative_dir/3_cashiers.sql", generate_users_sql_chunk(4000, 3, 'op_cajero', 1));

// Generar Pirámide Ecosistema (Separada)
echo "Dividiendo SQL de Pirámide Ecosistema...\n";
save_sql_file("$ecosystem_dir/1_admins.sql", generate_users_sql_chunk(50, 1, 'eco_admin', 1));
save_sql_file("$ecosystem_dir/2_supervisors.sql", generate_users_sql_chunk(200, 2, 'eco_super', 1));
save_sql_file("$ecosystem_dir/3_cashiers.sql", generate_users_sql_chunk(1250, 3, 'eco_cajero', 1));
save_sql_file("$ecosystem_dir/4_clients.sql", generate_clients_sql_chunk(3500, 1));

echo "Archivos SQL individuales creados exitosamente.\n";
?>
