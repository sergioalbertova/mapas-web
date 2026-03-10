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
}

.nav-item:hover {
    background: var(--primary);
    color: white;
}

.nav-item a {
    text-decoration: none;
    color: inherit;
    display: block;
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
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>COX Panel</h2>

    <div class="nav-item"><a href="index.php">🏠 Inicio</a></div>
    <div class="nav-item"><a href="calendario.php">📅 Calendario</a></div>
    <div class="nav-item"><a href="dashboard.php">🗺️ Mapeo de nodos</a></div>
    <div class="nav-item"><a href="cambiar_password.php">🔐 Cambiar contraseña</a></div>
    <div class="nav-item"><a href="logout.php">🚪 Cerrar sesión</a></div>

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
