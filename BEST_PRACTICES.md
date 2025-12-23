# Guía de Mejores Prácticas - Optic Suite Galileo

## Introducción

Este documento establece las mejores prácticas de desarrollo para el proyecto Optic Suite Galileo. Todos los desarrolladores deben seguir estas pautas para mantener la calidad, seguridad y consistencia del código.

---

## 1. Seguridad

### 1.1 Protección contra SQL Injection ✅ CRÍTICO

**SIEMPRE usar prepared statements**. Nunca concatenar variables directamente en consultas SQL.

```php
// ❌ INCORRECTO (VULNERABLE)
$sql = "SELECT * FROM pacientes WHERE id = " . $_GET['id'];
$result = $pdo->query($sql);

// ✅ CORRECTO (SEGURO)
$sql = "SELECT * FROM pacientes WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_GET['id']]);
```

---

### 1.2 Protección contra XSS (Cross-Site Scripting) ✅ CRÍTICO

**SIEMPRE escapar datos** antes de mostrarlos en HTML.

```php
// ❌ INCORRECTO
<h2><?= $patient['nombre'] ?></h2>

// ✅ CORRECTO
<?php require_once __DIR__ . '/../../Helpers/SecurityHelper.php'; ?>
<h2><?= SecurityHelper::escape($patient['nombre']) ?></h2>
```

**Regla de oro**: Si proviene del usuario o la base de datos, escápalo antes de mostrarlo.

---

### 1.3 Protección CSRF ✅ CRÍTICO

**Todos los formularios** deben incluir un token CSRF.

```php
<!-- En el formulario -->
<?php require_once __DIR__ . '/../../Helpers/SecurityHelper.php'; ?>
<form method="POST">
    <?= SecurityHelper::csrfField() ?>
    <!-- campos del formulario -->
</form>

<!-- En el controlador -->
if (!SecurityHelper::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    Response::redirectWithError('page', 'Token inválido');
    return;
}
```

---

### 1.4 Validación de Entrada ✅ OBLIGATORIO

**Nunca confiar en datos del usuario**. Validar TODOS los inputs.

```php
$validator = new Validator($_POST);
$validator
    ->required(['nombre'])
    ->maxLength('nombre', 100)
    ->email('email')
    ->phone('telefono');

if (!$validator->isValid()) {
    // Manejar errores
    $errors = $validator->getFirstErrors();
}
```

---

### 1.5 Gestión de Contraseñas ✅ CRÍTICO

**Usar hashing seguro** con Argon2id o bcrypt.

```php
// Hash
$hash = SecurityHelper::hashPassword($password);

// Verificar
if (SecurityHelper::verifyPassword($inputPassword, $storedHash)) {
    // Login exitoso
}
```

**NUNCA** almacenar contraseñas en texto plano o con MD5/SHA1.

---

## 2. Validación de Datos

### 2.1 Usar el Validador Centralizado

```php
require_once __DIR__ . '/../Core/Validator.php';

$validator = new Validator($_POST);
$validator
    ->required(['campo1', 'campo2'])
    ->email('email')
    ->integer('edad')
    ->between('edad', 1, 150);

if ($validator->isValid()) {
    $data = $validator->getSanitizedData();
} else {
    $errors = $validator->getAllErrors();
}
```

---

### 2.2 Validaciones Comunes

| Tipo de Dato | Validación |
|--------------|------------|
| Nombre/Texto | `maxLength()`, `minLength()`, `sanitizeString()` |
| Email | `email()`, `sanitizeEmail()` |
| Teléfono | `phone()`, `sanitizePhone()` |
| Número | `integer()`, `numeric()`, `between()` |
| Fecha | `date('Y-m-d')` |
| Lista cerrada | `in(['valor1', 'valor2'])` |

---

## 3. Manejo de Errores y Logging

### 3.1 Logging de Actividades

```php
require_once __DIR__ . '/../Core/Logger.php';

// Actividades de usuario
Logger::userActivity('Login exitoso', $userId);

// Información general
Logger::info('Venta creada', ['venta_id' => $id]);

// Errores
Logger::error('Error al procesar pago', ['error' => $e->getMessage()]);

// Excepciones
Logger::exception($e, 'Error crítico al crear registro');

// Advertencias de seguridad
Logger::warning('Intento de acceso no autorizado', ['user' => $username]);
```

---

### 3.2 Niveles de Log

