<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Iniciar sesión</title>

<style>
* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
    font-family: "Segoe UI", sans-serif;
}

/* FONDO */
body {
    height: 100vh;
    background: radial-gradient(circle at top, #0f172a, #020617);
}

/* CONTENEDOR */
.login-wrapper {
    display: flex;
    height: 100%;
}

/* LADO IZQUIERDO */
.login-illustration {
    flex: 1;
    background: linear-gradient(135deg, #0078d4, #00c6ff);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 60px;
}

.illu-card {
    color: white;
    max-width: 420px;
}

.illu-card h1 {
    font-size: 34px;
    margin-bottom: 10px;
}

.illu-card p {
    opacity: 0.9;
    margin-bottom: 30px;
}

/* IMAGEN */
.illu-placeholder {
    text-align: center;
}

.illu-img {
    width: 70%;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.4));
}

/* LADO DERECHO */
.login-panel {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* CARD LOGIN */
.login-box {
    width: 340px;
    background: #111827;
    padding: 35px;
    border-radius: 18px;
    border: 5px solid rgba(255, 255, 255, 0.89);
    box-shadow: 0 25px 50px rgba(0,0,0,0.6);
    animation: fadeIn 0.5s ease;
}

.login-box h2 {
    margin-bottom: 5px;
    color: #fff;
}

.subtitle {
    color: #9ca3af;
    font-size: 14px;
    margin-bottom: 20px;
}

/* INPUTS */
.input-group {
    margin-bottom: 15px;
}

.input-group label {
    font-size: 13px;
    color: #d1d5db;
}

.input-with-icon {
    display: flex;
    align-items: center;
    background: #1f2937;
    border-radius: 10px;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid transparent;
    transition: 0.2s;
}

.input-with-icon:focus-within {
    border: 1px solid rgba(0,120,212,0.6);
    box-shadow: 0 0 0 2px rgba(0,120,212,0.2);
}

.input-with-icon .icon {
    margin-right: 10px;
    color: #9ca3af;
}

.input-with-icon input {
    background: transparent;
    border: none;
    outline: none;
    color: white;
    width: 100%;
}

/* BOTÓN */
.btn {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 10px;
    background: linear-gradient(135deg, #0078d4, #00aaff);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: 0.3s;
    margin-top: 10px;
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(0,120,212,0.5);
}

/* MENSAJE */
#msg {
    margin-top: 15px;
    font-size: 13px;
    text-align: center;
}

/* ANIMACIÓN */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* RESPONSIVE */
@media(max-width: 900px) {
    .login-illustration {
        display: none;
    }
}

.icon svg {
    width: 18px;
    height: 18px;
    fill: #9ca3af;
}
</style>

</head>

<body>

<div class="login-wrapper">

    <!-- IZQUIERDA -->
    <div class="login-illustration">
        <div class="illu-card">
            <h1>Panel TI</h1>
            <p>Gestión de incidentes y operaciones tecnológicas</p>

            <div class="illu-placeholder">
                <img src="imagenlogin.png" class="illu-img">
            </div>
        </div>
    </div>

    <!-- DERECHA -->
    <div class="login-panel">
        <div class="login-box">
            <h2>Iniciar sesión</h2>
            <p class="subtitle">Accede a tu sistema</p>

            <!-- FORMULARIO REAL (POST) -->
            <form action="login_process.php" method="POST">

                <div class="input-group">
                    <label>Usuario</label>
                    <div class="input-with-icon">
                        <span class="icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 12c2.7 0 5-2.3 5-5s-2.3-5-5-5-5 2.3-5 5 2.3 5 5 5zm0 2c-3.3 0-8 1.7-8 5v3h16v-3c0-3.3-4.7-5-8-5z"/>
                            </svg>
                        </span>
                        <input type="text" name="usuario" required autocomplete="username">
                    </div>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <div class="input-with-icon">
                        <span class="icon">
                            <svg viewBox="0 0 24 24">
                                <path d="M12 1a5 5 0 00-5 5v3H5v10h14V9h-2V6a5 5 0 00-5-5zm-3 5a3 3 0 016 0v3H9V6zm1 6h4v6h-4v-6z"/>
                            </svg>
                        </span>
                        <input type="password" name="clave" required autocomplete="current-password">
                    </div>
                </div>

                <!-- RECORDARME -->
                <label style="display:flex; align-items:center; gap:6px; margin-top:10px; color:white;">
                    <input type="checkbox" name="remember" value="1">
                    Recordarme
                </label>

                <button type="submit" class="btn">Entrar</button>
            </form>

            <p id="msg"></p>
        </div>
    </div>

</div>

</body>
</html>
