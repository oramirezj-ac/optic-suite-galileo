<?php
$userId = $_GET['id'] ?? null;
// CAMBIO: Obtenemos también la acción a realizar
$action = $_GET['action'] ?? 'deactivate'; 

if (!$userId) {
    header('Location: /index.php?page=users');
    exit();
}

$pdo = getConnection();
$stmt = $pdo->prepare("SELECT nombre_completo, activo FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: /index.php?page=users');
    exit();
}

// Adaptamos los textos y estilos según la acción
if ($action === 'activate') {
    $title = 'Confirmar Activación';
    $message = '¿Estás seguro de que quieres activar al usuario';
    $buttonText = 'Sí, activar usuario';
    $buttonClass = 'btn-success';
} else {
    $action = 'deactivate'; // Por seguridad, si no es 'activate', es 'deactivate'
    $title = 'Confirmar Desactivación';
    $message = '¿Estás seguro de que quieres desactivar al usuario';
    $buttonText = 'Sí, desactivar usuario';
    $buttonClass = 'btn-danger';
}
?>

<div class="page-header">
    <h1><?= $title ?></h1>
</div>

<div class="page-content">
    <div class="card">
        <div class="card-body">
            <p class="emphasis-text">
                <?= $message ?> <strong><?= htmlspecialchars($user['nombre_completo']) ?></strong>?
            </p>
            
           <form action="/user_handler.php?action=<?= $action ?>" method="POST" class="mt-2">
                <input type="hidden" name="id" value="<?= $userId ?>">

               <div class="form-actions actions-clean">
                    <button type="submit" class="btn <?= $buttonClass ?>"><?= $buttonText ?></button>
                    <a href="/index.php?page=users" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>