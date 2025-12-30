/* ==========================================================================
   CONSULTAS MÉDICAS - Cálculo de Montos y Productos Médicos
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
    // Solo ejecutar si estamos en la página de consulta médica
    const formConsultaMedica = document.querySelector('form[action*="consulta_medica_handler"]');
    if (!formConsultaMedica) return;

    // ========================================================================
    // MANEJO DE ESTADO FINANCIERO (Cobrado, Cortesía, Garantía, Pendiente)
    // ========================================================================
    const estadoFinanciero = document.getElementById('estado_financiero');
    const camposCobro = document.getElementById('campos-cobro');
    const costoConsultaInput = document.getElementById('costo_consulta');
    const metodoPagoSelect = document.getElementById('metodo_pago');

    if (estadoFinanciero && camposCobro) {
        function actualizarCamposCobro() {
            const estado = estadoFinanciero.value;

            // Si es cortesía o garantía, ocultar campos de cobro y poner costo en 0
            if (estado === 'cortesia' || estado === 'garantia') {
                camposCobro.style.display = 'none';
                if (costoConsultaInput) {
                    costoConsultaInput.value = '0.00';
                    costoConsultaInput.removeAttribute('required');
                }
                if (metodoPagoSelect) {
                    metodoPagoSelect.value = '';
                    metodoPagoSelect.removeAttribute('required');
                }
            } else {
                // Si es cobrado o pendiente, mostrar campos
                camposCobro.style.display = 'block';
                if (costoConsultaInput) {
                    costoConsultaInput.setAttribute('required', 'required');
                }
                if (metodoPagoSelect && estado === 'cobrado') {
                    metodoPagoSelect.setAttribute('required', 'required');
                } else if (metodoPagoSelect) {
                    metodoPagoSelect.removeAttribute('required');
                }
            }

            // Recalcular total
            calcularTotal();
        }

        // Ejecutar al cambiar el estado financiero
        estadoFinanciero.addEventListener('change', actualizarCamposCobro);

        // Ejecutar al cargar la página
        actualizarCamposCobro();
    }

    // ========================================================================
    // CÁLCULO DE TOTALES
    // ========================================================================
    function calcularTotal() {
        let total = 0;

        // 1. Sumar costo de consulta
        const costoConsulta = document.querySelector('.calc-costo-consulta');
        if (costoConsulta && costoConsulta.value) {
            total += parseFloat(costoConsulta.value) || 0;
        }

        // 2. Sumar medicamentos (cantidad * precio)
        const medicamentosRows = document.querySelectorAll('.medicamento-row');
        medicamentosRows.forEach(row => {
            const cantidad = row.querySelector('.calc-cantidad');
            const precio = row.querySelector('.calc-precio');

            if (cantidad && precio && cantidad.value && precio.value) {
                const subtotal = (parseFloat(cantidad.value) || 0) * (parseFloat(precio.value) || 0);
                total += subtotal;
            }
        });

        // 3. Actualizar campo total
        const totalVenta = document.getElementById('total_venta');
        if (totalVenta) {
            totalVenta.value = total.toFixed(2);
        }
    }

    // Función para autocompletar precio al seleccionar medicamento
    function setupMedicamentoSelect(selectElement) {
        selectElement.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const precio = selectedOption.getAttribute('data-precio');
            const row = this.closest('.medicamento-row');
            const precioInput = row.querySelector('.calc-precio');

            if (precioInput) {
                // Validar que el precio sea un número válido
                if (precio && precio !== '' && !isNaN(parseFloat(precio))) {
                    precioInput.value = parseFloat(precio).toFixed(2);
                } else {
                    precioInput.value = '0.00';
                }
                calcularTotal();
            }
        });
    }

    // Configurar selects existentes
    document.querySelectorAll('.select-medicamento').forEach(setupMedicamentoSelect);

    // Agregar event listeners a campos de cálculo
    const camposCosto = document.querySelectorAll('.calc-costo-consulta, .calc-cantidad, .calc-precio');
    camposCosto.forEach(campo => {
        campo.addEventListener('input', calcularTotal);
        campo.addEventListener('change', calcularTotal);
    });

    // Botón para agregar más medicamentos
    const btnAgregar = document.getElementById('btn-agregar-medicamento');
    if (btnAgregar) {
        btnAgregar.addEventListener('click', function () {
            const container = document.getElementById('medicamentos-container');
            const rows = container.querySelectorAll('.medicamento-row');
            const newIndex = rows.length;

            // Clonar la primera fila
            const firstRow = rows[0];
            const newRow = firstRow.cloneNode(true);

            // Actualizar nombres e IDs
            newRow.querySelectorAll('[name]').forEach(input => {
                const name = input.getAttribute('name');
                input.setAttribute('name', name.replace('[0]', `[${newIndex}]`));
                input.value = input.classList.contains('calc-cantidad') ? '1' : '';
            });

            // Agregar al container
            container.appendChild(newRow);

            // Configurar el nuevo select
            const newSelect = newRow.querySelector('.select-medicamento');
            setupMedicamentoSelect(newSelect);

            // Agregar listeners a los nuevos campos
            newRow.querySelectorAll('.calc-cantidad, .calc-precio').forEach(campo => {
                campo.addEventListener('input', calcularTotal);
                campo.addEventListener('change', calcularTotal);
            });
        });
    }

    // Calcular total inicial
    calcularTotal();

    // ========================================================================
    // MANEJO DE PESTAÑAS (TABS) - Para formularios con navegación por pestañas
    // ========================================================================
    const tabs = document.querySelectorAll('.view-tab');
    const panels = document.querySelectorAll('.view-panel');

    if (tabs.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                const targetView = this.getAttribute('data-view');

                // Remover active de todos
                tabs.forEach(t => t.classList.remove('active'));
                panels.forEach(p => p.classList.remove('active'));

                // Agregar active al seleccionado
                this.classList.add('active');
                const targetPanel = document.getElementById('view-' + targetView);
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        });
    }

    // ========================================================================
    // ELIMINAR MEDICAMENTO - Botón de eliminar en formulario de edición
    // ========================================================================
    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('btn-eliminar-medicamento') ||
            e.target.closest('.btn-eliminar-medicamento')) {
            const row = e.target.closest('.medicamento-row');
            const container = document.getElementById('medicamentos-container');

            if (row && container) {
                const rows = container.querySelectorAll('.medicamento-row');

                // Solo eliminar si hay más de una fila
                if (rows.length > 1) {
                    row.remove();
                    calcularTotal(); // Recalcular total después de eliminar
                } else {
                    // Si es la última fila, limpiar los campos
                    row.querySelectorAll('select, input').forEach(field => {
                        if (field.classList.contains('calc-cantidad')) {
                            field.value = '1';
                        } else {
                            field.value = '';
                        }
                    });
                    calcularTotal(); // Recalcular total después de limpiar
                }
            }
        }
    });
});
