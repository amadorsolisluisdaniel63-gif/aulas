<?php
require_once __DIR__ . '/config/config.php';

startSession();
requireRole('teacher');

$db = getDB();

$grupo = $_GET['grupo'] ?? '';

$stmt = $db->prepare("
SELECT
u.id,
u.nombre,
u.apellido,

AVG(p.calificacion) promedio

FROM usuarios u

LEFT JOIN progreso_usuario p
ON u.id = p.usuario_id

WHERE u.rol='student'
AND u.nivel=?

GROUP BY u.id
");

$stmt->execute([$grupo]);

$alumnos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Boletas</title>
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="wrapper">

<h1>📄 Boleta del Grupo</h1>

<table border="1" cellpadding="10">

<tr>
<th>Alumno</th>
<th>Promedio</th>
</tr>

<?php foreach ($alumnos as $a): ?>

<tr>

<td>
<?= htmlspecialchars($a['nombre'].' '.$a['apellido']) ?>
</td>

<td>
<?= number_format($a['promedio'],2) ?>
</td>

</tr>

<?php endforeach; ?>

</table>

</div>

</body>
</html>