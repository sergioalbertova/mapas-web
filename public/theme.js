/* ============================================
   SISTEMA DE TEMA TIHIL — CLARO / OSCURO
   ============================================ */

/* Cambiar tema */
function toggleTheme() {
    const body = document.body;

    // Alternar clase
    const isDark = body.classList.toggle("dark");

    // Guardar preferencia
    localStorage.setItem("theme", isDark ? "dark" : "light");

    // Actualizar icono del botón
    const btn = document.querySelector(".theme-toggle");
    if (btn) {
        btn.textContent = isDark ? "☀️" : "🌙";
    }
}

/* Aplicar tema guardado al cargar */
document.addEventListener("DOMContentLoaded", () => {
    const savedTheme = localStorage.getItem("theme");

    if (savedTheme === "dark") {
        document.body.classList.add("dark");
    }

    // Ajustar icono inicial
    const btn = document.querySelector(".theme-toggle");
    if (btn) {
        btn.textContent = document.body.classList.contains("dark") ? "☀️" : "🌙";
    }
});

/* Sidebar colapsable */
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (!sidebar) return;

    sidebar.classList.toggle("collapsed");
}
