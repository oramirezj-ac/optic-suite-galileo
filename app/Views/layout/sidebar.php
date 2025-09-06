<aside class="sidebar">
    <h1 class="sidebar-brand">Optic Suite</h1>
    <class="sidebar-nav">
        <ul>
            <li><a href="index.php?page=dashboard" class="<?= ($page === 'dashboard') ? 'active' : '' ?>">🏠 Dashboard</a></li>
            <li><a href="#">👥 Pacientes</a></li>
            <li><a href="#">📋 Consultas</a></li>
            <li><a href="#">🛒 Ventas</a></li>
            
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <li><a href="index.php?page=users" class="<?= ($page === 'users') ? 'active' : '' ?>">⚙️ Usuarios</a></li>
            <?php endif; ?>

            <li><a href="logout.php">🚪 Cerrar Sesión</a></li>
        </ul>
    </nav>
</aside>