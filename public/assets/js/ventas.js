document.addEventListener('DOMContentLoaded', function() {
    
    // Constructor de Descripción (Selects dinámicos)
    const selects = document.querySelectorAll('.js-text-helper');
    const textarea = document.getElementById('observaciones');

    if (selects.length > 0 && textarea) {
        selects.forEach(select => {
            select.addEventListener('change', function() {
                const prefix = this.getAttribute('data-prefix') || '';
                const value = this.value;

                if (value) {
                    // Construir el texto: Prefijo + Valor
                    let textToAdd = value;
                    if (prefix) {
                        textToAdd = `${prefix} ${value}`;
                    }

                    // Agregar al textarea con coma si ya hay texto
                    const currentText = textarea.value.trim();
                    const separator = currentText.length > 0 ? ', ' : '';
                    
                    textarea.value = currentText + separator + textToAdd;

                    // Resetear el select a la opción por defecto
                    this.selectedIndex = 0;
                }
            });
        });
    }
});