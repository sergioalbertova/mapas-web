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
    <link rel="stylesheet" href="login2.css">
</head>
<body>
<canvas id="particles"></canvas>

<div class="login-wrapper">
    <div class="login-illustration">
        <div class="illu-card">
            <h2>Bienvenido</h2>
            <p>Accede a tu panel para gestionar tus sistemas.</p>
            <div class="illu-placeholder">
                <img src="img2.png" alt="Ilustración" class="illu-img" style="width: 120%; height: auto;">
                
            </div>
        </div>
    </div>

    <div class="login-panel">
        <div class="login-box">
            <h3></h3>
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
<script>
const canvas = document.getElementById("particles");
const ctx = canvas.getContext("2d");

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let particles = [];

for (let i = 0; i < 80; i++) {
    particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 2 + 1,
        dx: (Math.random() - 0.5) * 0.6,
        dy: (Math.random() - 0.5) * 0.6
    });
}

function animate() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    particles.forEach(p => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
        ctx.fillStyle = "rgba(255,255,255,0.7)";
        ctx.fill();

        p.x += p.dx;
        p.y += p.dy;

        if (p.x < 0 || p.x > canvas.width) p.dx *= -1;
        if (p.y < 0 || p.y > canvas.height) p.dy *= -1;
    });

    requestAnimationFrame(animate);
}

animate();
</script>

</body>
</html>
