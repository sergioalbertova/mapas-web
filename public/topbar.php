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

        <button onclick="toggleTheme()" class="top-icon theme-toggle">🌙</button>

        <a href="logout.php" class="top-icon">⎋</a>

    </div>

</div>
<script>
function toggleTheme() {
    document.body.classList.toggle("dark");
    localStorage.setItem("theme", document.body.classList.contains("dark") ? "dark" : "light");
}

if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark");
}
</script>
