<?php
require "session_config.php";
require "db.php";

/* ===== MES ===== */
$mes = $_GET['mes'] ?? date('Y-m');
$inicioMes = date('Y-m-01', strtotime($mes));
$finMes = date('Y-m-t', strtotime($mes));

/* ===== TECNICOS ===== */
$tecnicos = [
    'ERIK',
    'JUAN CARLOS',
    'ANTONIETA',
    'SERGIO'
];

/* ===== GUARDAR ===== */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    foreach ($_POST['guardias'] as $fecha => $data) {

        $tecnico = $data['tecnico'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO guardias (fecha, tecnico, cumple, cumpleanero)
            VALUES (?, ?, FALSE, NULL)
            ON CONFLICT (fecha) DO UPDATE
            SET tecnico = EXCLUDED.tecnico,
                cumple = FALSE,
                cumpleanero = NULL,
                updated_at = NOW()
        ");

        $stmt->execute([$fecha, $tecnico]);
    }

    header("Location: guardias_carga.php?mes=$mes");
    exit;
}

/* ===== GENERAR DIAS ===== */
$fechas = [];
$current = strtotime($inicioMes);
$end = strtotime($finMes);

while ($current <= $end) {
    $fechas[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Guardias</title>

<style>

body{
    font-family:"Segoe UI", Arial;
    background:#0f172a;
    color:#e5e7eb;
    padding:20px;
}

h2{
    text-align:center;
}

/* SELECT MES */
.filtro-mes{
    text-align:center;
    margin-bottom:20px;
}

.filtro-mes input{
    padding:8px;
    border-radius:8px;
    border:none;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns: repeat(7,1fr);
    gap:12px;
}

/* CARD DIA */
.card{
    background:#1f2937;
    padding:10px;
    border-radius:12px;
    box-shadow:0 5px 15px rgba(0,0,0,0.3);
}

/* FECHA */
.fecha{
    font-size:13px;
    font-weight:bold;
    margin-bottom:5px;
}

/* SELECT */
select{
    width:100%;
    padding:6px;
    border-radius:8px;
    border:none;
    background:#0f172a;
    color:#e5e7eb;
}

/* BOTON */
.btn-save{
    display:block;
    margin:25px auto;
    padding:10px 20px;
    background:#00AEEF;
    border:none;
    border-radius:12px;
    color:white;
    font-weight:bold;
    cursor:pointer;
    font-size:14px;
}

.btn-save:hover{
    background:#008FCC;
}

</style>
</head>

<body>

<h2>Guardias - <?= date('F Y', strtotime($mes)) ?></h2>

<!-- CAMBIAR MES -->
<form method="GET" class="filtro-mes">
    <input type="month" name="mes" value="<?= $mes ?>">
    <button class="btn-save" style="margin-top:10px;">Cambiar</button>
</form>

<form method="POST">

<div class="grid">

<?php foreach($fechas as $f): ?>

<div class="card">

    <div class="fecha"><?= date('d M', strtotime($f)) ?></div>

    <select name="guardias[<?= $f ?>][tecnico]">
        <option value="">-- Seleccionar --</option>
        <?php foreach($tecnicos as $t): ?>
            <option value="<?= $t ?>"><?= $t ?></option>
        <?php endforeach; ?>
    </select>

</div>

<?php endforeach; ?>

</div>

<button class="btn-save">Guardar Guardias</button>

</form>

</body>
</html>