document.addEventListener('DOMContentLoaded', function() {
    
    const selector = document.getElementById('motivo_consulta');
    const bloqueMedico = document.getElementById('bloque-medico');
    const btnSubmit = document.getElementById('btnSubmit'); 

    if (!selector || !bloqueMedico) return;

    function toggleCampos() {
        if (selector.value === 'Médica') {
            // MODO MÉDICO
            bloqueMedico.style.display = 'block';
            
            if (btnSubmit) {
                btnSubmit.innerHTML = 'Guardar Consulta Médica (Finalizar)';
            }

        } else {
            // MODO REFRACTIVO (Lentes)
            bloqueMedico.style.display = 'none';
            
            if (btnSubmit) {
                btnSubmit.innerHTML = 'Guardar y Añadir Graduaciones &rarr;';
            }
        }
    }

    selector.addEventListener('change', toggleCampos);
    toggleCampos();
});