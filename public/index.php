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
<title>Panel principal</title>

<style>
/* ============================
   PALETA CORPORATIVA - TEMA CLARO
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
    transition: 0.3s;
    overflow: hidden;
}

.sidebar.collapsed {
    width: 70px;
}

.sidebar h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: var(--primary);
    transition: 0.3s;
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
    transition: 0.2s;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 12px;
    position: relative;
}

.nav-item:hover {
    background: var(--sidebar-hover);
}

.nav-item svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.sidebar.collapsed .nav-item {
    justify-content: center;
}

.sidebar.collapsed .nav-text {
    display: none;
}

/* ============================
   TOOLTIP
   ============================ */
.tooltip {
    position: absolute;
    left: 75px;
    background: var(--sidebar-bg);
    padding: 6px 10px;
    border-radius: 6px;
    box-shadow: 0 2px 8px var(--shadow);
    font-size: 13px;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transform: translateY(-50%);
    top: 50%;
    transition: 0.2s;
    z-index: 9999;
}

.sidebar.collapsed .nav-item:hover .tooltip {
    opacity: 1;
}

/* ============================
   BOTÓN DE COLAPSAR
   ============================ */
.toggle-btn {
    margin-bottom: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* ============================
   TOPBAR
   ============================ */
.topbar {
    position: fixed;
    left: 240px;
    top: 0;
    height: 60px;
    width: calc(100% - 240px);
    background: var(--sidebar-bg);
    box-shadow: 0 2px 10px var(--shadow);
    display: flex;
    align-items: center;
    padding-left: 20px;
    gap: 20px;
    z-index: 10;
    transition: 0.3s;
}

.sidebar.collapsed ~ .topbar {
    left: 70px;
    width: calc(100% - 70px);
}

.logo {
    height: 36px;
}

/* ============================
   CONTENIDO PRINCIPAL
   ============================ */
.main {
    margin-left: 240px;
    margin-top: 80px;
    padding: 30px;
    width: calc(100% - 240px);
    transition: 0.3s;
    display: flex;
    justify-content: center;
}

.sidebar.collapsed ~ .topbar + .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 18px;
    max-width: 900px;
    width: 100%;
}

.card {
    background: var(--card-bg);
    padding: 16px;
    border-radius: 12px;
    box-shadow: 0 4px 12px var(--shadow);
    transition: 0.25s;
    cursor: pointer;
    border: 1px solid rgba(255,255,255,0.06);
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 24px var(--shadow);
}

.card h3 {
    margin-bottom: 6px;
    font-size: 17px;
}

.card p {
    color: var(--subtext);
    font-size: 12px;
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

    <!-- INICIO -->
    <div class="nav-item">
        <a href="index.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            <span class="nav-text">Inicio</span>
            <span class="tooltip">Inicio</span>
        </a>
    </div>

    <!-- CALENDARIO -->
    <div class="nav-item">
        <a href="calendario.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <span class="nav-text">Calendario</span>
            <span class="tooltip">Calendario</span>
        </a>
    </div>

    <!-- MAPEO -->
    <div class="nav-item">
        <a href="dashboard.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            <span class="nav-text">Mapeo de nodos</span>
            <span class="tooltip">Mapeo de nodos</span>
        </a>
    </div>

    <!-- CAMBIAR CONTRASEÑA -->
    <div class="nav-item">
        <a href="cambiar_password.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/></svg>
            <span class="nav-text">Cambiar contraseña</span>
            <span class="tooltip">Cambiar contraseña</span>
        </a>
    </div>

    <!-- CERRAR SESIÓN -->
    <div class="nav-item">
        <a href="logout.php" style="display:flex;align-items:center;gap:12px;color:inherit;text-decoration:none;">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm2-10H8v2h10v14H8v2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
            <span class="nav-text">Cerrar sesión</span>
            <span class="tooltip">Cerrar sesión</span>
        </a>
    </div>

    <!-- TEMA OSCURO -->
    <div class="nav-item" onclick="toggleTheme()">
        <svg><path d="M12 2a9 9 0 100 18 9 9 0 010-18z"/></svg>
        <span class="nav-text">Tema oscuro</span>
        <span class="tooltip">Tema oscuro</span>
    </div>

</div>

<!-- TOPBAR -->
<div class="topbar">
    <img src="logo.png" class="logo">
    <span class="top-title">Panel Administrativo</span>
</div>

<!-- CONTENIDO PRINCIPAL -->
<div class="main" id="main">
    <h1>Bienvenido, <?= $_SESSION['usuario'] ?></h1>

    <div class="cards">

        <a href="calendario.php" style="text-decoration:none;color:inherit;">
            <div class="card">
                <h3>📅 Calendario</h3>
                <p>Ver guardias y programación</p>
            </div>
        </a>

        <a href="dashboard.php" style="text-decoration:none;color:inherit;">
            <div class="card">
                <h3>🗺️ Mapeo de nodos</h3>
                <p>Entrar al sistema de nodos</p>
            </div>
        </a>

        <a href="cambiar_password.php" style="text-decoration:none;color:inherit;">
            <div class="card">
                <h3>🔐 Cambiar contraseña</h3>
                <p>Actualizar tu acceso</p>
            </div>
        </a>

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
