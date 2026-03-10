<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="login-wrapper">
    <div class="login-illustration">
        <div class="illu-card">
            <h2>Bienvenido</h2>
            <p>Accede a tu panel para gestionar tus nodos y calendarios.</p>
            <div class="illu-placeholder">
                <!-- Aquí podrías poner una imagen <img> si quieres -->
                <span>Ilustración</span>
            </div>
        </div>
    </div>

    <div class="login-panel">
        <div class="login-box">
            <h3>Hola!</h3>
            <p class="subtitle">Inicia sesión para comenzar</p>

            <form id="loginForm">
                <div class="input-group">
                    <label>Usuario</label>
                    <div class="input-with-icon">
                        <span class="icon">👤</span>
                        <input type="text" name="usuario" required autocomplete="username">
                    </div>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <div class="input-with-icon">
                        <span class="icon">🔒</span>
                        <input type="password" name="clave" required autocomplete="current-password">
                    </div>
                </div>

                <button type="submit" class="btn">Entrar</button>
            </form>

            <p id="msg"></p>
        </div>
    </div>
</div>

<script src="login.js"></script>
</body>
</html>
