<?php
/* ==========================================================================
   Controlador para la Gestión de Usuarios (CRUD)
   ========================================================================== */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';

if ($_SESSION['user_role'] !== 'admin') {
    header('Location: /index.php?page=dashboard&error=auth');
    exit();
}

$action = $_GET['action'] ?? 'list';

switch ($action) {
    // Los casos 'store' y 'update' permanecen sin cambios...
    case 'store':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ... (código existente)
            $nombre = $_POST['nombre_completo'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 'vendedor';
            $activo = isset($_POST['activo']) ? 1 : 0;
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            try {
                $pdo = getConnection();
                $stmt = $pdo->prepare("INSERT INTO users (nombre_completo, username, email, password, rol, activo) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nombre, $username, $email, $hashedPassword, $rol, $activo]);
                header('Location: /index.php?page=users&success=created');
                exit();
            } catch (PDOException $e) {
                $error_message = ($e->getCode() == 23000) ? "El nombre de usuario o el email ya existen." : "Error al crear el usuario.";
                header('Location: /index.php?page=users_create&error=' . urlencode($error_message));
                exit();
            }
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // ... (código existente)
            $id = $_POST['id'] ?? null;
            $nombre = $_POST['nombre_completo'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $rol = $_POST['rol'] ?? 'vendedor';
            $activo = isset($_POST['activo']) ? 1 : 0;
            if (!$id) { header('Location: /index.php?page=users&error=invalid_id'); exit(); }
            try {
                $pdo = getConnection();
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET nombre_completo = ?, username = ?, email = ?, password = ?, rol = ?, activo = ? WHERE id = ?");
                    $stmt->execute([$nombre, $username, $email, $hashedPassword, $rol, $activo, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET nombre_completo = ?, username = ?, email = ?, rol = ?, activo = ? WHERE id = ?");
                    $stmt->execute([$nombre, $username, $email, $rol, $activo, $id]);
                }
                header('Location: /index.php?page=users&success=updated');
                exit();
            } catch (PDOException $e) {
                $error_message = ($e->getCode() == 23000) ? "El nombre de usuario o el email ya existen." : "Error al actualizar el usuario.";
                header('Location: /index.php?page=users_edit&id=' . $id . '&error=' . urlencode($error_message));
                exit();
            }
        }
        break;

    // CAMBIO: El caso 'delete' ahora es 'deactivate'
    case 'deactivate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id || $id == $_SESSION['user_id']) {
                header('Location: /index.php?page=users&error=invalid_action');
                exit();
            }
            $pdo = getConnection();
            $stmt = $pdo->prepare("UPDATE users SET activo = 0 WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: /index.php?page=users&success=deactivated');
            exit();
        }
        break;

    // NUEVO CASO: Lógica para activar un usuario
    case 'activate':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if (!$id) {
                header('Location: /index.php?page=users&error=invalid_id');
                exit();
            }
            $pdo = getConnection();
            $stmt = $pdo->prepare("UPDATE users SET activo = 1 WHERE id = ?");
            $stmt->execute([$id]);
            header('Location: /index.php?page=users&success=activated');
            exit();
        }
        break;
}