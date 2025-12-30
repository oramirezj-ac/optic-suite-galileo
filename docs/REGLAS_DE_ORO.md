# Reglas de Oro del Proyecto Optic Suite Galileo

## 🚫 Prohibiciones Absolutas

### 1. **NO usar estilos inline (CSS en HTML)**
- ❌ Prohibido: `<div style="color: red;">`
- ✅ Correcto: Usar clases CSS en archivos `.css` separados
- **Razón**: Mantener separación de responsabilidades y facilitar mantenimiento

### 2. **NO usar JavaScript inline (JS en HTML)**
- ❌ Prohibido: `<button onclick="myFunction()">`
- ✅ Correcto: Usar `addEventListener` en archivos `.js` separados
- **Razón**: Mantener código limpio y reutilizable

### 3. **NO usar alertas JavaScript**
- ❌ Prohibido: `alert()`, `confirm()`, `prompt()`
- ✅ Correcto: Usar mensajes en la interfaz, modales personalizados o redirecciones
- **Razón**: Mejor experiencia de usuario y control sobre la UI

## ✅ Mejores Prácticas

### Organización de Archivos
- CSS: `/public/assets/css/components/`
- JavaScript: `/public/assets/js/`
- Vistas: `/app/Views/`
- Controladores: `/app/Controllers/`
- Modelos: `/app/Models/`

### Nomenclatura
- Archivos CSS: `nombre-componente.css` (kebab-case)
- Archivos JS: `nombre_modulo.js` (snake_case)
- Clases CSS: `.nombre-clase` (kebab-case)
- Variables PHP: `$nombreVariable` (camelCase)

### Flujo de Datos
1. Usuario → Vista (HTML)
2. Vista → Controlador (PHP)
3. Controlador → Modelo (PHP)
4. Modelo → Base de Datos
5. Respuesta inversa con redirecciones

### Manejo de Errores
- Usar parámetros GET para mensajes: `?error=mensaje` o `?success=mensaje`
- Mostrar mensajes en la interfaz con clases `.alert`
- Nunca usar `alert()` o `confirm()`

## 📝 Notas Importantes

- Todos los scripts JS se cargan en `footer.php`
- Todos los estilos CSS se importan en `styles.css`
- Las rutas se manejan en `public/index.php`
- La sesión se maneja en `config/session.php`
