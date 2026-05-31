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
    const body = document.body;
    const isDark = body.classList.toggle("dark");

    // Guardar preferencia
    localStorage.setItem("theme", isDark ? "dark" : "light");

    // Cambiar icono
    const btn = document.querySelector(".theme-toggle");
    btn.textContent = isDark ? "☀️" : "🌙";
}

// Aplicar tema guardado al cargar
document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem("theme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark");
    }

    // Ajustar icono inicial
    const btn = document.querySelector(".theme-toggle");
    btn.textContent = document.body.classList.contains("dark") ? "☀️" : "🌙";
});
</script>
