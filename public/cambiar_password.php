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
<title>Cambiar contraseña</title>
<link rel="icon" href="apoyo2.png" type="image/x-icon">
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

body.dark .form-container {
    border: 1px solid rgba(255,255,255,.10);
}

/* ============================
   ESTILOS GENERALES
   ============================ */
body {
    margin: 0;
    font-family: "Segoe UI", Arial;
    background: var(--bg);
    color: var(--text);
    display: flex;
    transition: background 0.3s ease, color 0.3s ease;
}


h2 {
    text-align: center;
    font-size: 28px;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--text);
}


/* ============================
   CONTENIDO PRINCIPAL
   ============================ */

.main {
    margin-left: 240px;
    padding: 20px 40px;
    width: calc(100% - 240px);
}


.sidebar.collapsed ~ .main {
    margin-left: 70px;
    width: calc(100% - 70px);
}


.form-container {
    background: var(--card-bg);
    padding: 30px;
    border-radius: 12px;
    border: 1px solid rgba(0,0,0,.08);
    box-shadow: 0 4px 20px var(--shadow);

    width: 100%;
    max-width: 500px;

     margin: 30px auto 0 auto;
}


.subtitle {
    text-align: center;
    color: var(--subtext);
    margin-bottom: 25px;
}

label {
    font-size: 14px;
    font-weight: 600;
}

input {
    width: 100%;
    padding: 12px;

    margin-top: 6px;
    margin-bottom: 18px;

    border-radius: 8px;

    border: 1px solid #9ca3af;

    background: var(--card-bg);
    color: var(--text);

    box-sizing: border-box;

    transition: .2s;
}

input:focus {
    outline: none;

    border-color: #00AEEF;

    box-shadow:
        0 0 0 3px rgba(0,174,239,.20);
}

button {
    width: 100%;
    padding: 12px;

    background: #00AEEF;

    color: white;

    border: none;
    border-radius: 8px;

    cursor: pointer;
    font-size: 15px;
    font-weight: 600;

    transition: .2s;
}

button:hover {
    opacity: .9;
}


.form-container {
    background: var(--card-bg);
    padding: 35px;
    border-radius: 14px;

    border: 1px solid rgba(0,0,0,.10);

    box-shadow: 0 4px 20px var(--shadow);

    width: 100%;
    max-width: 550px;

    margin: 40px auto;
}

body.dark .form-container {
    border: 1px solid rgba(255,255,255,.12);
}


</style>
<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">
</head>

<body>
<?php require "sidebar.php"; ?>
<!-- SIDEBAR -->


<!-- CONTENIDO PRINCIPAL -->
<div class="main">

     <?php require "topbar.php"; ?>

    <div class="form-container">

        <h2>Cambiar contraseña</h2>
        <div class="subtitle">
             Actualiza tu contraseña de acceso al sistema
        </div>

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

<script src="theme.js"></script>

</body>
</html>
