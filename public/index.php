<?php
require "session_config.php";
require "db.php";

$id = $_SESSION['user_id'];

// Obtener nombre real del usuario
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Inicio</title>

<style>
:root {
    --bg: #F4F7FA;
    --card-bg: #FFFFFF;
    --text: #1F2933;
    --subtext: #6B7280;
    --primary: #0054A6;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #1A1D21;
    --card-bg: #2C2F34;
    --text: #E5E7EB;
    --subtext: #9CA3AF;
    --primary: #00AEEF;
    --shadow: rgba(0,0,0,0.45);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: radial-gradient(circle at top, #0f172a, #020617);
    color: var(--text);
    display: flex;
    transition: 0.3s;
}


/* SIDEBAR */
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
.sidebar.collapsed { width: 70px; }

.sidebar h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: var(--primary);
    transition: opacity 0.25s ease;
}
.sidebar.collapsed h2 { opacity: 0; }

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
}
.nav-item:hover { background: var(--sidebar-hover); }

.nav-item a {
    display:flex;
    align-items:center;
    gap:12px;
    color:inherit;
    text-decoration:none;
}

.nav-item svg {
    width: 20px;
    height: 20px;
    fill: currentColor;
}

.sidebar.collapsed .nav-text { display: none; }

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

/* MAIN */
.main {
    margin-left: 240px;
    padding: 40px;
    width: calc(100% - 240px);
    transition: margin-left 0.25s ease;
}
.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}

/* TITULO */
.main h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 8px;
    font-weight: 600;
}

.subtitle {
    text-align: center;
    color: var(--subtext);
    margin-bottom: 40px;
    font-size: 15px;
}

/* GRID 4×2 */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 22px;
}

/* TARJETA EMPRESARIAL */
.card {
    background: linear-gradient(145deg, #1f2937, #111827);
    padding: 20px;
    border-radius: 18px;

    border: 1px solid rgba(255,255,255,0.05);

    box-shadow: 0 10px 25px rgba(0,0,0,0.4);

    display: flex;
    align-items: flex-start;
    gap: 14px;

    cursor: pointer;
    transition: all 0.25s ease;

    text-decoration: none;
    color: inherit;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.5);
}


.card svg {
    width: 26px;
    height: 26px;
    fill: #00aaff;
}

/* contenedor visual para icono */
.card svg {
    background: rgba(0,120,212,0.15);
    padding: 10px;
    border-radius: 10px;
}

.card-content {
    display: flex;
    flex-direction: column;
}

.card-title {
    font-size: 16px;
    font-weight: 600;
}

.card-sub {
    font-size: 13px;
    color: #9ca3af;
}


</style>
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">

    <h2>Bienvenido, <?= htmlspecialchars($nombreUsuario) ?></h2>
    <div class="subtitle">Panel principal de operaciones TI</div>

    <div class="cards-grid">

        <!-- INICIO -->
        <a href="index.php" class="card">
            <svg><path d="M10 2L2 8h2v8h4V12h4v4h4V8h2z"/></svg>
            <div class="card-content">
                <div class="card-title">Inicio</div>
                <div class="card-sub">Página principal</div>
            </div>
        </a>

        <!-- INCIDENTES / APOYOS -->
        <a href="itil_incidentes.php" class="card">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 2v2h8v-2H8z"/></svg>
            <div class="card-content">
                <div class="card-title">Incidentes / Apoyos</div>
                <div class="card-sub">Gestión y seguimiento</div>
            </div>
        </a>

        <!-- MAPEO DE NODOS -->
        <a href="dashboard.php" class="card">
            <svg><path d="M3 3h8v8H3V3zm10 0h8v5h-8V3zM3 13h5v8H3v-8zm7 0h11v8H10v-8z"/></svg>
            <div class="card-content">
                <div class="card-title">Mapeo de nodos</div>
                <div class="card-sub">Ubicación de nodos</div>
            </div>
        </a>

        <!-- CALENDARIO -->
        <a href="calendario.php" class="card">
            <svg><path d="M6 2v2H4v2h12V4h-2V2h-2v2H8V2H6zm12 6H2v10h16V8z"/></svg>
            <div class="card-content">
                <div class="card-title">Calendario</div>
                <div class="card-sub">Monitoreo de Guardias</div>
            </div>
        </a>

        <!-- INCIDENTES TI -->
        <a href="incidentes.php" class="card">
            <svg><path d="M4 4h16v4H4V4zm0 6h16v10H4V10zm4 2v2h8v-2H8z"/></svg>
            <div class="card-content">
                <div class="card-title">Incidentes TI</div>
                <div class="card-sub">Temas que se pueden registrar</div>
            </div>
        </a>

        <!-- CAMBIAR CONTRASEÑA -->
        <a href="cambiar_password.php" class="card">
            <svg><path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/></svg>
            <div class="card-content">
                <div class="card-title">Cambiar contraseña</div>
                <div class="card-sub">Seguridad de acceso</div>
            </div>
        </a>

        <!-- CERRAR SESIÓN -->
        <a href="logout.php" class="card">
            <svg><path d="M16 13v-2H7V8l-5 4 5 4v-3h9zm2-10H8v2h10v14H8v2h10a2 2 0 002-2V5a2 2 0 00-2-2z"/></svg>
            <div class="card-content">
                <div class="card-title">Cerrar sesión</div>
                <div class="card-sub">Salir del sistema</div>
            </div>
        </a>

        <!-- TEMA OSCURO -->
        <div class="card" onclick="toggleTheme()">
            <svg viewBox="0 0 24 24">
    <path d="M21 12.79A9 9 0 0111.21 3 7 7 0 1019 14.79 9 9 0 0121 12.79z"/></svg>

            <div class="card-content">
                <div class="card-title">Tema oscuro</div>
                <div class="card-sub">Cambiar apariencia</div>
            </div>
        </div>

    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById("sidebar").classList.toggle("collapsed");
}

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