| Nivel | Uso |
|-------|-----|
| **debug** | Información detallada para desarrollo |
| **info** | Eventos normales del sistema (crear, actualizar) |
| **warning** | Situaciones anormales no críticas |
| **error** | Errores que permiten continuar |
| **critical** | Errores críticos que afectan el sistema |

---

### 3.3 Manejo de Excepciones

```php
try {
    $result = $model->create($data);
    
    if (!$result) {
        throw new Exception('Error al crear registro');
    }
    
} catch (PDOException $e) {
    // Error de base de datos
    Logger::error('Error de BD', [
        'error' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
    Response::redirectWithError('page', 'Error en la base de datos');
    
} catch (Exception $e) {
    // Error general
    Logger::exception($e);
    Response::redirectWithError('page', 'Error al procesar la solicitud');
}
```

---

## 4. Respuestas HTTP

### 4.1 Usar la Clase Response

```php
require_once __DIR__ . '/../Core/Response.php';

// Redirección simple
Response::redirect('/index.php?page=patients');

// Con mensaje de éxito
Response::redirectWithSuccess('patients', 'Operación exitosa');

// Con mensaje de error
Response::redirectWithError('patients_edit', 'Error al actualizar', ['id' => $id]);

// Respuesta JSON (APIs)
Response::jsonSuccess($data, 'Operación completada');
Response::jsonError('Error de validación', 400, $errors);
```

---

### 4.2 Mensajes Flash

```php
// Mostrar en la vista
<?php require_once __DIR__ . '/../../Core/Response.php'; ?>
<?= Response::renderFlashMessages() ?>
```

---

## 5. Estructura de Código

### 5.1 Organización de Archivos

```
/app
  /Controllers        # Lógica de aplicación
  /Models            # Acceso a datos (PDO)
  /Views             # Presentación (HTML/PHP)
  /Helpers           # Funciones reutilizables
  /Core              # Componentes del sistema
/config              # Configuración
/public              # Punto de entrada
/logs                # Archivos de log
```

---

### 5.2 Estructura de un Controlador

```php
<?php
// 1. Requires
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../Models/ExampleModel.php';
require_once __DIR__ . '/../Core/Validator.php';
require_once __DIR__ . '/../Core/Response.php';
require_once __DIR__ . '/../Core/Logger.php';
require_once __DIR__ . '/../Helpers/SecurityHelper.php';

function handleExampleAction() {
    $action = $_GET['action'] ?? 'list';
    
    switch ($action) {
        case 'create':
            // Lógica de creación
            break;
            
        case 'update':
            // Lógica de actualización
            break;
            
        default:
            // Lógica por defecto (lista)
            break;
    }
}
```

---

### 5.3 Estructura de un Modelo

```php
<?php
class ExampleModel {
    private $pdo;
    
    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Descripción PHPDoc
     * @param array $data
     * @return int|false
     */
    public function create(array $data) {
        try {
            $sql = "INSERT INTO tabla (campo) VALUES (?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['campo']]);
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            Logger::error('Error en create', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
```

---

## 6. Documentación

### 6.1 PHPDoc en Funciones Públicas

```php
/**
 * Crea un nuevo paciente en el sistema.
 *
 * @param array $data Datos del paciente (nombre, apellido_paterno, telefono, etc.)
 * @return int|false ID del paciente creado o false si falla
 * @throws PDOException Si hay error en la base de datos
 */
public function create(array $data)
{
    // Implementación...
}
```

---

### 6.2 Comentarios en el Código

```php
// ✅ BUENO - Explica el "por qué"
// Usamos transacción para asegurar que se guarden venta Y abono o nada
$pdo->beginTransaction();

// ❌ MALO - Explica el "qué" (obvio)
// Incrementa el contador
$counter++;

// ✅ BUENO - Para lógica compleja
// Algoritmo de detección de duplicados:
// 1. Búsqueda exacta por teléfono (alta confianza)
// 2. Búsqueda parcial por nombre + apellido (media confianza)
```

---

## 7. Base de Datos

### 7.1 Prepared Statements

```php
// ✅ Parámetros posicionales
$stmt = $pdo->prepare("SELECT * FROM tabla WHERE id = ? AND status = ?");
$stmt->execute([$id, $status]);

// ✅ Parámetros nombrados (preferido para muchos parámetros)
$stmt = $pdo->prepare("SELECT * FROM tabla WHERE id = :id AND status = :status");
$stmt->execute(['id' => $id, 'status' => $status]);
```

