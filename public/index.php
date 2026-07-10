<?php
require "auth.php";
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
<link rel="icon" href="apoyo2.png" type="image/x-icon">
<style>
:root {
    --bg: #F4F7FA;
    --text: #1F2933;

    --topbar-bg: rgba(255,255,255,0.85);
    --topbar-text: #1F2933;
    --topbar-border: rgba(0,0,0,0.1);

    --sidebar-bg: #FFFFFF;
    --sidebar-text: #1F2933;
    --sidebar-border: rgba(0,0,0,0.1);

    --card-bg: #FFFFFF;
    --card-text: #1F2933;

    --accent: #00AEEF;
    --shadow: rgba(0,0,0,0.08);
}

body.dark {
    --bg: #0f172a;
    --text: #E5E7EB;

    --topbar-bg: rgba(17,24,39,0.85);
    --topbar-text: #E5E7EB;
    --topbar-border: rgba(255,255,255,0.1);

    --sidebar-bg: #020617;
    --sidebar-text: #E5E7EB;
    --sidebar-border: rgba(255,255,255,0.1);

    --card-bg: #1f2937;
    --card-text: #E5E7EB;

    --shadow: rgba(0,0,0,0.45);
}

/* ============================
     ESTILOS BASE
     ============================ */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: background 0.3s ease, color 0.3s ease;
}

/* MAIN */
.main {
    margin-left: 240px;
    padding: 20px 40px;
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
    color: var(--text);
    opacity: 0.7;
    margin-bottom: 40px;
    font-size: 15px;
}

/* GRID */
.cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 22px;
}

/* TARJETAS */
.card {
    background: var(--card-bg);
    padding: 20px;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 10px 25px var(--shadow);

    display: flex;
    align-items: flex-start;
    gap: 14px;

    cursor: pointer;
    transition: all 0.25s ease;

    text-decoration: none;
    color: var(--card-text);
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 40px var(--shadow);
}

.card svg {
    width: 26px;
    height: 26px;
    fill: var(--accent);
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
    opacity: 0.7;
}
</style>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
</head>

<body>

<?php require "sidebar.php"; ?>

<div class="main">
    
    <?php require "topbar.php"; ?>

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

        <!-- ACTIVIDADES EXTRAS (NUEVO) -->
        <a href="actividades_extras.php" class="card">
            <svg viewBox="0 0 24 24">
                <path d="M19 3h-4.18C14.4 1.84 13.3 1 12 1s-2.4.84-2.82 2H5a2 2 0 00-2 
                         2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-7 0c.55 0 
                         1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm0 16H7v-2h5v2zm5-4H7v-2h10v2zm0-4H7V9h10v2z"/>
            </svg>
            <div class="card-content">
                <div class="card-title">Actividades Extras</div>
                <div class="card-sub">Registro de actividades realizadas</div>
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

    </div>
</div>
<script src="theme.js"></script>
</body>
</html>
