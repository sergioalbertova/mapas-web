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
   PALETA COX - TEMA CLARO
   ============================ */
:root {
    --bg: #F4F7FA;
    --sidebar-bg: #FFFFFF;
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
    padding: 25px 20px;
    display: flex;
    flex-direction: column;
    position: fixed;
}

.sidebar h2 {
    margin: 0 0 25px;
    font-size: 22px;
    color: var(--primary);
}

.nav-item {
    padding: 12px 14px;
    border-radius: 8px;
    margin-bottom: 10px;
    cursor: pointer;
    transition: 0.2s;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.nav-item:hover {
    background: var(--primary);
    color: white;
}

.nav-item a {
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    gap: 10px;
}

/* ============================
   CONTENIDO PRINCIPAL
   ============================ */
.main {
    margin-left: 260px;
    padding: 40px;
    width: calc(100% - 260px);
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
}

.card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 25px var(--shadow);
    transition: 0.25s;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 12px 30px var(--shadow);
}

.card h3 {
    margin-bottom: 10px;
    font-size: 22px;
}

.card p {
    color: var(--subtext);
    font-size: 14px;
}

/* ============================
   BOTÓN TEMA
   ============================ */
.theme-btn {
    margin-top: auto;
    padding: 10px;
    background: var(--primary);
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}

.theme-btn:hover {
    background: var(--primary-hover);
}

svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>COX Panel</h2>

    <div class="nav-item">
        <a href="index.php">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            Inicio
        </a>
    </div>

    <div class="nav-item">
        <a href="calendario.php">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            Calendario
        </a>
    </div>

    <div class="nav-item">
        <a href="dashboard.php">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            Mapeo de nodos
        </a>
    </div>

    <div class="nav-item">
        <a href="cambiar_password.php">
            <svg><path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/></svg>
            Cambiar contraseña
        </a>
    </div>

    <div class="nav-item">
        <a href="logout.php">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm2-10H8v2h10v14H8v2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
            Cerrar sesión
        </a>
    </div>

    <button class="theme-btn" onclick="toggleTheme()">🌙 Tema</button>
</div>

<!-- CONTENIDO PRINCIPAL -->
<div class="main">
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
</script>

</body>
</html>
