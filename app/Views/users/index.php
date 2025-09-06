<?php
// Obtenemos la conexión a la BD
$pdo = getConnection();

// Preparamos la consulta para obtener todos los usuarios
$stmt = $pdo->prepare("SELECT id, username, nombre_completo, email, rol, activo FROM users ORDER BY nombre_completo ASC");
$stmt->execute();
$users = $stmt->fetchAll();
?>

<div class="page-header">
    <h1>Gestión de Usuarios</h1>
    <a href="#" class="btn btn-primary">➕ Agregar Nuevo Usuario</a>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($user['rol'])) ?></td>
                            <td>
                                <span class="badge <?= $user['activo'] ? 'badge-success' : 'badge-danger' ?>">
                                    <?= $user['activo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </td>
                            <td>
                                <a href="#" class="btn btn-secondary">Editar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>