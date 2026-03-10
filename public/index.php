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
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: #f3f6fb;
}
.header {
    background: #1e88e5;
    color: white;
    padding: 15px 25px;
    font-size: 22px;
}
.container {
    max-width: 900px;
    margin: 40px auto;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
}
.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    text-align: center;
    cursor: pointer;
    transition: 0.2s;
}
.card:hover {
    transform: translateY(-4px);
}
.card h3 {
    margin-bottom: 10px;
}
.card a {
    text-decoration: none;
    color: inherit;
}
.logout {
    position: fixed;
    top: 15px;
    right: 20px;
    background: #e53935;
    padding: 10px 15px;
    color: white;
    border-radius: 6px;
    text-decoration: none;
}
</style>
</head>
<body>

<div class="header">
    Bienvenido, <?= $_SESSION['usuario'] ?>
</div>

<a class="logout" href="logout.php">Cerrar sesión</a>

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

</body>
</html>
