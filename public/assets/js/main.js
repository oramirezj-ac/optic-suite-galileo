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

/* ==========================================================================
   Generador de Texto para Ventas (El "Lego")
   ========================================================================== */

function initializeTextGenerator() {
    const textArea = document.getElementById('observaciones');
    const helpers = document.querySelectorAll('.js-text-helper');

    // Cláusula de guardia: Si no estamos en la página correcta, salir.
    if (!textArea || helpers.length === 0) return;

    helpers.forEach(select => {
        select.addEventListener('change', function() {
            const valor = this.value;
            const prefix = this.getAttribute('data-prefix') || '';
            
            if (valor) {
                // Construimos el fragmento de texto
                let textToAppend = (prefix ? prefix + ' ' : '') + valor;

                // Lógica de puntuación: Añadir coma si ya hay texto
                if (textArea.value.trim().length > 0) {
                    if (!textArea.value.endsWith('\n')) {
                        textToAppend = ', ' + textToAppend;
                    }
                }

                // Insertamos el texto
                textArea.value += textToAppend;

                // Reseteamos el selector y devolvemos el foco
                this.selectedIndex = 0;
                textArea.focus();
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initializeTextGenerator);

/* ==========================================================================
   Calculadora Inversa de Edad (Pacientes)
   ========================================================================== */

function initAgeCalculator() {
    // 1. Identificamos los inputs
    const inputVisita = document.getElementById('fecha_primera_visita');
    const inputEdad = document.getElementById('edad_calculadora'); // El campo visual
    const inputNacimiento = document.getElementById('fecha_nacimiento'); // El campo oculto/real

    // Si no estamos en el formulario de pacientes, salimos
    if (!inputEdad || !inputNacimiento) return;

    // 2. Función de cálculo
    function calculateBirthDate() {
        const edad = parseInt(inputEdad.value);
        
        // Si la fecha de visita está vacía, usamos HOY como referencia
        const fechaRefString = inputVisita && inputVisita.value ? inputVisita.value : new Date().toISOString().split('T')[0];
        const fechaRef = new Date(fechaRefString);

        if (!isNaN(edad) && edad > 0) {
            // Restamos la edad al año de referencia
            const anioNacimiento = fechaRef.getFullYear() - edad;
            
            // Mantenemos mes y día de la fecha de referencia
            // (Es una estimación, pero consistente)
            const mes = (fechaRef.getMonth() + 1).toString().padStart(2, '0');
            const dia = fechaRef.getDate().toString().padStart(2, '0');

            // Formato YYYY-MM-DD para el input date
            const fechaStr = `${anioNacimiento}-${mes}-${dia}`;
            
            inputNacimiento.value = fechaStr;
        }
    }

    // 3. Escuchamos cambios en Edad y en Fecha de Visita
    inputEdad.addEventListener('input', calculateBirthDate);
    if (inputVisita) {
        inputVisita.addEventListener('change', calculateBirthDate);
    }
}

// Añadimos a la inicialización
document.addEventListener('DOMContentLoaded', initAgeCalculator);

/* ==========================================================================
   Lógica del Dashboard (Selector de Años)
   ========================================================================== */

function initializeDashboardTabs() {
    const yearTriggers = document.querySelectorAll('.js-year-trigger');
    const emptyState = document.getElementById('dashboard-empty-state');
    const monthContainers = document.querySelectorAll('.months-container');

    if (yearTriggers.length === 0) return;

    yearTriggers.forEach(trigger => {
        trigger.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetContent = document.getElementById(targetId);

            // 1. Resetear estado visual de los botones
            yearTriggers.forEach(btn => btn.classList.remove('active'));
            
            // 2. Activar el botón clicado
            this.classList.add('active');

            // 3. Ocultar mensaje inicial y todos los meses
            if (emptyState) emptyState.style.display = 'none';
            monthContainers.forEach(container => container.classList.remove('show'));

            // 4. Mostrar el mes seleccionado
            if (targetContent) {
                targetContent.classList.add('show');
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', initializeDashboardTabs);

/* ==========================================================================
   Memoria de Fecha (Sticky Date) para Captura Masiva
   ========================================================================== */

function initializeStickyDate() {
    // 1. Lista de IDs de los campos de fecha que queremos sincronizar
    const dateFieldIds = [
        'fecha_primera_visita', // En Crear/Editar Paciente
        'fecha',                // En Crear/Editar Consulta y Abono
        'fecha_venta',          // En Crear Venta
        'fecha_anticipo'        // En Crear Venta (Anticipo)
    ];

    // 2. Recuperar la fecha guardada (si existe)
    const savedDate = sessionStorage.getItem('app_sticky_date');

    dateFieldIds.forEach(id => {
        const input = document.getElementById(id);
        
        if (input) {
            // A. Si tenemos una fecha guardada, la aplicamos al cargar la página
            if (savedDate) {
                input.value = savedDate;
                
                // Disparamos el evento 'change' o 'input' por si hay otros scripts escuchando (ej. la calculadora de edad)
                input.dispatchEvent(new Event('input'));
            }

            // B. Si el usuario cambia la fecha manualmente, actualizamos la memoria
            input.addEventListener('change', function() {
                if (this.value) {
                    sessionStorage.setItem('app_sticky_date', this.value);
                }
            });
        }
    });
}

document.addEventListener('DOMContentLoaded', initializeStickyDate);