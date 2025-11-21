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
document.addEventListener('DOMContentLoaded', function() {
    initializeDetailViewTabs();
    // La función initializeNoteCaptureForm() se ha eliminado correctamente.
});

/* ==========================================================================
   Activador de Pestañas (Tabs) desde la URL
   ========================================================================== */

/**
 * Revisa si la URL contiene un parámetro 'tab' y activa la pestaña
 * correspondiente en cualquier página que use el sistema de [data-view].
 */
function activateTabFromURL() {
    // 1. Lee los parámetros de la URL
    const urlParams = new URLSearchParams(window.location.search);

    // 2. Busca el parámetro 'tab'
    const tabToActivate = urlParams.get('tab');

    if (tabToActivate) {
        // 3. Busca el botón que tiene ese 'data-view'
        const tabButton = document.querySelector(`.card-header .btn[data-view="${tabToActivate}"]`);

        // 4. Si encontramos el botón, le damos clic
        if (tabButton) {
            // Asumimos que ya tienes un JS que maneja el clic
            // de las pestañas. Esto lo simula.
            tabButton.click();
        }
    }
}

// Ejecuta nuestra nueva función solo cuando la página haya cargado
document.addEventListener('DOMContentLoaded', activateTabFromURL);

/* ==========================================================================
   Automatización de Datos Biométricos (DP)
   ========================================================================== */

function initializeBiometricsCalculator() {
    // 1. Identificamos los elementos del DOM
    const dpTotalInput = document.getElementById('dp_lejos_total');
    const dpOdInput = document.getElementById('dp_od');
    const dpOiInput = document.getElementById('dp_oi');

    // 2. Cláusula de Guardia: Si no estamos en la página correcta, salimos.
    if (!dpTotalInput || !dpOdInput || !dpOiInput) {
        return;
    }

    // 3. Agregamos el escuchador de eventos
    dpTotalInput.addEventListener('input', function() {
        const total = parseFloat(this.value);
        
        // Validamos que sea un número real y positivo
        if (!isNaN(total) && total > 0) {
            const half = total / 2;
            
            // Llenamos los campos monoculares automáticamente
            // (El usuario aún puede editarlos manualmente si lo necesita)
            dpOdInput.value = half;
            dpOiInput.value = half;
        } else {
            // Si borran el total, limpiamos los hijos (opcional, pero limpio)
            if (this.value === '') {
                dpOdInput.value = '';
                dpOiInput.value = '';
            }
        }
    });
}

// Agregamos la función a la cola de ejecución al cargar la página
document.addEventListener('DOMContentLoaded', initializeBiometricsCalculator);