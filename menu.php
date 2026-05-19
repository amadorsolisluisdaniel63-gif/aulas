<?php
require_once __DIR__ . '/config/config.php';

startSession();
requireLogin();

$db = getDB();

$userName  = htmlspecialchars($_SESSION['user_name']  ?? 'Usuario');
$userRol   = $_SESSION['user_rol']   ?? 'student';
$userNivel = $_SESSION['user_nivel'] ?? null;
$userId    = $_SESSION['user_id']    ?? 0;

/* CORRECCIÓN DE SEGURIDAD Y FILTRADO:
  Si es estudiante, solo traemos los cursos en los que está inscrito.
  Si es admin o maestro, traemos todos para que puedan gestionar.
*/
if ($userRol === 'student') {
    // Unimos 'cursos' con 'inscripciones' para mostrar solo lo que le corresponde al alumno
    $stmt = $db->prepare("
        SELECT c.* FROM cursos c
        INNER JOIN inscripciones i ON c.id = i.curso_id
        WHERE i.user_id = ?
        ORDER BY c.id DESC
    ");
    $stmt->execute([$userId]);
} else {
    // Admins y Maestros siguen viendo todo
    $stmt = $db->query("SELECT * FROM cursos ORDER BY id DESC");
}

$cursos = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$rolInfo = [
  'admin'   => ['emoji' => '🛡️', 'label' => 'Administrador', 'css' => 'admin'],
  'teacher' => ['emoji' => '👩‍🏫', 'label' => 'Maestro/a', 'css' => 'teacher'],
  'student' => ['emoji' => '🎒', 'label' => 'Alumno/a', 'css' => 'student'],
];

$rol = $rolInfo[$userRol] ?? $rolInfo['student'];

$nivelLabel = '';
if ($userRol === 'student') {
  $nivelLabel = ($userNivel === 'low')
    ? '🌱 Primaria Baja (1°-3°)'
    : '🌟 Primaria Alta (4°-6°)';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Aulas Mágicas — Menú</title>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;700;800;900&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="assets/css/styles.css"/>
</head>
<body>

<?php include __DIR__ . '/includes/decorations.php'; ?>

<div class="wrapper">
    <div class="topbar">
      <div class="topbar-user">
        <div class="avatar"><?= $rol['emoji'] ?></div>
        <div>
          <div style="font-size:.85rem;font-weight:900;color:#3a2a1a;">
            <?= $userName ?>
          </div>
          <?php if ($nivelLabel): ?>
            <div style="font-size:.72rem;color:#9A7050;font-weight:800;">
              <?= $nivelLabel ?>
            </div>
          <?php endif; ?>
        </div>
        <span class="role-pill <?= $rol['css'] ?>">
          <?= $rol['label'] ?>
        </span>
      </div>
      <a href="logout.php" class="logout-btn">Salir</a>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom:24px;">
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
    <?php endif; ?>

    <header>
      <div class="logo-badge">✨ Plataforma Educativa</div>
      <h1>Aulas Mágicas</h1>
      <p class="tagline">Explora mundos de conocimiento 🚀</p>
    </header>

    <div class="grid">
      <?php if ($userRol === 'admin'): ?>
        <a class="card" href="admin_cursos.php">
          <div class="card-deco">🛠️</div>
          <div class="icon-orb">📚</div>
          <h2>Gestionar Cursos</h2>
          <p>Crear, editar y eliminar cursos</p>
        </a>
      <?php endif; ?>

      <?php if ($userRol === 'teacher'): ?>
        <a class="card" href="teacher_tareas.php">
          <div class="card-deco">📝</div>
          <div class="icon-orb">📋</div>
          <h2>Gestionar Módulos</h2>
          <p>Crea contenido educativo</p>
        </a>
      <?php endif; ?>

      <?php if (!empty($cursos)): ?>
        <?php foreach ($cursos as $curso): ?>
          <a class="card" href="curso.php?curso_id=<?= $curso['id'] ?>">
            <div class="card-deco">📚</div>
            <div class="icon-orb">🎓</div>
            <h2><?= htmlspecialchars($curso['nombre']) ?></h2>
            <p><?= htmlspecialchars($curso['descripcion'] ?: 'Explora este curso') ?></p>
            <?php if (!empty($curso['materia'])): ?>
              <small>📘 <?= htmlspecialchars($curso['materia']) ?></small><br>
            <?php endif; ?>
            <?php if (!empty($curso['grupo'])): ?>
              <small>👥 <?= htmlspecialchars($curso['grupo']) ?></small>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="card" style="grid-column: 1/-1; text-align: center;">
            <p>Aún no tienes cursos asignados. ¡Contacta a tu maestro! 🎈</p>
        </div>
      <?php endif; ?>

      <a class="card" href="literatura_ia.php">
        <div class="card-deco">📖</div>
        <div class="icon-orb">🤖</div>
        <h2>Literatura IA</h2>
        <p>Genera cuentos creativos con inteligencia artificial.</p>
      </a>
    </div>

    <footer>© 2026 Aulas Mágicas 🌟</footer>
</div>

<script src="assets/js/bubbles.js"></script>
</body>
</html>