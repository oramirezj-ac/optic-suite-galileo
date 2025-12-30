/* ==========================================================================
   MÓDULO DE CLÍNICA - JavaScript
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
    console.log('🏥 Módulo de clínica inicializado');

    // Validación del formulario de paciente antes de enviar
    const formPaciente = document.getElementById('form-paciente');
    if (formPaciente) {
        formPaciente.addEventListener('submit', function (e) {
            const fechaNacimiento = document.getElementById('fecha_nacimiento');

            // Si la fecha de nacimiento está vacía, calcularla ahora
            if (fechaNacimiento && !fechaNacimiento.value) {
                const edad = document.getElementById('edad');
                const fechaVisita = document.getElementById('fecha_primera_visita');

                if (edad && fechaVisita && edad.value && fechaVisita.value) {
                    // Calcular fecha de nacimiento
                    const refDate = new Date(fechaVisita.value);
                    const birthYear = refDate.getFullYear() - parseInt(edad.value);
                    const month = String(refDate.getMonth() + 1).padStart(2, '0');
                    const day = String(refDate.getDate()).padStart(2, '0');

                    fechaNacimiento.value = `${birthYear}-${month}-${day}`;
                    console.log('📅 Fecha de nacimiento calculada:', fechaNacimiento.value);
                }
            }

            // Quitar readonly temporalmente para que se envíe el valor
            if (fechaNacimiento) {
                fechaNacimiento.removeAttribute('readonly');
            }
        });
    }
});
