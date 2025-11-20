# Ejemplos de Implementación de Mejores Prácticas

Este documento contiene ejemplos prácticos de cómo usar los nuevos componentes implementados en el proyecto.

## 1. Uso del Validador

### Ejemplo 1: Validación en PatientController (Crear Paciente)

```php
<?php
// app/Controllers/PatientController.php

require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Core/Logger.php';
require_once __DIR__ . '/../Helpers/SecurityHelper.php';

function handlePatientAction() {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'store':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                
                // 1. Verificar token CSRF
                if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                    Logger::warning('Intento de CSRF detectado', [
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                    Response::redirectWithError('patients_create', 'Token de seguridad inválido');
                    return;
                }
                
                // 2. Validar datos con Validator
                $validator = new Validator($_POST);
                $validator
                    ->required(['nombre'], 'El nombre es obligatorio')
                    ->maxLength('nombre', 100)
                    ->maxLength('apellido_paterno', 100)
                    ->maxLength('apellido_materno', 100)
                    ->phone('telefono', 'El teléfono debe tener 10 dígitos')
                    ->integer('edad')
                    ->between('edad', 1, 150, 'La edad debe estar entre 1 y 150 años');
                
                // 3. Si hay errores, redirigir con mensajes
                if (!$validator->isValid()) {
                    $errors = $validator->getFirstErrors();
                    $errorMessage = implode(', ', $errors);
                    
                    Logger::info('Validación fallida al crear paciente', [
                        'errors' => $errors
                    ]);
                    
                    Response::redirectWithError('patients_create', $errorMessage);
                    return;
                }
                
                // 4. Obtener datos validados y limpios
                $data = [
                    'nombre' => $validator->get('nombre'),
                    'apellido_paterno' => $validator->get('apellido_paterno'),
                    'apellido_materno' => $validator->get('apellido_materno'),
                    'domicilio' => $validator->get('domicilio'),
                    'telefono' => $validator->get('telefono'),
                    'edad' => $validator->get('edad'),
                    'antecedentes' => $validator->get('antecedentes_medicos', '')
                ];
                
                // 5. Verificar duplicados
                $pdo = getConnection();
                $patientModel = new PatientModel($pdo);
                $duplicates = $patientModel->findSimilar($data);
                
                if (!empty($duplicates)) {
                    $_SESSION['new_patient_data'] = $data;
                    Response::redirect('/index.php?page=patients_review');
                    return;
                }
                
                // 6. Crear paciente
                try {
                    $newPatientId = $patientModel->create($data);
                    
                    if ($newPatientId) {
                        Logger::userActivity('Paciente creado', $_SESSION['user_id'] ?? null, [
                            'patient_id' => $newPatientId,
                            'patient_name' => $data['nombre'] . ' ' . $data['apellido_paterno']
                        ]);
                        
                        Response::redirectWithSuccess(
                            'patients_details', 
                            'Paciente creado exitosamente',
                            ['id' => $newPatientId]
                        );
                    }
                } catch (Exception $e) {
                    Logger::exception($e, 'Error al crear paciente');
                    Response::redirectWithError('patients_create', 'Error al crear el paciente');
                }
            }
            break;
    }
}
```

---

## 2. Protección XSS en Vistas

### Antes (VULNERABLE):
```php
<!-- app/Views/patients/details.php -->
<h2>Expediente de <?= $patient['nombre'] ?></h2>
<p>Teléfono: <?= $patient['telefono'] ?></p>
```

### Después (SEGURO):
```php
<?php require_once __DIR__ . '/../../Helpers/SecurityHelper.php'; ?>

<h2>Expediente de <?= SecurityHelper::escape($patient['nombre']) ?></h2>
<p>Teléfono: <?= SecurityHelper::escape($patient['telefono']) ?></p>
```

---

## 3. Tokens CSRF en Formularios

### Formulario de Creación de Paciente

```php
<?php require_once __DIR__ . '/../../Helpers/SecurityHelper.php'; ?>

<form method="POST" action="/patient_handler.php?action=store">
    
    <!-- Token CSRF -->
    <?= SecurityHelper::csrfField() ?>
    
    <div class="form-group">
        <label>Nombre *</label>
        <input type="text" name="nombre" required maxlength="100">
    </div>
    
    <div class="form-group">
        <label>Apellido Paterno</label>
        <input type="text" name="apellido_paterno" maxlength="100">
    </div>
    
    <div class="form-group">
        <label>Teléfono</label>
        <input type="tel" name="telefono" pattern="[0-9]{10}" 
               placeholder="10 dígitos">
    </div>
    
    <button type="submit">Guardar Paciente</button>
</form>
```

---

## 4. Mensajes Flash

### En el Controlador:
```php
// Éxito
Response::redirectWithSuccess('patients', 'Paciente actualizado correctamente');

// Error
Response::redirectWithError('patients_edit', 'No se pudo actualizar el paciente', ['id' => $id]);

// Información
Response::redirectWithInfo('patients', 'El paciente ya existe en el sistema');
```

### En la Vista (header o layout):
```php
<?php require_once __DIR__ . '/../../Core/Response.php'; ?>

<!-- Mostrar mensajes flash automáticamente -->
<?= Response::renderFlashMessages() ?>
```

---

## 5. Logging de Actividades

### En Controladores:

```php
require_once __DIR__ . '/../Core/Logger.php';

// Login de usuario
Logger::userActivity('Login exitoso', $userId);

// Creación de registro
Logger::info('Venta creada', [
    'venta_id' => $ventaId,
    'patient_id' => $patientId,
    'monto' => $costoTotal
]);

// Error al procesar
Logger::error('Error al procesar pago', [
    'venta_id' => $ventaId,
    'error' => $e->getMessage()
]);

// Actividad sospechosa
Logger::warning('Múltiples intentos de login fallidos', [
    'username' => $username,
    'ip' => $_SERVER['REMOTE_ADDR']
]);
```

