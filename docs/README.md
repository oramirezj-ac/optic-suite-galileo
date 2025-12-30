# 📚 Documentación del Proyecto - Optic Suite Galileo

## 🎯 Resumen del Sistema

**Optic Suite Galileo** es un sistema integral para gestión de ópticas que combina:

1. **Captura Masiva de Notas Históricas** - Módulo legacy para digitalizar ventas antiguas
2. **Consultas en Tiempo Real** - Sistema moderno para consultas médicas y refractivas
3. **Gestión de Pacientes** - Expedientes completos con historial
4. **Control de Ventas** - Notas de venta con auditoría de folios

---

## 🏗️ Arquitectura del Sistema

### Módulos Principales

#### 1. **Patients** (Directorio de Pacientes)
- **Ubicación**: `app/Views/patients/`
- **Propósito**: Gestión centralizada de pacientes
- **Características**:
  - Búsqueda por nombre, fecha, letra
  - Auditoría física de expedientes
  - Detección de duplicados
  - Integración con consultas y ventas

#### 2. **Consultas de Lentes** (Sistema Nuevo)
- **Ubicación**: `app/Views/consultas_lentes/`
- **Propósito**: Consultas refractivas modernas
- **Características**:
  - Captura de AV (Agudeza Visual)
  - Captura de CV (Corrección Visual)
  - Graduaciones en tiempo real
  - Distancia pupilar
  - Marcado de graduación final

#### 3. **Consultas Médicas** (Sistema Nuevo)
- **Ubicación**: `app/Views/consultas_medicas/`
- **Propósito**: Consultas por infecciones/problemas oculares
- **Características**:
  - Diagnósticos médicos
  - Tratamientos
  - Seguimiento de casos

#### 4. **Consultas Legacy** (Captura Masiva)
- **Ubicación**: `app/Views/consultas/`
- **Propósito**: Digitalización de consultas históricas
- **Características**:
  - Solo consultas refractivas
  - Captura rápida de graduaciones
  - Integración con ventas antiguas

#### 5. **Graduaciones Live**
- **Ubicación**: `app/Views/graduaciones_live/`
- **Propósito**: Taller de graduaciones en tiempo real
- **Características**:
  - Autorefractómetro
  - Foroptor
  - Prueba ambulatoria
  - Lensómetro
  - Graduación externa
  - Sistema de jerarquía automática

#### 6. **Ventas**
- **Ubicación**: `app/Views/ventas/`
- **Propósito**: Gestión de notas de venta
- **Características**:
  - Auditoría de folios
  - Detección de huecos y duplicados
  - Historial de precios
  - Control de abonos

---

## 📋 Flujos de Trabajo

### Flujo 1: Nueva Consulta de Lentes (Tiempo Real)

```
1. Buscar/Crear Paciente
   ↓
2. Crear Consulta de Lentes
   ↓
3. Capturar AV (Sin Lentes)
   ↓
4. Capturar Graduaciones
   - Autorefractómetro
   - Foroptor
   - Prueba Ambulatoria
   ↓
5. Capturar CV (Con Lentes)
   ↓
6. Capturar DP (Distancia Pupilar)
   ↓
7. Sistema marca automáticamente graduación final
   ↓
8. Crear Venta (Opcional)
```

### Flujo 2: Captura Masiva Histórica

```
1. Buscar/Crear Paciente
   ↓
2. Crear Consulta Legacy
   ↓
3. Capturar Graduación Final Directamente
   ↓
4. Crear Venta con Folio Antiguo
   ↓
5. Registrar Abonos (si aplica)
```

### Flujo 3: Consulta Médica

```
1. Buscar/Crear Paciente
   ↓
2. Crear Consulta Médica
   ↓
3. Capturar Diagnóstico
   ↓
4. Prescribir Tratamiento
   ↓
5. Programar Seguimiento
```

---

## 🎨 Estándares del Sistema

### Formato de Graduación
```
OD -1.00 = -0.50 x 90° 1.25
OI -1.50 = -0.75 x 180° 1.25

Formato: Esfera = Cilindro x Eje° Adición
```