---

### 7.2 Transacciones

```php
try {
    $pdo->beginTransaction();
    
    // Operación 1
    $ventaId = $ventaModel->create($dataVenta);
    
    // Operación 2
    $abonoModel->create(['id_venta' => $ventaId, 'monto' => $anticipo]);
    
    $pdo->commit();
    
} catch (Exception $e) {
    $pdo->rollBack();
    Logger::exception($e, 'Error en transacción');
    throw $e;
}
```

---

## 8. Performance

### 8.1 Consultas Eficientes

```php
// ❌ MALO - N+1 queries
foreach ($patients as $patient) {
    $consultas = $consultaModel->getByPatient($patient['id']);
}

// ✅ BUENO - 1 query con JOIN
$patients = $patientModel->getAllWithConsultas();
```

---

### 8.2 Limitar Resultados

```php
// Siempre usar LIMIT en listados
SELECT * FROM pacientes ORDER BY nombre LIMIT 50
```

---

## 9. Testing (Próximamente)

### 9.1 Estructura de Tests

```php
class PatientModelTest extends TestCase {
    public function testCreatePatientReturnsId() {
        // Arrange
        $data = ['nombre' => 'Juan', 'apellido_paterno' => 'Pérez'];
        
        // Act
        $id = $this->model->create($data);
        
        // Assert
        $this->assertIsNumeric($id);
    }
}
```

---

## 10. Checklist de PR/Commit

Antes de hacer commit, verificar:

- [ ] ✅ Todos los inputs están validados
- [ ] ✅ Todos los outputs están escapados (XSS)
- [ ] ✅ Formularios tienen token CSRF
- [ ] ✅ Errores están logueados apropiadamente
- [ ] ✅ Se usan prepared statements
- [ ] ✅ Código está documentado (PHPDoc)
- [ ] ✅ No hay contraseñas/secrets hardcodeados
- [ ] ✅ Variables de entorno se usan correctamente
- [ ] ✅ Rutas son absolutas (no relativas)
- [ ] ✅ Código sigue el estilo del proyecto

---

## 11. Convenciones de Código

### 11.1 Nomenclatura

```php
// Variables y funciones: camelCase
$patientName = "Juan";
function getPatientById($id) { }

// Clases: PascalCase
class PatientModel { }

// Constantes: UPPER_SNAKE_CASE
const MAX_LOGIN_ATTEMPTS = 3;

// Tablas de BD: snake_case
pacientes, ventas, venta_detalles
```

---

### 11.2 Indentación y Formato

- **4 espacios** para indentación (no tabs)
- **Llaves** en nueva línea para funciones/clases
- **Llaves** en misma línea para if/while/for
- **Líneas max** de 120 caracteres

```php
// ✅ Correcto
function example() 
{
    if ($condition) {
        // código
    }
}
```

---

## 12. Git y Control de Versiones

### 12.1 Mensajes de Commit

```bash
# ✅ BUENO - Describe el cambio claramente
git commit -m "Add CSRF protection to patient creation form"

# ❌ MALO - No informativo
git commit -m "fix"
git commit -m "cambios"
```

---

### 12.2 Estructura de Commits

- **feat:** Nueva funcionalidad
- **fix:** Corrección de bug
- **refactor:** Refactorización sin cambio funcional
- **docs:** Documentación
- **style:** Formato, espacios, etc.
- **test:** Agregar o modificar tests
- **chore:** Tareas de mantenimiento

Ejemplo:
```bash
git commit -m "feat: Add input validation with Validator class"
git commit -m "fix: Prevent XSS in patient details view"
```

---

## 13. Recursos Adicionales

- **CODE_ANALYSIS.md** - Análisis completo del código
- **IMPLEMENTATION_EXAMPLES.md** - Ejemplos prácticos de implementación
- **PHPDoc Standards** - https://docs.phpdoc.org/
- **OWASP Top 10** - https://owasp.org/www-project-top-ten/

---

## 14. Contacto y Soporte

Para dudas sobre estas prácticas:
- Revisar documentación en `/docs`
- Consultar ejemplos en `IMPLEMENTATION_EXAMPLES.md`
- Revisar código en `PatientControllerImproved.php`

---

*Última actualización: 2025-11-20*  
*Versión: 1.0*
