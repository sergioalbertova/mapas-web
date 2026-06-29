<?php
require "session_config.php";
require "db.php";

/* MES */
$mes = $_GET['mes'] ?? date('Y-m');
$inicioMes = date('Y-m-01', strtotime($mes));
$finMes = date('Y-m-t', strtotime($mes));

/* TECNICOS FIJOS */
$tecnicos = [
    'ERIK',
    'JUAN CARLOS',
    'ANTONIETA',
    'SERGIO'
];

/* GUARDAR */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    foreach ($_POST['guardias'] as $fecha => $data) {

        $tecnico = $data['tecnico'] ?? null;
        $cumple = isset($data['cumple']) ? true : false;
        $cumpleanero = $data['cumpleanero'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO guardias (fecha, tecnico, cumple, cumpleanero)
            VALUES (?, ?, ?, ?)
            ON CONFLICT (fecha) DO UPDATE
            SET tecnico = EXCLUDED.tecnico,
                cumple = EXCLUDED.cumple,
                cumpleanero = EXCLUDED.cumpleanero,
                updated_at = NOW()
        ");

        $stmt->execute([$fecha, $tecnico, $cumple, $cumpleanero]);
    }

    header("Location: guardias_carga.php?mes=$mes");
    exit;
}

/* GENERAR DIAS */
$fechas = [];
$current = strtotime($inicioMes);
$end = strtotime($finMes);

while ($current <= $end) {
    $fechas[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Guardias</title>

<style>
body {
    font-family:Arial;
    background:#0f172a;
    color:white;
    padding:20px;
}

h2 {
    text-align:center;
}

/* GRID */
.grid {
    display:grid;
    grid-template-columns: repeat(7,1fr);
    gap:10px;
}

/* CARD DIA */
.card {
    background:#1f2937;
    padding:10px;
    border-radius:10px;
}

/* INPUTS */
select, input {
    width:100%;
    margin-top:5px;
    padding:5px;
    border-radius:6px;
    border:none;
}

/* BOTON */
button {
    margin-top:20px;
    padding:10px 20px;
    background:#00AEEF;
    border:none;
    border-radius:10px;
    color:white;
    cursor:pointer;
}

</style>
</head>

<body>

<h2>Guardias - <?= date('F Y', strtotime($mes)) ?></h2>

<form method="GET">
<input type="month" name="mes" value="<?= $mes ?>">
<button>Cambiar</button>
</form>

<form method="POST">

<div class="grid">

<?php foreach($fechas as $f): ?>

<div class="card">

<strong><?= date('d M', strtotime($f)) ?></strong>

<select name="guardias[<?= $f ?>][tecnico]">
<option value="">-- Técnico --</option>
<?php foreach($tecnicos as $t): ?>
<option value="<?= $t ?>"><?= $t ?></option>
<?php endforeach; ?>
</select>

<label>
<input type="checkbox" name="guardias[<?= $f ?>][cumple]"> Cumple
</label>

<input type="text" name="guardias[<?= $f ?>][cumpleanero]" placeholder="Nombre cumple">

</div>

<?php endforeach; ?>

</div>

<button type="submit">Guardar Guardias</button>

</form>

</body>
</html>