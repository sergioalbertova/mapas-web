<?php
require "session_config.php";
require "db.php";

date_default_timezone_set('America/Mexico_City');

// Si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validar campos obligatorios
    $requeridos = ['nomuser'];
    foreach ($requeridos as $campo) {
        if (!isset($_POST[$campo]) || trim($_POST[$campo]) === '') {
            die("Error: Falta el campo obligatorio: $campo");
        }
    }

    // Recibir datos
    $nomuser       = trim($_POST['nomuser']);
    $ubicacion     = $_POST['ubicacion'] ?? null;
    $hor1          = $_POST['hor1'] ?? null;
    $hor2          = $_POST['hor2'] ?? null;
    $piso          = $_POST['piso'] ?? null;
    $ux            = $_POST['ux'] !== '' ? (int)$_POST['ux'] : null;
    $uy            = $_POST['uy'] !== '' ? (int)$_POST['uy'] : null;
    $ubimapa       = $_POST['ubimapa'] ?? null;
    $observaciones = $_POST['observaciones'] ?? null;
    $hor3          = $_POST['hor3'] ?? null;
    $ubimapa2      = $_POST['ubimapa2'] !== '' ? (int)$_POST['ubimapa2'] : null;

    // Insertar usuario SIN idu (autoincremental)
    $sql = "INSERT INTO activeuser 
            (nomuser, ubicacion, hor1, hor2, piso, ux, uy, ubimapa, observaciones, hor3, ubimapa2)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            $nomuser,
            $ubicacion,
            $hor1,
            $hor2,
            $piso,
            $ux,
            $uy,
            $ubimapa,
            $observaciones,
            $hor3,
            $ubimapa2
        ]);

        $msg = "Usuario agregado correctamente.";
    } catch (Exception $e) {
        $msg = "Error al guardar: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Nuevo Usuario</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f4;
    padding: 20px;
}
.form-box {
    background: white;
    padding: 20px;
    border-radius: 10px;
    width: 450px;
    margin: auto;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}
input, textarea {
    width: 100%;
    padding: 8px;
    margin-bottom: 12px;
}
button {
    padding: 10px 15px;
    background: #0054A6;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
button:hover {
    background: #003f7d;
}
</style>
</head>
<body>

<div class="form-box">
    <h2>Agregar nuevo usuario</h2>

    <?php if (isset($msg)) echo "<p><strong>$msg</strong></p>"; ?>

    <form method="POST">

        <label>Nombre (nomuser) *</label>
        <input type="text" name="nomuser" required>

        <label>Ubicación</label>
        <input type="text" name="ubicacion">

        <label>Horario 1</label>
        <input type="text" name="hor1">

        <label>Horario 2</label>
        <input type="text" name="hor2">

        <label>Piso</label>
        <input type="text" name="piso">

        <label>UX</label>
        <input type="number" name="ux">

        <label>UY</label>
        <input type="number" name="uy">

        <label>Ubimapa</label>
        <input type="text" name="ubimapa">

        <label>Observaciones</label>
        <textarea name="observaciones"></textarea>

        <label>Horario 3</label>
        <input type="text" name="hor3">

        <label>Ubimapa 2</label>
        <input type="number" name="ubimapa2">

        <button type="submit">Guardar usuario</button>
    </form>
</div>

</body>
</html>
