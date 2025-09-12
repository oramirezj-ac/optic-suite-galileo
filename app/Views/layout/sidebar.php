<aside class="sidebar">
    <h1 class="sidebar-brand">Optic Suite</h1>
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="index.php?page=dashboard" class="<?= ($page === 'dashboard') ? 'active' : '' ?>">
                    <span class="icon">🏠</span> Dashboard
                </a>
            </li>
            <li>
                <a href="index.php?page=patients" class="<?= ($page === 'patients') ? 'active' : '' ?>">
                    <span class="icon">👥</span> Pacientes
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="icon">📋</span> Consultas
                </a>
            </li>
            <li>
                <a href="#">
                    <span class="icon">🛒</span> Ventas
                </a>
            </li>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li>
                    <a href="index.php?page=users" class="<?= ($page === 'users') ? 'active' : '' ?>">
                        <span class="icon">⚙️</span> Usuarios
                    </a>
                </li>
            <?php endif; ?>

            <li>
                <a href="logout.php">
                    <span class="icon">🚪</span> Cerrar Sesión
                </a>
            </li>
        </ul>
    </nav>
</aside>