<?php
require_once __DIR__ . '/config/config.php';

startSession();
requireLogin();

$db = getDB();

$modulo_id = $_GET['id'] ?? null;

$stmt = $db->prepare("
SELECT *
FROM modulos
WHERE id=?
");

$stmt->execute([$modulo_id]);

$modulo = $stmt->fetch();

if (!$modulo) {
    die("Módulo no encontrado");
}





$stmt = $db->prepare("
SELECT *
FROM actividades
WHERE modulo_id=?
ORDER BY id ASC
");

$stmt->execute([$modulo_id]);

$actividades = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Módulo</title>
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="wrapper">

<h1>
<?= htmlspecialchars($modulo['titulo']) ?>
</h1>

<div class="grid">

<?php foreach ($actividades as $a): ?>

<a class="card" href="actividad.php?id=<?= $a['id'] ?>">

<h2>
<?= htmlspecialchars($a['titulo']) ?>
</h2>

<p>
<?= htmlspecialchars($a['tipo']) ?>
</p>

<p>
⭐ <?= $a['puntos'] ?> puntos
</p>

</a>

<?php endforeach; ?>

</div>

</div>

</body>
</html>