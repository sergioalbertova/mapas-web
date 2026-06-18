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
<title>Administrar ActiveUser</title>

<link rel="stylesheet" href="sidebar.css">
<link rel="stylesheet" href="topbar.css">

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

.contenedor {
    padding: 20px;
}

.titulo {
    font-size: 26px;
    font-weight: 600;
    margin-bottom: 5px;
}

.subtitulo {
    opacity: 0.7;
    margin-bottom: 25px;
}

#buscar {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border-radius: 10px;
    border: 1px solid #ccc;
    background: var(--card-bg);
    color: var(--text);
}

#resultados {
    margin-top: 15px;
    background: var(--card-bg);
    border-radius: 10px;
    border: 1px solid var(--sidebar-border);
}

.item {
    padding: 12px;
    border-bottom: 1px solid var(--sidebar-border);
    cursor: pointer;
}

.item:hover {
    background: rgba(0,0,0,0.05);
}

body.dark .item:hover {
    background: rgba(255,255,255,0.08);
}

.nuevo-btn {
    margin-top: 15px;
    padding: 12px 18px;
    background: var(--accent);
    color: white;
    border-radius: 10px;
    display: inline-block;
    text-decoration: none;
    font-weight: 600;
}
</style>

</head>
<body>

<?php require "sidebar.php"; ?>

<div class="main">

<?php require "topbar.php"; ?>

<div class="contenedor">

    <div class="titulo">Usuarios</div>
    <?php if ($_SESSION['rol'] === 'administrador'): ?>
    <div class="subtitulo">Buscar, editar o crear usuarios del módulo ActiveUser</div>
    
    <?php endif; ?>
    <input type="text" id="buscar" placeholder="Escribe un nombre…">

    <div id="resultados"></div>

    <!-- SOLO ADMINISTRADOR VE ESTE BOTÓN -->
    <?php if ($_SESSION['rol'] === 'administrador'): ?>
        <a href="activeuser_nuevo.php" class="nuevo-btn" id="btnNuevo" style="display:none;">
            + Nuevo usuario
        </a>
    <?php endif; ?>

</div>

</div>

<script src="theme.js"></script>

<script>
const input = document.getElementById("buscar");
const resultados = document.getElementById("resultados");
const btnNuevo = document.getElementById("btnNuevo");

input.addEventListener("keyup", () => {
    let q = input.value.trim();

    if (q.length === 0) {
        resultados.innerHTML = "";
        if (btnNuevo) btnNuevo.style.display = "none";
        return;
    }

    fetch("buscar_activeuser.php?q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(data => {

            resultados.innerHTML = "";

            if (data.length === 0) {
                resultados.innerHTML = "<div class='item'>No encontrado</div>";
                if (btnNuevo) btnNuevo.style.display = "inline-block";
                return;
            }

            if (btnNuevo) btnNuevo.style.display = "none";

            data.forEach(u => {
                let div = document.createElement("div");
                div.className = "item";
                div.textContent = u.nomuser + " — " + (u.ubicacion ?? "");
                div.onclick = () => {
                    window.location = "activeuser_editar.php?idu=" + u.idu;
                };
                resultados.appendChild(div);
            });
        });
});
</script>

</body>
</html>
