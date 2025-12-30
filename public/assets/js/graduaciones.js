/* ==========================================================================
   GRADUACIONES - Funcionalidad de Copiar y Manejo de Formularios
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // ========================================================================
    // COPIAR GRADUACIÓN - Para graduaciones/ (formulario en misma página)
    // ========================================================================
    const btnsCopiarFormulario = document.querySelectorAll('.btn-copiar-graduacion');

    btnsCopiarFormulario.forEach(btn => {
        btn.addEventListener('click', function () {
            const fila = this.closest('.graduacion-fila');
            const formula = fila.querySelector('.graduacion-formula');

            // Obtener valores de data attributes
            const odEsfera = formula.getAttribute('data-od-esfera');
            const odCilindro = formula.getAttribute('data-od-cilindro');
            const odEje = formula.getAttribute('data-od-eje');
            const odAdicion = formula.getAttribute('data-od-adicion');

            const oiEsfera = formula.getAttribute('data-oi-esfera');
            const oiCilindro = formula.getAttribute('data-oi-cilindro');
            const oiEje = formula.getAttribute('data-oi-eje');
            const oiAdicion = formula.getAttribute('data-oi-adicion');

            // Rellenar formulario de nueva graduación
            const odEsferaInput = document.querySelector('input[name="od_esfera"]');
            const odCilindroInput = document.querySelector('input[name="od_cilindro"]');
            const odEjeInput = document.querySelector('input[name="od_eje"]');
            const odAdicionInput = document.querySelector('input[name="od_adicion"]');

            if (odEsferaInput) odEsferaInput.value = odEsfera;
            if (odCilindroInput) odCilindroInput.value = odCilindro;
            if (odEjeInput) odEjeInput.value = odEje;
            if (odAdicionInput) odAdicionInput.value = odAdicion;

            const oiEsferaInput = document.querySelector('input[name="oi_esfera"]');
            const oiCilindroInput = document.querySelector('input[name="oi_cilindro"]');
            const oiEjeInput = document.querySelector('input[name="oi_eje"]');
            const oiAdicionInput = document.querySelector('input[name="oi_adicion"]');

            if (oiEsferaInput) oiEsferaInput.value = oiEsfera;
            if (oiCilindroInput) oiCilindroInput.value = oiCilindro;
            if (oiEjeInput) oiEjeInput.value = oiEje;
            if (oiAdicionInput) oiAdicionInput.value = oiAdicion;

            // Scroll al formulario
            const formulario = document.querySelector('form[action*="graduacion_handler"]');
            if (formulario) {
                formulario.scrollIntoView({ behavior: 'smooth', block: 'start' });
                formulario.style.backgroundColor = '#fffbcc';
                setTimeout(() => { formulario.style.backgroundColor = ''; }, 1000);
            }

            // Feedback visual
            this.innerHTML = '✓ Copiado';
            this.classList.remove('btn-info');
            this.classList.add('btn-success');
            setTimeout(() => {
                this.innerHTML = '📋 Copiar';
                this.classList.remove('btn-success');
                this.classList.add('btn-info');
            }, 2000);
        });
    });

    // ========================================================================
    // COPIAR GRADUACIÓN - Para graduaciones_live/ (redirige con parámetros URL)
    // ========================================================================
    const btnsCopiarGrad = document.querySelectorAll('.btn-copiar-grad');

    btnsCopiarGrad.forEach(btn => {
        btn.addEventListener('click', function () {
            // Obtener valores de data attributes
            const odEsfera = this.getAttribute('data-od-esfera');
            const odCilindro = this.getAttribute('data-od-cilindro');
            const odEje = this.getAttribute('data-od-eje');
            const odAdicion = this.getAttribute('data-od-adicion');

            const oiEsfera = this.getAttribute('data-oi-esfera');
            const oiCilindro = this.getAttribute('data-oi-cilindro');
            const oiEje = this.getAttribute('data-oi-eje');
            const oiAdicion = this.getAttribute('data-oi-adicion');

            const targetUrl = this.getAttribute('data-target-url');

            if (!targetUrl) {
                console.error('No target URL specified');
                return;
            }

            // Construir URL con parámetros
            const url = new URL(targetUrl, window.location.origin);
            url.searchParams.set('copy_od_esfera', odEsfera);
            url.searchParams.set('copy_od_cilindro', odCilindro);
            url.searchParams.set('copy_od_eje', odEje);
            url.searchParams.set('copy_od_adicion', odAdicion);
            url.searchParams.set('copy_oi_esfera', oiEsfera);
            url.searchParams.set('copy_oi_cilindro', oiCilindro);
            url.searchParams.set('copy_oi_eje', oiEje);
            url.searchParams.set('copy_oi_adicion', oiAdicion);

            // Feedback visual antes de redirigir
            this.innerHTML = '✓ Copiando...';
            this.classList.remove('btn-info');
            this.classList.add('btn-success');

            // Redirigir después de un breve delay
            setTimeout(() => {
                window.location.href = url.toString();
            }, 500);
        });
    });

});
