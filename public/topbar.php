<div class="topbar">

    <div class="topbar-left">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>

        <div class="topbar-title">
            Panel TI
        </div>
    </div>

    <div class="topbar-right">
        
        <div class="user-info">
            <?= htmlspecialchars($nombreUsuario ?? 'Usuario') ?>
        </div>

        <!-- Botón de tema -->
        <button class="top-icon theme-toggle" onclick="toggleTheme()">🌙</button>

        <!-- Logout -->
        <a href="logout.php" class="top-icon">⎋</a>

    </div>

</div>
