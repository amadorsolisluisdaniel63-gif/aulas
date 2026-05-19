<?php
require_once __DIR__ . '/config/config.php';

startSession();
requireLogin();

$db = getDB();

$actividad_id = $_GET['id'] ?? null;

if (!$actividad_id) {   
    die("Actividad inválida");
}

$stmt = $db->prepare("SELECT a.*, m.titulo AS modulo, c.nombre AS curso FROM actividades a JOIN modulos m ON a.modulo_id = m.id JOIN cursos c ON m.curso_id = c.id WHERE a.id = ?");
$stmt->execute([$actividad_id]);

$actividad = $stmt->fetch();

if (!$actividad) {
    die("Actividad no encontrada");
}

$userId = $_SESSION['user_id'];





if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $respuesta = clean($_POST['respuesta'] ?? '');

    $calificacion = rand(70, 100);

    $stmt = $db->prepare("INSERT INTO progreso_usuario(usuario_id, actividad_id, completado, calificacion, intentos, fecha_completado)VALUES (?, ?, 1, ?, 1, NOW()) ON DUPLICATE KEY UPDATE completado=1,calificacion=?,intentos=intentos+1,fecha_completado=NOW()");

    $stmt->execute([$userId,$actividad_id,$calificacion,$calificacion]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg' => 'Actividad completada 🎉'
    ];
    header("Location: actividad.php?id=".$actividad_id);
    exit;
}





$stmt = $db->prepare("SELECT * FROM progreso_usuario WHERE usuario_id=? AND actividad_id=?");
$stmt->execute([$userId, $actividad_id]);

$progreso = $stmt->fetch();

?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Actividad</title>
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="wrapper">

<h1><?= htmlspecialchars($actividad['titulo']) ?></h1>

<p>
Curso:
<?= htmlspecialchars($actividad['curso']) ?>
</p>

<p>
Módulo:
<?= htmlspecialchars($actividad['modulo']) ?>
</p>

<div class="card">

<h2>Contenido</h2>

<p>
<?= nl2br(htmlspecialchars($actividad['contenido'])) ?>
</p>

</div>

<form method="POST">

<textarea name="respuesta" placeholder="Escribe tu respuesta..." required></textarea>

<br><br>

<button type="submit">Enviar actividad</button>
</form>

<?php if ($progreso): ?>

<div class="card">

<h2>📊 Tu progreso</h2>

<p>
Calificación:
<?= $progreso['calificacion'] ?>
</p>

<p>
Intentos:
<?= $progreso['intentos'] ?>
</p>

</div>

<?php endif; ?>

</div>

</body>
</html>