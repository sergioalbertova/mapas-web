<?php
require "auth.php";
require "db.php";

$id = $_SESSION['user_id'];

/* ============================================================
   OBTENER TÉCNICO LOGUEADO
   ============================================================ */
$stmt = $pdo->prepare("SELECT nombre FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$nombreUsuario = $usuario ? $usuario['nombre'] : "Usuario";

/* ============================
   OBTENER LISTA DE PROBLEMAS
   ============================ */
$sql = "
SELECT p.id, p.titulo, p.estado, p.fecha_creacion,
       u.nombre AS tecnico
FROM problemas p
LEFT JOIN usuarios u ON u.id = p.tecnico_responsable
ORDER BY p.fecha_creacion DESC
";
$stmt = $pdo->query($sql);
$problemas = $stmt->fetchAll(PDO::FETCH_ASSOC);
$paginaActual = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Problemas ITIL</title>
    <link rel="icon" href="apoyo2.png" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <style>
        /* ========================= */
        /* VARIABLES                 */
        /* ========================= */
        :root {
            --bg: #F4F7FA;
            --text: #1F2933;

            --topbar-bg: rgba(255, 255, 255, 0.85);
            --topbar-text: #1F2933;
            --topbar-border: rgba(0, 0, 0, 0.1);

            --sidebar-bg: #FFFFFF;
            --sidebar-text: #1F2933;
            --sidebar-border: rgba(0, 0, 0, 0.1);

            --card-bg: #FFFFFF;
            --card-text: #1F2933;

            --accent: #00AEEF;
            --shadow: rgba(0, 0, 0, 0.08);
        }

        body.dark {
            --bg: #0f172a;
            --text: #E5E7EB;

            --topbar-bg: rgba(17, 24, 39, 0.85);
            --topbar-text: #E5E7EB;
            --topbar-border: rgba(255, 255, 255, 0.1);

            --sidebar-bg: #020617;
            --sidebar-text: #E5E7EB;
            --sidebar-border: rgba(255, 255, 255, 0.1);

            --card-bg: #1f2937;
            --card-text: #E5E7EB;

            --shadow: rgba(0, 0, 0, 0.45);
        }

        /* ========================= */
        /* GENERAL                   */
        /* ========================= */
        body {
            margin: 0;
            font-family: "Segoe UI", Arial;
            background: var(--bg);
            color: var(--text);
            display: flex;
            transition: background 0.3s ease, color 0.3s ease;
        }


        .sidebar.collapsed~.main {
            margin-left: 70px;
            width: calc(100% - 70px);
        }

        /* ========================= */
        /* TOPBAR GENERAL (PRIMERO) */
        /* ========================= */


        .sidebar.collapsed~.main {
            margin-left: 70px;
            width: calc(100% - 70px);
        }


        /* ========================= */
        /* TOPBAR ITIL (DEBAJO)     */
        /* ========================= */
        .itil-topbar {
            display: flex;
            align-items: center;
            gap: 18px;
            position: fixed;
            top: 65px;
            left: 240px;
            right: 0;
            height: 55px;
            z-index: 1500;
            border-radius: 12px;
            margin: 10px 20px 0 20px;
            width: auto;
        }

        #sidebar.collapsed~.itil-topbar {
            left: 70px;
        }

        #sidebar.collapsed~.main {
            margin-left: 70px;
            width: calc(100% - 70px);
        }

        /* ESTILO PROFESIONAL DEL MENÚ ITIL */
        .itil-topbar a {
            text-decoration: none;
            color: var(--text);
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: 0.2s ease;
            font-size: 15px;
        }

        .itil-topbar a:hover {
            background: var(--sidebar-hover);
            transform: translateY(-1px);
        }

        .itil-topbar svg {
            width: 20px;
            height: 20px;
            fill: currentColor;
            opacity: 0.85;
        }

        /* ========================= */
        /* MAIN                      */
        /* ========================= */
        .main {

            margin-top: 110px;

            padding: 15px 20px;

        }


        /* ============================================================
   CORRECCIÓN DEFINITIVA PARA EL SIDEBAR COLAPSADO
   ============================================================ */
        #sidebar.collapsed~.main-shell {

            margin-left: 70px;

            width: calc(100% - 70px);

        }


        /* ========================= */
        /* TARJETAS                  */
        /* ========================= */



        .itil-topbar a.active {
            background: #00AEEF;
            color: white;
            border-bottom: 3px solid #fff;
            box-shadow: 0 3px 10px rgba(0, 174, 239, .25);
        }


        .itil-topbar a.active svg {

            fill: white;

            opacity: 1;

        }




        .card-itil {

            background: var(--card-bg);

            border-radius: 14px;

            padding: 20px;

            box-shadow: 0 8px 20px var(--shadow);

        }

        .table {

            color: var(--text);

        }

        .table thead th {

            background: var(--accent);

            color: white;

            border: none;

            padding: 14px;

        }

        .table tbody td {

            padding: 14px;

        }

        .header-itil {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 18px 20px;
            margin-bottom: 20px;
            box-shadow: 0 6px 16px var(--shadow);
        }

        .card-itil {
            background: var(--card-bg);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 8px 20px var(--shadow);
        }

        .table {
            margin-bottom: 0;
            color: var(--text);
        }

        .table thead th {
            background: var(--accent);
            color: white;
            border: none;
            padding: 14px;
        }

        .table tbody td {
            padding: 14px;
            vertical-align: middle;
        }
    </style>
    <link rel="stylesheet" href="sidebar.css">
    <link rel="stylesheet" href="topbar.css">
