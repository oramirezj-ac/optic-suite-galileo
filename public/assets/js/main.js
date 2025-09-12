/* ==========================================================================
   Lógica para Vistas Dinámicas (Pestañas) en la página de detalles
   ========================================================================== */

function initializeDetailViewTabs() {
    // Buscamos los botones y paneles en la página actual
    const viewButtons = document.querySelectorAll('.view-actions .btn');
    const viewPanels = document.querySelectorAll('.view-panel');

    // Si no se encuentran los botones, no hacemos nada.
    // Esto evita errores en otras páginas que no tienen esta estructura.
    if (viewButtons.length === 0 || viewPanels.length === 0) {
        return;
    }

    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const viewId = this.getAttribute('data-view');

            // Ocultar todos los paneles y quitar 'active' de todos los botones
            viewPanels.forEach(panel => panel.classList.remove('active'));
            viewButtons.forEach(btn => btn.classList.remove('active'));

            // Mostrar el panel correcto y marcar el botón como activo
            document.getElementById('view-' + viewId).classList.add('active');
            this.classList.add('active');
        });
    });
}

// Ejecutamos nuestra función cuando el DOM esté completamente cargado
document.addEventListener('DOMContentLoaded', initializeDetailViewTabs);