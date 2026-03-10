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
:root {
    --bg: #f3f6fb;
    --card-bg: #ffffff;
    --text: #1f2933;
    --subtext: #6b7280;
    --primary: #1e88e5;
    --primary-dark: #1565c0;
    --shadow: rgba(0,0,0,0.08);
}

/* Modo oscuro */
body.dark {
    --bg: #1e1e1e;
    --card-bg: #2a2a2a;
    --text: #e5e5e5;
    --subtext: #bdbdbd;
    --shadow: rgba(0,0,0,0.4);
}

body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    transition: 0.3s;
}

.header {
    background: var(--primary);
    color: white;
    padding: 18px 30px;
    font-size: 22px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header button {
    background: rgba(255,255,255,0.2);
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    color: white;
    cursor: pointer;
}

.header button:hover {
    background: rgba(255,255,255,0.35);
}

.container {
    max-width: 1100px;
    margin: 40px auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
    padding: 0 20px;
}

.card {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 8px 25px var(--shadow);
    text-align: center;
    cursor: pointer;
    transition: 0.25s;
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

.card a {
    text-decoration: none;
    color: inherit;
}

.logout {
    background: #e53935;
    padding: 10px 15px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}

.logout:hover {
    background: #c62828;
}
</style>
</head>

<body>

<div class="header">
    <div>Bienvenido, <?= $_SESSION['usuario'] ?></div>

    <div>
        <button onclick="toggleTheme()">🌙 Tema</button>
        <a class="logout" href="logout.php">Cerrar sesión</a>
    </div>
</div>

<div class="container">

    <a href="calendario.php">
        <div class="card">
            <h3>📅 Calendario</h3>
            <p>Ver guardias y programación</p>
        </div>
    </a>

    <a href="dashboard.php">
        <div class="card">
            <h3>🗺️ Mapeo de nodos</h3>
            <p>Entrar al sistema de nodos</p>
        </div>
    </a>

    <a href="cambiar_password.php">
        <div class="card">
            <h3>🔐 Cambiar contraseña</h3>
            <p>Actualizar tu acceso</p>
        </div>
    </a>

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
