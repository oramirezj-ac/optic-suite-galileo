// Calculadora de Distancia Pupilar
function calcularDNPIndex() {
    const dpTotal = parseFloat(document.getElementById('dp_total_input').value);
    if (!isNaN(dpTotal) && dpTotal > 0) {
        const dnp = (dpTotal / 2).toFixed(1);
        const dpCerca = (dpTotal - 2).toFixed(1);

        document.getElementById('dp_od_input').value = dnp;
        document.getElementById('dp_oi_input').value = dnp;
        document.getElementById('dp_cerca_input').value = dpCerca;

        // También actualizar el formulario de edición si existe
        const dpCercaEdit = document.getElementById('dp_cerca_edit_input');
        if (dpCercaEdit) {
            dpCercaEdit.value = dpCerca;
        }
    }
}

// Toggle formulario DP
function toggleDPForm() {
    const display = document.getElementById('dp-display');
    const form = document.getElementById('dp-form');

    if (form.classList.contains('d-none')) {
        form.classList.remove('d-none');
        display.classList.add('d-none');
    } else {
        form.classList.add('d-none');
        display.classList.remove('d-none');
    }
}
