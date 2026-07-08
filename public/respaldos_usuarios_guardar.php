<?php

require "auth.php";
require "db.php";

// =====================================
// VALIDAR SESIÓN
// =====================================

$creado_por = $_SESSION['user_id'] ?? null;

if (!$creado_por) {

    header("Location: login.php");
    exit;

}

// =====================================
// RECIBIR DATOS
// =====================================

$usuario = trim($_POST['usuario'] ?? '');
$iddisco = $_POST['iddisco'] ?? '';
$tamano_gb = $_POST['tamano_gb'] ?? '';
$observaciones = trim($_POST['observaciones'] ?? '');

// =====================================
// VALIDACIONES BÁSICAS
// =====================================

if (
    $usuario === '' ||
    $iddisco === '' ||
    $tamano_gb === ''
) {

    header("Location: respaldos_usuarios_nuevo.php?error=1");
    exit;

}

// =====================================
// VERIFICAR ESPACIO DISPONIBLE
// =====================================

$stmt = $pdo->prepare("
    SELECT
        d.tamano_total_gb,

        COALESCE(
            SUM(r.tamano_gb),
            0
        ) AS utilizado

    FROM discos_respaldo d

    LEFT JOIN respaldos_usuarios r
        ON r.iddisco = d.iddisco

    WHERE d.iddisco = ?

    GROUP BY
        d.iddisco,
        d.tamano_total_gb
");

$stmt->execute([$iddisco]);

$disco = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$disco) {

    header("Location: respaldos_usuarios_nuevo.php?error=disco");
    exit;

}

$disponible =
    $disco['tamano_total_gb']
    - $disco['utilizado'];

// =====================================
// VALIDAR CAPACIDAD
// =====================================

if ($tamano_gb > $disponible) {

    header(
        "Location: respaldos_usuarios_nuevo.php?error=espacio"
    );
    exit;

}

// =====================================
// INSERTAR RESPALDO
// =====================================

$stmt = $pdo->prepare("
    INSERT INTO respaldos_usuarios
    (
        usuario,
        iddisco,
        tamano_gb,
        observaciones,
        creado_por
    )
    VALUES
    (
        ?, ?, ?, ?, ?
    )
");

$stmt->execute([
    $usuario,
    $iddisco,
    $tamano_gb,
    $observaciones,
    $creado_por
]);

// =====================================
// REDIRECCIÓN
// =====================================

header("Location: respaldos_usuarios.php?ok=1");
exit;