### Jerarquía de Graduaciones
```
1. Prueba Ambulatoria (Mayor prioridad)
2. Foroptor
3. Autorefractómetro
4. Lensómetro (Manual)
5. Externa (Manual)
```

### Nomenclatura de Campos
- **AV**: Agudeza Visual (sin lentes)
- **CV**: Corrección Visual (con lentes)
- **DP**: Distancia Pupilar
- **OD**: Ojo Derecho
- **OI**: Ojo Izquierdo
- **AO**: Ambos Ojos

---

## 🗂️ Estructura de Base de Datos

### Tablas Principales

#### `pacientes`
- Información demográfica
- Antecedentes médicos
- Fecha de primera visita

#### `consultas`
- Fecha de consulta
- Motivo (Refractiva/Médica)
- Datos biométricos (DP, altura)
- Campos de graduación final

#### `graduaciones`
- Tipo (autorrefractometro, foroptor, ambulatoria, etc.)
- Ojo (OD/OI)
- Valores (esfera, cilindro, eje, adición)
- Flag `es_graduacion_final`

#### `ventas`
- Número de nota
- Fecha de venta
- Costo total
- Estado de pago
- Relación con consulta

#### `abonos`
- Monto
- Fecha
- Método de pago
- Relación con venta

---

## 🔧 Archivos de Soporte

### `fix_tipo_column.sql`
Script SQL para corregir el tamaño de la columna `tipo` en la tabla `graduaciones`.
**Nota**: Ya no es necesario, se corrigió el typo en el código.

### `REGLAS_DE_ORO.md`
Reglas de desarrollo establecidas durante el proyecto:
- No usar Bootstrap
- CSS custom en `public/css/styles.css`
- Componentes modulares
- Formato estándar de graduación

### `add_metodo_pago.php`
Script de migración para agregar la columna `metodo_pago` a la tabla `abonos`.

---

## 🚀 Próximos Pasos Sugeridos

### Corto Plazo (Enero 2025)
1. ✅ Terminar captura masiva de notas históricas
2. ⏳ Validar integridad de datos
3. ⏳ Backup completo de base de datos

### Mediano Plazo
1. ⏳ Reportes de ventas por período
2. ⏳ Dashboard con métricas clave
3. ⏳ Exportación a Excel/PDF

### Largo Plazo
1. ⏳ Sistema de inventario de armazones
2. ⏳ Control de citas
3. ⏳ Notificaciones automáticas

---

## 📝 Notas de Desarrollo

### Cambios Importantes Realizados

#### Diciembre 2024
- ✅ Implementado módulo de consultas médicas
- ✅ Refactorizado módulo de graduaciones a componentes
- ✅ Corregido sistema de graduación final
- ✅ Estandarizado formato de visualización
- ✅ Agregado soporte para AV/CV independientes
- ✅ Implementado filtros en `patients/details.php`

#### Correcciones de Bugs
1. **Graduación Final no se mostraba**
   - Causa: JOIN usaba `g.tipo = 'final'` en lugar de `g.es_graduacion_final = 1`
   - Solución: Corregido en `ConsultaModel.php`

2. **Typo en nombre de tipo de graduación**
   - Causa: "autorefractometro" vs "autorrefractometro"
   - Solución: Corregido en controlador y vistas

3. **Filtro de consultas en details.php**
   - Causa: Mostraba consultas médicas en módulo de lentes
   - Solución: Agregado filtro `motivo_consulta === 'Refractiva'`

---

## 🎓 Lecciones Aprendidas

1. **Separación de Módulos**: Mantener consultas médicas y refractivas separadas desde el inicio
2. **Nomenclatura Consistente**: Usar nombres correctos en base de datos evita bugs
3. **Componentes Modulares**: Facilita mantenimiento y escalabilidad
4. **Flags Booleanos**: Usar `es_graduacion_final` es más claro que `tipo = 'final'`
5. **Filtros Tempranos**: Filtrar datos en el controlador/modelo, no solo en la vista

---

## 📞 Contacto y Soporte

Para dudas sobre el sistema, consultar:
- Documentación en `docs/`
- Comentarios en el código
- Commits de Git con mensajes descriptivos

---

**Última actualización**: 30 de Diciembre de 2024
**Versión del Sistema**: 1.0.0
**Estado**: Producción ✅
