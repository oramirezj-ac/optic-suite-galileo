<header class="header">
    <div class="header-search">
        <input type="search" placeholder="Buscar paciente...">
    </div>
    <div class="header-user-profile">
        <span>
            👤 <?= htmlspecialchars($_SESSION['user_fullname']) ?> (<?= htmlspecialchars($_SESSION['user_role']) ?>)
        </span>
    </div>
</header>