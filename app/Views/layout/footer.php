<footer class="footer">
    <span>© 2025 Optic Suite</span>
    <span>Versión 1.0.0</span>
</footer>

<!-- Scripts Globales (siempre se cargan) -->
<script src="/assets/js/main.js"></script>
<script src="/assets/js/consultas.js"></script>
<script src="/assets/js/ventas.js"></script>
<script src="/assets/js/clinica.js"></script>
<script src="/assets/js/consulta_medica.js"></script>
<script src="/assets/js/graduaciones.js"></script>

<!-- Scripts Específicos de Página (opcionales) -->
<?php
// Permite que cada vista defina sus propios scripts
// Uso: $pageScripts = ['/js/mi-script.js', '/js/otro-script.js'];
if (isset($pageScripts) && is_array($pageScripts)): 
    foreach ($pageScripts as $script): ?>
    <script src="<?= htmlspecialchars($script) ?>"></script>
    <?php endforeach;
endif;
?>
