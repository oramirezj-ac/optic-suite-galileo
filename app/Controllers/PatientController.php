<?php
/* ==========================================================================
   Controlador para la Gestión de Pacientes (CRUD)
   ========================================================================== */

require_once __DIR__ . '/../../config/database.php';

function handlePatientAction()
{
    $action = $_GET['action'] ?? 'list';

    switch ($action) {
        case 'store':
            // ... (Lógica de 'store' sin cambios) ...
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
                $apellido_paterno = !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null;
                $apellido_materno = !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null;
                $domicilio = !empty($_POST['domicilio']) ? $_POST['domicilio'] : null;
                $telefono = !empty($_POST['telefono']) ? $_POST['telefono'] : null;
                $edad = !empty($_POST['edad']) ? $_POST['edad'] : null;
                $antecedentes = !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : '';
                if ($nombre === null) { header('Location: /index.php?page=patients_create&error=' . urlencode('El campo Nombre es obligatorio.')); exit(); }
                try {
                    $pdo = getConnection();
                    $stmt = $pdo->prepare("INSERT INTO pacientes (nombre, apellido_paterno, apellido_materno, domicilio, telefono, edad, antecedentes_medicos) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$nombre, $apellido_paterno, $apellido_materno, $domicilio, $telefono, $edad, $antecedentes]);
                    header('Location: /index.php?page=patients&success=created');
                    exit();
                } catch (PDOException $e) { /* ... */ }
            }
            break;

        case 'update':
            // NUEVA LÓGICA PARA ACTUALIZAR
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? null;
                $nombre = !empty($_POST['nombre']) ? $_POST['nombre'] : null;
                $apellido_paterno = !empty($_POST['apellido_paterno']) ? $_POST['apellido_paterno'] : null;
                $apellido_materno = !empty($_POST['apellido_materno']) ? $_POST['apellido_materno'] : null;
                $domicilio = !empty($_POST['domicilio']) ? $_POST['domicilio'] : null;
                $telefono = !empty($_POST['telefono']) ? $_POST['telefono'] : null;
                $edad = !empty($_POST['edad']) ? $_POST['edad'] : null;
                $antecedentes = !empty($_POST['antecedentes_medicos']) ? $_POST['antecedentes_medicos'] : '';

                if ($id === null || $nombre === null) {
                    header('Location: /index.php?page=patients&error=invalid_data');
                    exit();
                }

                try {
                    $pdo = getConnection();
                    $stmt = $pdo->prepare(
                        "UPDATE pacientes SET nombre = ?, apellido_paterno = ?, apellido_materno = ?, domicilio = ?, telefono = ?, edad = ?, antecedentes_medicos = ? 
                         WHERE id = ?"
                    );
                    $stmt->execute([$nombre, $apellido_paterno, $apellido_materno, $domicilio, $telefono, $edad, $antecedentes, $id]);

                    header('Location: /index.php?page=patients&success=updated');
                    exit();
                } catch (PDOException $e) {
                    $error_message = "Error al actualizar al paciente: " . $e->getMessage();
                    header('Location: /index.php?page=patients_edit&id=' . $id . '&error=' . urlencode($error_message));
                    exit();
                }
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id = $_POST['id'] ?? null;
                if (!$id) { header('Location: /index.php?page=patients&error=invalid_id'); exit(); }

                try {
                    $pdo = getConnection();
                    // Esta es la consulta de borrado permanente
                    $stmt = $pdo->prepare("DELETE FROM pacientes WHERE id = ?");
                    $stmt->execute([$id]);
                    header('Location: /index.php?page=patients&success=deleted');
                    exit();
                } catch (PDOException $e) {
                    header('Location: /index.php?page=patients&error=delete_failed');
                    exit();
                }
            }
            break;
        
        default:
            // ... (Lógica de búsqueda/listado sin cambios) ...
            $pdo = getConnection();
            $searchTerm = $_GET['search'] ?? '';
            if (!empty($searchTerm)) {
                $stmt = $pdo->prepare("SELECT * FROM pacientes WHERE CONCAT(nombre, ' ', apellido_paterno) LIKE ? OR telefono LIKE ? ORDER BY apellido_paterno ASC, apellido_materno ASC, nombre ASC");
                $stmt->execute(['%' . $searchTerm . '%', '%' . $searchTerm . '%']);
            } else {
                $stmt = $pdo->prepare("SELECT * FROM pacientes ORDER BY apellido_paterno ASC, apellido_materno ASC, nombre ASC LIMIT 50");
                $stmt->execute();
            }
            return $stmt->fetchAll();
    }
}