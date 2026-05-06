<div class="toolbar mb-4">
    <div class="search-bar">
        <i class="fas fa-search"></i>
        <input type="text" id="userSearchInput" placeholder="Buscar usuario o nombre..." onkeyup="filterUsers()">
    </div>
    <a href="<?= APP_URL ?>?route=users&action=create" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nuevo Usuario
    </a>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users-gear"></i> Gestión de Usuarios</h3>
    </div>
    <div class="table-wrapper">
        <table class="table" id="usersTable">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Nombre Completo</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Último Acceso</th>
                    <th class="text-right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['users'] as $u): ?>
                    <tr>
                        <td>
                            <div class="fw-bold"><?= htmlspecialchars($u['username']) ?></div>
                        </td>
                        <td><?= htmlspecialchars($u['full_name']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <span class="badge badge-outline-primary"><?= ucfirst($u['role_name']) ?></span>
                        </td>
                        <td>
                            <?php if ($u['is_active']): ?>
                                <span class="badge badge-success">Activo</span>
                            <?php else: ?>
                                <span class="badge badge-secondary">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted small">
                            <?= $u['last_login'] ? date('d/m/Y H:i', strtotime($u['last_login'])) : 'Nunca' ?>
                        </td>
                        <td class="text-right">
                            <div class="btn-group gap-2">
                                <a href="<?= APP_URL ?>?route=users&action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($u['id'] != Auth::id()): ?>
                                    <a href="<?= APP_URL ?>?route=users&action=delete&id=<?= $u['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('¿Eliminar usuario?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterUsers() {
    let input = document.getElementById('userSearchInput');
    let filter = input.value.toLowerCase();
    let table = document.getElementById('usersTable');
    let tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let tdUsername = tr[i].getElementsByTagName('td')[0];
        let tdFullName = tr[i].getElementsByTagName('td')[1];
        if (tdUsername || tdFullName) {
            let txtUsername = tdUsername.textContent || tdUsername.innerText;
            let txtFullName = tdFullName.textContent || tdFullName.innerText;
            if (txtUsername.toLowerCase().indexOf(filter) > -1 || txtFullName.toLowerCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}
</script>

