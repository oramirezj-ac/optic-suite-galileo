# 🚀 Quick Reference Card - Optic Suite Galileo

Guía rápida de consulta para desarrolladores. **Imprime y mantén a mano.**

---

## 🛡️ Seguridad (SIEMPRE)

### ✅ Protección XSS
```php
<?php require_once __DIR__ . '/../../Helpers/SecurityHelper.php'; ?>
<h2><?= SecurityHelper::escape($patient['nombre']) ?></h2>
```

### ✅ Protección CSRF
```php
<!-- En formulario -->
<?= SecurityHelper::csrfField() ?>

<!-- En controlador -->
if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    Response::redirectWithError('page', 'Token inválido');
}
```

### ✅ SQL Injection (Prepared Statements)
```php
// ❌ NUNCA HAGAS ESTO
$sql = "SELECT * FROM tabla WHERE id = " . $_GET['id'];

// ✅ SIEMPRE HAZ ESTO
$sql = "SELECT * FROM tabla WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_GET['id']]);
```

---

## ✔️ Validación de Datos

```php
require_once __DIR__ . '/../Core/Validator.php';

$validator = new Validator($_POST);
$validator
    ->required(['nombre', 'email'])
    ->maxLength('nombre', 100)
    ->email('email')
    ->phone('telefono')
    ->integer('edad')
    ->between('edad', 1, 150);

if ($validator->isValid()) {
    $data = $validator->getSanitizedData();
    // Procesar...
} else {
    $errors = $validator->getFirstErrors();
    // Mostrar errores...
}
```

### Reglas Comunes:
| Regla | Uso |
|-------|-----|
| `required(['campo'])` | Obligatorio |
| `email('campo')` | Email válido |
| `phone('campo')` | Teléfono 10 dígitos |
| `integer('campo')` | Número entero |
| `numeric('campo')` | Número (int/float) |
| `between('campo', min, max)` | Rango numérico |
| `minLength('campo', n)` | Longitud mínima |
| `maxLength('campo', n)` | Longitud máxima |
| `date('campo', 'Y-m-d')` | Fecha válida |
| `in('campo', ['a', 'b'])` | Lista cerrada |

---

## 📝 Logging

```php
require_once __DIR__ . '/../Core/Logger.php';

// Niveles
Logger::debug('Debug info', $context);      // Desarrollo
Logger::info('User login', $context);       // Información
Logger::warning('Anomalía', $context);      // Advertencia
Logger::error('Error', $context);           // Error
Logger::critical('Crítico', $context);      // Crítico

// Helpers
Logger::exception($e, 'Mensaje');
Logger::userActivity('Acción', $userId, $details);
```

---

## 🔄 Respuestas HTTP

```php
require_once __DIR__ . '/../Core/Response.php';

// Redirecciones
Response::redirectWithSuccess('page', 'Mensaje de éxito');
Response::redirectWithError('page', 'Mensaje de error', ['id' => $id]);
Response::redirect('/url');

// En vistas (mostrar mensajes)
<?= Response::renderFlashMessages() ?>

// APIs JSON
Response::jsonSuccess($data, 'Mensaje');
Response::jsonError('Error', 400, $errors);
```

---

## 🧹 Sanitización

```php
require_once __DIR__ . '/../Helpers/SecurityHelper.php';

// Email
$email = SecurityHelper::sanitizeEmail($_POST['email']);

// Teléfono
$tel = SecurityHelper::sanitizePhone($_POST['telefono']);

// String general
$text = SecurityHelper::sanitizeString($_POST['texto']);

// Genérico
$value = SecurityHelper::sanitize($_POST['campo'], 'tipo');
// Tipos: 'string', 'email', 'phone', 'int', 'float'
```

---

## 📋 Estructura de Controlador

```php
<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../Models/Model.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Core/Logger.php';
require_once __DIR__ . '/../Helpers/SecurityHelper.php';

function handleAction() {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'create':
            // CSRF
            if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
                Logger::warning('CSRF attempt');
                Response::redirectWithError('page', 'Token inválido');
                return;
            }
            
            // Validar
            $validator = new Validator($_POST);
            $validator->required(['campo'])->maxLength('campo', 100);
            
            if (!$validator->isValid()) {
                Response::redirectWithError('page', 
                    implode(', ', $validator->getFirstErrors()));
                return;
            }
            
            // Procesar
            try {
                $data = $validator->getSanitizedData();
                $result = $model->create($data);
                Logger::userActivity('Creado', $_SESSION['user_id']);
                Response::redirectWithSuccess('page', 'Éxito');
            } catch (Exception $e) {
                Logger::exception($e);
                Response::redirectWithError('page', 'Error');
            }
            break;
    }
}
```

---

## 🔐 Contraseñas

```php
// Hash al crear/actualizar
$hash = SecurityHelper::hashPassword($password);

// Verificar en login
if (SecurityHelper::verifyPassword($inputPassword, $storedHash)) {
    // Login exitoso
}
```

---

## 📊 PHPDoc

```php
/**
 * Descripción breve de la función.
 *
 * Descripción extendida opcional.
 *
 * @param array $data Descripción del parámetro
 * @return int|false ID generado o false si falla
 * @throws PDOException Si hay error de BD
 */
public function create(array $data)
{
    // ...
}
```

---

## ⚡ Tips Rápidos

### ✅ SIEMPRE:
- Validar todos los inputs
- Escapar todos los outputs
- Usar prepared statements
- Loguear errores críticos
- Verificar tokens CSRF en POST
- Documentar funciones públicas

### ❌ NUNCA:
- Concatenar SQL con variables de usuario
- Mostrar datos sin escapar
- Ignorar errores silenciosamente
- Hardcodear contraseñas/secrets
- Confiar en datos del cliente

---

## 📁 Estructura de Archivos

```
/app
  /Controllers  → Lógica de aplicación
  /Models       → Acceso a datos (PDO)
  /Views        → HTML/PHP
  /Helpers      → Funciones reutilizables
  /Core         → Validator, Logger, Response
/config         → Database, Session
/public         → index.php, assets
/logs           → app.log (rotar diario)
```

---

## 🔧 Convenciones

```php
// Variables/funciones: camelCase
$patientName = "Juan";
function getPatientById($id) { }

// Clases: PascalCase
class PatientModel { }

// Constantes: UPPER_SNAKE_CASE
const MAX_ATTEMPTS = 3;

// Indentación: 4 espacios (no tabs)
```

---

## 🆘 ¿Necesitas Ayuda?

1. **CODE_ANALYSIS.md** → Análisis técnico completo
2. **BEST_PRACTICES.md** → Guía detallada (14 secciones)
3. **IMPLEMENTATION_EXAMPLES.md** → 12 ejemplos prácticos
4. **PatientControllerImproved.php** → Controlador de referencia

---

## ✅ Checklist Pre-Commit

- [ ] Inputs validados con Validator
- [ ] Outputs escapados con SecurityHelper
- [ ] CSRF en formularios POST
- [ ] Errores logueados apropiadamente
- [ ] Prepared statements en queries
- [ ] PHPDoc en funciones públicas
- [ ] Sin secrets hardcodeados
- [ ] Código sigue convenciones

---

**¿Pregunta rápida?** Busca en la documentación o revisa ejemplos.

*v1.0 - 2025-11-20*
