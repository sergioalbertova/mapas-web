<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Cambiar contraseña</title>

<style>
/* ============================
   PALETA CORPORATIVA
   ============================ */
:root {
    --bg: #F4F7FA;
    --sidebar-bg: #FFFFFF;
    --sidebar-hover: #E8EEF5;
    --card-bg: #FFFFFF;
    --text: #1F2933;
    --subtext: #6B7280;
    --primary: #0054A6;
    --primary-hover: #003F7D;
    --accent-cyan: #00AEEF;
    --accent-red: #EF3E42;
    --shadow: rgba(0,0,0,0.08);
}

/* ============================
   TEMA OSCURO
   ============================ */
body.dark {
    --bg: #1A1D21;
    --sidebar-bg: #24272C;
    --sidebar-hover: #2F3338;
    --card-bg: #2C2F34;
    --text: #E5E7EB;
    --subtext: #9CA3AF;
    --primary: #00AEEF;
    --primary-hover: #0088C0;
    --shadow: rgba(0,0,0,0.45);
}

/* ============================
   ESTILOS GENERALES
   ============================ */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    transition: 0.3s;
    display: flex;
}

/* ============================
   SIDEBAR
   ============================ */
.sidebar {
    width: 240px;
    background: var(--sidebar-bg);
    height: 100vh;
    box-shadow: 4px 0 20px var(--shadow);
    padding: 20px 15px;
    display: flex;
    flex-direction: column;
    position: fixed;
    transition: width 0.25s ease;
    overflow: visible;
    z-index: 2000;
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: var(--primary);
    transition: opacity 0.25s ease;
}

.sidebar.collapsed h2 {
    opacity: 0;
}

/* ============================
   ITEMS DEL MENÚ
   ============================ */
.nav-item {
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
    overflow: visible;
}

.nav-item:hover {
    background: var(--sidebar-hover);
}

.nav-item svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.sidebar.collapsed .nav-text {
    display: none;
}

/* ============================
   TOOLTIP
   ============================ */
.tooltip {
    position: absolute;
    left: 80px;
    top: 50%;
    transform: translateY(-50%);
    background: var(--sidebar-bg);
    padding: 6px 12px;
    border-radius: 6px;
    box-shadow: 0 2px 8px var(--shadow);
    font-size: 13px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, left 0.2s ease;
    z-index: 99999;
}

.sidebar.collapsed .nav-item:hover .tooltip {
    opacity: 1;
    left: 75px;
}

/* ============================
   CONTENIDO PRINCIPAL
   ============================ */
.main {
    margin-left: 240px;
    padding: 40px;
    width: calc(100% - 240px);
    transition: margin-left 0.25s ease, width 0.25s ease;
    display: flex;
    justify-content: center;
}

.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

.form-container {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 20px var(--shadow);
    width: 100%;
    max-width: 450px;
}

h1 {
    text-align: center;
    color: var(--primary);
    margin-bottom: 25px;
}

label {
    font-size: 14px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    margin-bottom: 18px;
    border-radius: 8px;
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
}

button {
    width: 100%;
    padding: 12px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 15px;
}

button:hover {
    background: var(--primary-hover);
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <div class="toggle-btn" onclick="toggleSidebar()">
        <svg><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        <span class="nav-text">Menú</span>
    </div>

    <h2>Panel</h2>

    <div class="nav-item">
        <a href="index.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            <span class="nav-text">Inicio</span>
        </a>
        <span class="tooltip">Inicio</span>
    </div>

    <div class="nav-item">
        <a href="calendario.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <span class="nav-text">Calendario</span>
        </a>
        <span class="tooltip">Calendario</span>
    </div>

    <div class="nav-item">
        <a href="dashboard.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            <span class="nav-text">Mapeo de nodos</span>
        </a>
        <span class="tooltip">Mapeo de nodos</span>
    </div>

    

    <div class="nav-item">
        <a href="logout.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm2-10H8v2h10v14H8v2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
            <span class="nav-text">Cerrar sesión</span>
        </a>
        <span class="tooltip">Cerrar sesión</span>
    </div>

    <div class="nav-item" onclick="toggleTheme()">
        <svg><path d="M12 2a9 9 0 100 18 9 9 0 010-18z"/></svg>
        <span class="nav-text">Tema oscuro</span>
        <span class="tooltip">Tema oscuro</span>
    </div>

</div>

<!-- CONTENIDO PRINCIPAL -->
<div class="main">
    <div class="form-container">

        <h1>Cambiar contraseña</h1>

        <form action="guardar_password.php" method="POST">

            <label>Contraseña actual</label>
            <input type="password" name="actual" required>

            <label>Nueva contraseña</label>
            <input type="password" name="nueva" required>

            <label>Confirmar nueva contraseña</label>
            <input type="password" name="confirmar" required>

            <button type="submit">Actualizar contraseña</button>

        </form>

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

function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("collapsed");
}
</script>

</body>
</html>
