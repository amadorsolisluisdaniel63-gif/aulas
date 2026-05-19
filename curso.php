<?php
require_once __DIR__ . '/config/config.php';

startSession();
requireLogin();

$db = getDB();

$curso_id = $_GET['curso_id'] ?? null;

if (!$curso_id) {
    die("Curso no válido");
}

$userRol   = $_SESSION['user_rol'] ?? 'student';
$userNivel = $_SESSION['user_nivel'] ?? 'low';
$userId    = $_SESSION['user_id'] ?? 0;

$stmt = $db->prepare("SELECT * FROM cursos WHERE id = ?");

$stmt->execute([$curso_id]);
$curso = $stmt->fetch();

if (!$curso) {
    die("Curso no encontrado");
}


if ($userRol === 'student') {

    $stmt = $db->prepare("SELECT * FROM modulos WHERE curso_id = ? AND nivel = ? ORDER BY orden ASC, id ASC");

    $stmt->execute([$curso_id,$userNivel]);

} else {

    $stmt = $db->prepare("SELECT * FROM modulos WHERE curso_id = ? ORDER BY orden ASC, id ASC");
    $stmt->execute([$curso_id]);
}

$modulos = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($curso['nombre']) ?></title>
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="wrapper">
<h1> 📚 <?= htmlspecialchars($curso['nombre']) ?></h1>
<p><?= htmlspecialchars($curso['descripcion']) ?></p>

<br><a href="menu.php" class="btn">⬅ Volver al menú</a>

<br><br>

<?php if ($flash): ?>

<div class="alert alert-<?= $flash['type'] ?>">

<?= htmlspecialchars($flash['msg']) ?>

</div>

<?php endif; ?>

<h2>🧩 Módulos disponibles</h2>

<?php if (empty($modulos)): ?>

<div class="no-modules">

<h3>😕 No hay módulos disponibles</h3>

<p>Todavía no existen módulos para este curso.</p>

</div>
<?php else: ?>
<div class="grid">
<?php foreach ($modulos as $m): ?>

<?php

$stmtAct = $db->prepare("SELECT COUNT(*) total FROM actividades WHERE modulo_id = ?");

$stmtAct->execute([$m['id']]);
$totalActividades = $stmtAct->fetch()['total'] ?? 0;
$completadas = 0;
$promedio = 0;

if ($userRol === 'student') {

    $stmtProg = $db->prepare("SELECT COUNT(p.id) completadas,AVG(p.calificacion) promedio FROM progreso_usuario p JOIN actividades a ON p.actividad_id = a.id WHERE a.modulo_id = ? AND p.usuario_id = ? AND p.completado = 1");
    $stmtProg->execute([
        $m['id'],
        $userId
    ]);

    $progreso = $stmtProg->fetch();

    $completadas = $progreso['completadas'] ?? 0;
    $promedio    = $progreso['promedio'] ?? 0;
}

$porcentaje = 0;

if ($totalActividades > 0) {
    $porcentaje = ($completadas / $totalActividades) * 100;
}

?>

<a class="card"
href="modulo.php?id=<?= $m['id'] ?>">

<div class="card-deco">
📦
</div>

<div class="icon-orb">
📘
</div>

<h2><?= htmlspecialchars($m['titulo']) ?></h2>

<p><?= htmlspecialchars($m['descripcion']) ?></p>

<div class="module-stats">

<span class="badge">🎮 <?= $totalActividades ?> actividades</span>

<?php if ($userRol === 'student'): ?>

<span class="badge">✅ <?= $completadas ?> completadas</span>

<span class="badge">⭐ <?= number_format($promedio, 1) ?></span>

<?php endif; ?>

</div>

<?php if ($userRol === 'student'): ?>

<div class="progress-bar">

<div
class="progress-fill"
style="width:<?= $porcentaje ?>%">
</div>

</div>

<p style="margin-top:10px;font-weight:700;">

📈 Progreso:
<?= round($porcentaje) ?>%

</p>

<?php endif; ?>

<br>

<span class="btn">Entrar al módulo</span>
</a>

<?php endforeach; ?>

</div>

<?php endif; ?>

</div>

</body>
</html>