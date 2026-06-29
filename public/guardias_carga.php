<?php
require "session_config.php";
require "db.php";

/* ===== TECNICOS EN ORDEN ===== */
$rotacion = ['ERIK', 'JUAN CARLOS', 'SERGIO', 'ANTONIETA'];

/* ===== MES ===== */
$mes = $_GET['mes'] ?? date('Y-m');
$inicioMes = date('Y-m-01', strtotime($mes));
$finMes = date('Y-m-t', strtotime($mes));

/* ===== FECHAS ===== */
$fechas = [];
$current = strtotime($inicioMes);
$end = strtotime($finMes);

while ($current <= $end) {
    $fechas[] = date('Y-m-d', $current);
    $current = strtotime('+1 day', $current);
}

/* ===== OBTENER GUARDIAS EXISTENTES ===== */
$stmt = $pdo->prepare("
    SELECT fecha, tecnico 
    FROM guardias 
    WHERE fecha BETWEEN ? AND ?
");
$stmt->execute([$inicioMes, $finMes]);
$guardias = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

/* ===== DETECTAR ULTIMO DEL MES ANTERIOR ===== */
$mesAnteriorFin = date('Y-m-t', strtotime('-1 month', strtotime($mes)));

$stmt = $pdo->prepare("
    SELECT tecnico 
    FROM guardias 
    WHERE fecha = ?
");
$stmt->execute([$mesAnteriorFin]);
$ultimo = $stmt->fetchColumn();

/* ===== CALCULAR POSICION EN ROTACION ===== */
$index = 0;
if ($ultimo && in_array($ultimo, $rotacion)) {
    $index = (array_search($ultimo, $rotacion) + 1) % count($rotacion);
}

/* ===== AUTO GENERAR ===== */
if (isset($_GET['auto'])) {

    foreach ($fechas as $f) {

        if (!isset($guardias[$f])) {

            $tecnico = $rotacion[$index];

            $stmt = $pdo->prepare("
                INSERT INTO guardias (fecha, tecnico, cumple, cumpleanero)
                VALUES (?, ?, FALSE, NULL)
                ON CONFLICT (fecha) DO NOTHING
            ");

            $stmt->execute([$f, $tecnico]);

            $index = ($index + 1) % count($rotacion);
        }
    }

    header("Location: guardias_carga.php?mes=$mes");
    exit;
}

/* ===== GUARDADO MANUAL ===== */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    foreach ($_POST['guardias'] as $fecha => $data) {

        $tecnico = $data['tecnico'] ?? null;

        $stmt = $pdo->prepare("
            INSERT INTO guardias (fecha, tecnico, cumple, cumpleanero)
            VALUES (?, ?, FALSE, NULL)
            ON CONFLICT (fecha) DO UPDATE
            SET tecnico = EXCLUDED.tecnico,
                updated_at = NOW()
        ");

        $stmt->execute([$fecha, $tecnico]);
    }

    header("Location: guardias_carga.php?mes=$mes");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Guardias</title>

<style>
body{
    font-family:"Segoe UI";
    background:#0f172a;
    color:#E5E7EB;
    padding:20px;
}

/* HEADER */
.top{
    text-align:center;
    margin-bottom:15px;
}

input{
    padding:8px;
    border-radius:8px;
    border:none;
}

/* BOTONES */
.btn{
    padding:8px 14px;
    border-radius:10px;
    background:#00AEEF;
    color:white;
    border:none;
    cursor:pointer;
    margin-top:5px;
}

/* GRID */
.grid{
    display:grid;
    grid-template-columns:repeat(7,1fr);
    gap:10px;
    margin-top:20px;
}

/* CARD */
.card{
    background:#1f2937;
    padding:10px;
    border-radius:10px;
}

/* SELECT */
select{
    width:100%;
    padding:6px;
    border-radius:6px;
    background:#0f172a;
    color:white;
}

/* STATUS AUTO */
.auto{
    text-align:center;
    margin-top:10px;
    color:#22c55e;
    font-size:14px;
}
</style>

</head>

<body>

<div class="top">
<h2>Guardias <?= date('F Y', strtotime($mes)) ?></h2>

<form method="GET">
<input type="month" name="mes" value="<?= $mes ?>">
<button class="btn">Cambiar</button>
</form>

<a href="guardias_carga.php?mes=<?= $mes ?>&auto=1">
<button class="btn">Auto-generar guardias</button>
</a>

</div>

<form method="POST">

<div class="grid">

<?php foreach($fechas as $f): 
$valor = $guardias[$f] ?? '';
?>

<div class="card">

<strong><?= date('d M', strtotime($f)) ?></strong>

<select name="guardias[<?= $f ?>][tecnico]">
<option value="">-- Seleccionar --</option>

<?php foreach($rotacion as $t): ?>
<option value="<?= $t ?>" <?= ($valor == $t) ? 'selected' : '' ?>>
<?= $t ?>
</option>
<?php endforeach; ?>

</select>

</div>

<?php endforeach; ?>

</div>

<button class="btn">Guardar cambios</button>

</form>

</body>
</html>
