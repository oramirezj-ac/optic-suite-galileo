<?php
// Obtenemos el ID del usuario de la URL. Si no existe, redirigimos.
$userId = $_GET['id'] ?? null;
if (!$userId) {
    header('Location: /index.php?page=users');
    exit();
}

// Buscamos los datos del usuario en la base de datos
$pdo = getConnection();
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Si no se encuentra el usuario, redirigimos
if (!$user) {
    header('Location: /index.php?page=users');
    exit();
}
?>

<div class="page-header">
    <h1>Editar Usuario: <?= htmlspecialchars($user['nombre_completo']) ?></h1>
    <a href="/index.php?page=users" class="btn btn-secondary">Cancelar</a>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <?php if (isset($_GET['error'])): ?>
                <div class="error-message" style="margin-bottom: 1.5rem;"><?= htmlspecialchars($_GET['error']) ?></div>
            <?php endif; ?>

            <form action="/user_handler.php?action=update" method="POST">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">

                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" value="<?= htmlspecialchars($user['nombre_completo']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Nombre de Usuario</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Nueva Contraseña (opcional)</label>
                    <input type="password" id="password" name="password" placeholder="Dejar en blanco para no cambiar">
                </div>
                <div class="form-group">
                    <label for="rol">Rol</label>
                    <select id="rol" name="rol">
                        <option value="vendedor" <?= $user['rol'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                        <option value="admin" <?= $user['rol'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    </select>
                </div>
                <div class="form-group-checkbox">
                    <input type="checkbox" id="activo" name="activo" value="1" <?= $user['activo'] ? 'checked' : '' ?>>
                    <label for="activo">Usuario Activo</label>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>