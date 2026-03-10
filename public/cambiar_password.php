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
body {
    font-family: Arial, sans-serif;
    background: #f3f6fb;
    padding: 40px;
}
.container {
    max-width: 400px;
    margin: auto;
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
input {
    width: 100%;
    padding: 10px;
    margin-top: 6px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    padding: 12px;
    background: #1e88e5;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #1565c0;
}
</style>
</head>
<body>

<div class="container">
    <h2>Cambiar contraseña</h2>

    <form action="guardar_password.php" method="POST">
        <label>Contraseña actual</label>
        <input type="password" name="actual" required>

        <label>Nueva contraseña</label>
        <input type="password" name="nueva" required>

        <label>Confirmar nueva contraseña</label>
        <input type="password" name="confirmar" required>

        <button type="submit">Guardar</button>
    </form>
</div>

</body>
</html>
