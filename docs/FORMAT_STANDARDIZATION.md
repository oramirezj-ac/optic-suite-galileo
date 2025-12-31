# Estandarización de Formato - Guía de Uso

## Helpers Agregados

### 1. FormatHelper::patientName()
Estandariza la visualización de nombres de pacientes.

**Uso**:
```php
// Antes (inconsistente):
$fullName = implode(' ', array_filter([$paciente['nombre'], $paciente['apellido_paterno'], $paciente['apellido_materno']]));

// Ahora (estandarizado):
$fullName = FormatHelper::patientName($paciente);
```

### 2. FormatHelper::saleNote()
Estandariza la visualización de números de nota.

**Uso**:
```php
// Para tablas (solo número):
FormatHelper::saleNote('787')  // → "0787"
FormatHelper::saleNote('787', 'D')  // → "0787 (D)"

// Para títulos (con label):
FormatHelper::saleNoteTitle('787')  // → "Nota de Venta 0787"
```

## Archivos Actualizados

### Ventas
- ✅ `ventas/create.php`
- ✅ `ventas/edit.php`
- ✅ `ventas/details.php`
- ✅ `ventas/index.php`

### Consultas
- ✅ `consultas/create.php`
- ✅ `consultas/edit.php`
- ✅ `consultas/delete.php`
- ✅ `consultas/index.php`

### Pacientes
- ✅ `patients/details.php`
- ✅ `patients/edit.php`
- ✅ `patients/delete.php`
- ✅ `patients/index.php`

### Graduaciones
- ✅ `graduaciones/index.php`
- ✅ `graduaciones/edit.php`
- ✅ `graduaciones/delete.php`
- ✅ `graduaciones_live/index.php`

### Otros
- ✅ `abonos/create.php`
- ✅ `abonos/edit.php`
- ✅ `abonos/delete.php`
- ✅ `av_live/*`
- ✅ `consultas_lentes/*`

## Beneficios

1. **Consistencia**: Todos los nombres se muestran igual en toda la aplicación
2. **Mantenibilidad**: Un solo lugar para cambiar el formato
3. **Seguridad**: HTML escaping automático
4. **Legibilidad**: Código más limpio y fácil de entender