</head>

<body>

    <?php require "sidebar.php"; ?>
    <div class="main">
        <!-- === TOPBAR GENERAL (PRIMERO) === -->
        <?php require "topbar.php"; ?>

        <!-- === TOPBAR ITIL (DEBAJO DEL GENERAL) === -->
        <!-- === TOPBAR ITIL (DEBAJO DEL GENERAL) === -->
        <div class="itil-topbar">

            <a href="itil_incidentes.php" class="<?= $paginaActual == 'itil_incidentes.php' ? 'active' : '' ?>">
                <svg>
                    <path d="M4 4h16v4H4V4zm0 6h16v10H4V10z" />
                </svg>
                Incidentes
            </a>

            <a href="itil_incidente_nuevo.php" class="<?= $paginaActual == 'itil_incidente_nuevo.php' ? 'active' : '' ?>">
                <svg>
                    <path d="M12 5v14m7-7H5" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
                Nuevo
            </a>

            <a href="itil_problemas.php" class="<?= $paginaActual == 'itil_problemas.php' ? 'active' : '' ?>">
                <svg>
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
                Problemas
            </a>

            <a href="itil_catalogo.php" class="<?= $paginaActual == 'itil_catalogo.php' ? 'active' : '' ?>">
                <svg>
                    <path d="M4 4h16v4H4zm0 6h16v10H4z" />
                </svg>
                Catálogo Incidentes
            </a>

            <a href="itil_solicitudes.php" class="<?= $paginaActual == 'itil_solicitudes.php' ? 'active' : '' ?>">
                <svg>
                    <rect x="3" y="6" width="18" height="12" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
                En Proceso
            </a>

            <a href="itil_sla.php" class="<?= $paginaActual == 'itil_sla.php' ? 'active' : '' ?>">
                <svg>
                    <path d="M12 2v20m10-10H2" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
                SLA
            </a>

            <a href="itil_estadisticas.php" class="<?= $paginaActual == 'itil_estadisticas.php' ? 'active' : '' ?>">
                <svg>
                    <path d="M4 20V10m6 10V4m6 16v-6m6 6V8" stroke="currentColor" stroke-width="2" fill="none" />
                </svg>
                Estadísticas
            </a>

        </div>

        <!-- === MAIN: LISTADO DE PROBLEMAS === -->

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">Problemas ITIL</h4>

            <a href="itil_problema_nuevo.php" class="btn btn-primary">+ Nuevo problema</a>
        </div>


        <div class="card-itil">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>Técnico responsable</th>
                        <th>Fecha creación</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($problemas as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><?= htmlspecialchars($p['titulo']) ?></td>
                            <td><?= $p['estado'] ?></td>
                            <td><?= $p['tecnico'] ?: 'Sin asignar' ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($p['fecha_creacion'])) ?></td>
                            <td>
                                <a href="itil_problema_ver.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>


    </div>
    <!-- === SCRIPTS === -->

    <script src="theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>