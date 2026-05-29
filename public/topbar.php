<div class="topbar">

    <div class="topbar-left">
        <button class="menu-btn" onclick="toggleSidebar()">☰</button>

        <div class="topbar-title">
            Panel TI
        </div>
    </div>

    <div class="topbar-right">
        
        <div class="user-info">
            <?= htmlspecialchars($nombreUsuario) ?>
        </div>

        <button onclick="toggleTheme()" class="top-icon">🌙</button>

        <a href="logout.php" class="top-icon">⎋</a>

    </div>

</div>