<?php
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT id, username, nombre_completo, email, rol, activo FROM users ORDER BY nombre_completo ASC");
$stmt->execute();
$users = $stmt->fetchAll();

$successMessage = '';
if (isset($_GET['success'])) {
    $messages = [
        'created' => 'Usuario creado exitosamente.',
        'updated' => 'Usuario actualizado exitosamente.',
        'deactivated' => 'Usuario desactivado exitosamente.',
        'activated' => 'Usuario activado exitosamente.'
    ];
    $successMessage = $messages[$_GET['success']] ?? 'Acción completada.';
}
$errorMessage = '';
// ... (código de errores sin cambios) ...
?>

<div class="page-header">
    <h1>Gestión de Usuarios</h1>
    <a href="/index.php?page=users_create" class="btn btn-primary">➕ Agregar Nuevo Usuario</a>
</div>

<?php if ($successMessage): ?><div class="alert alert-success"><?= $successMessage ?></div><?php endif; ?>
<?php if ($errorMessage): ?><div class="alert alert-danger"><?= $errorMessage ?></div><?php endif; ?>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <table>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['nombre_completo']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= ucfirst(htmlspecialchars($user['rol'])) ?></td>
                            <td><span class="badge <?= $user['activo'] ? 'badge-success' : 'badge-danger' ?>"><?= $user['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                            <td class="actions-cell">
                                <a href="/index.php?page=users_edit&id=<?= $user['id'] ?>" class="btn btn-secondary">Editar</a>
                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                    <?php if ($user['activo']): ?>
                                        <a href="/index.php?page=users_delete&id=<?= $user['id'] ?>&action=deactivate" class="btn btn-danger">Desactivar</a>
                                    <?php else: ?>
                                        <a href="/index.php?page=users_delete&id=<?= $user['id'] ?>&action=activate" class="btn btn-success">Activar</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>