### En Modelos (errores de BD):

```php
public function create($data) {
    try {
        // ... código de inserción
        return $this->pdo->lastInsertId();
    } catch (PDOException $e) {
        Logger::error('Error al crear paciente en BD', [
            'error' => $e->getMessage(),
            'code' => $e->getCode(),
            'data' => $data
        ]);
        return false;
    }
}
```

---

## 6. Validación con Reglas Personalizadas

```php
$validator = new Validator($_POST);

// Validación personalizada: fecha de venta no puede ser futura
$validator->custom('fecha_venta', function($fecha) {
    return strtotime($fecha) <= time();
}, 'La fecha de venta no puede ser futura');

// Validación personalizada: número de nota único
$validator->custom('numero_nota', function($nota) use ($ventaModel) {
    return !$ventaModel->existsNumeroNota($nota);
}, 'El número de nota ya existe');
```

---

## 7. Respuestas JSON (Para APIs Futuras)

```php
// Endpoint de API
if ($_GET['api'] === 'patients') {
    require_once __DIR__ . '/../Core/Response.php';
    
    try {
        $patients = $patientModel->getAll();
        
        Response::jsonSuccess($patients, 'Pacientes obtenidos correctamente');
        
    } catch (Exception $e) {
        Logger::exception($e);
        Response::jsonError('Error al obtener pacientes', 500);
    }
}
```

---

## 8. Mejora en el Modelo con Logging

### PatientModel Mejorado:

```php
class PatientModel {
    private $pdo;
    
    public function create($data) {
        try {
            $sql = "INSERT INTO pacientes (nombre, apellido_paterno, ...) VALUES (?, ?, ...)";
            $stmt = $this->pdo->prepare($sql);
            
            $startTime = microtime(true);
            $success = $stmt->execute([...]);
            $executionTime = microtime(true) - $startTime;
            
            if ($success) {
                $id = $this->pdo->lastInsertId();
                
                // Log de query lenta
                Logger::sqlQuery($sql, [...], $executionTime);
                
                return $id;
            }
            
            return false;
            
        } catch (PDOException $e) {
            Logger::error('Error al crear paciente', [
                'error' => $e->getMessage(),
                'sql_state' => $e->getCode(),
                'data' => $data
            ]);
            return false;
        }
    }
}
```

---

## 9. Headers de Seguridad en index.php

```php
<?php
// public/index.php - Al inicio del archivo

require_once '../app/Helpers/SecurityHelper.php';

// Establecer headers de seguridad
SecurityHelper::setSecurityHeaders();

// Resto del código...
?>
```

---

## 10. Sanitización de Datos de Entrada

```php
// Sanitizar email
$email = SecurityHelper::sanitizeEmail($_POST['email']);
if ($email === false) {
    Response::redirectWithError('contact', 'Email inválido');
}

// Sanitizar teléfono
$telefono = SecurityHelper::sanitizePhone($_POST['telefono']);

// Sanitizar string general
$observaciones = SecurityHelper::sanitizeString($_POST['observaciones']);

// Sanitización genérica
$edad = SecurityHelper::sanitize($_POST['edad'], 'int');
$precio = SecurityHelper::sanitize($_POST['precio'], 'float');
```

---

## 11. Validación Compleja con Múltiples Reglas

```php
// Validación de formulario de venta
$validator = new Validator($_POST);

$validator
    ->required(['id_paciente', 'numero_nota', 'fecha_venta', 'costo_total'])
    ->integer('id_paciente')
    ->regex('numero_nota', '/^\d{4}(-[A-Z])?$/', 'Formato de nota inválido (ej: 0123 o 0123-A)')
    ->date('fecha_venta', 'Y-m-d', 'Fecha inválida')
    ->numeric('costo_total')
    ->between('costo_total', 0.01, 999999.99)
    ->maxLength('observaciones', 500);

if ($validator->isValid()) {
    $cleanData = $validator->getSanitizedData();
    // Procesar venta...
} else {
    $errors = $validator->getAllErrors();
    Response::redirectWithError('ventas_create', implode(', ', $errors));
}
```

---

## 12. Ejemplo de Hash de Contraseñas (Módulo de Usuarios)

```php
// Crear usuario
$hashedPassword = SecurityHelper::hashPassword($_POST['password']);
$userData = [
    'username' => $_POST['username'],
    'password' => $hashedPassword,
    'role' => $_POST['role']
];

// Login
$storedHash = $user['password'];
$inputPassword = $_POST['password'];

if (SecurityHelper::verifyPassword($inputPassword, $storedHash)) {
    // Login exitoso
    $_SESSION['user_id'] = $user['id'];
    Logger::userActivity('Login', $user['id']);
} else {
    Logger::warning('Intento de login fallido', ['username' => $_POST['username']]);
}
```

---

## Checklist de Implementación

Al modificar o crear nuevos módulos, asegurarse de:

- [ ] Validar todos los inputs con `Validator`
- [ ] Verificar tokens CSRF en formularios POST
- [ ] Escapar todas las salidas con `SecurityHelper::escape()`
- [ ] Usar `Response` para redirecciones y mensajes
- [ ] Loguear errores críticos con `Logger`
- [ ] Loguear actividades de usuario importantes
- [ ] Sanitizar datos antes de procesarlos
- [ ] Usar prepared statements en todas las queries SQL
- [ ] Manejar excepciones apropiadamente
- [ ] Documentar funciones con PHPDoc

---

*Última actualización: 2025-11-20*
