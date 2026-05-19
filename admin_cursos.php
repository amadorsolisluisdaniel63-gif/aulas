<?php
require_once __DIR__ . '/config/config.php';

startSession();
requireRole('admin');

$db = getDB();

/* =========================
   CREAR / ACTUALIZAR
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id          = $_POST['id'] ?? null;
    $nombre      = clean($_POST['nombre'] ?? '');
    $descripcion = clean($_POST['descripcion'] ?? '');
    $materia     = clean($_POST['materia'] ?? '');
    $grupo       = clean($_POST['grupo'] ?? '');
    $profesor_id = $_POST['profesor_id'] ?? null;

    if (empty($nombre)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'El nombre es obligatorio.'
        ];
    } else {
        try {

            if ($id) {
                // EDITAR
                $stmt = $db->prepare("
                    UPDATE cursos 
                    SET nombre=?, descripcion=?, materia=?, grupo=?, profesor_id=? 
                    WHERE id=?
                ");
                $stmt->execute([$nombre, $descripcion, $materia, $grupo, $profesor_id, $id]);

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'msg'  => 'Curso actualizado.'
                ];

            } else {
                // CREAR
                $stmt = $db->prepare("
                    INSERT INTO cursos (nombre, descripcion, materia, grupo, profesor_id) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$nombre, $descripcion, $materia, $grupo, $profesor_id]);

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'msg'  => 'Curso creado.'
                ];
            }

        } catch (PDOException $e) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg'  => 'Error en la operación.'
            ];
        }
    }

    header("Location: admin_cursos.php");
    exit;
}

/* =========================
   ELIMINAR
========================= */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = $db->prepare("DELETE FROM cursos WHERE id=?");
    $stmt->execute([$id]);

    $_SESSION['flash'] = [
        'type' => 'success',
        'msg'  => 'Curso eliminado.'
    ];

    header("Location: admin_cursos.php");
    exit;
}

/* =========================
   OBTENER CURSO (EDITAR)
========================= */
$editCurso = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM cursos WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $editCurso = $stmt->fetch();
}

/* =========================
   LISTAS
========================= */
$cursos = $db->query("SELECT * FROM cursos ORDER BY id DESC")->fetchAll();

$profesores = $db->query("SELECT id, nombre FROM usuarios WHERE rol='teacher'")
                 ->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin Cursos</title>
<link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div class="wrapper">

<h1>🛡️ Panel de Cursos</h1>
<a href="menu.php">⬅ Volver</a>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?>">
  <?= htmlspecialchars($flash['msg']) ?>
</div>
<?php endif; ?>

<!-- =========================
     FORMULARIO
========================= -->
<h2><?= $editCurso ? '✏️ Editar Curso' : '➕ Crear Curso' ?></h2>

<form method="POST">

<input type="hidden" name="id" value="<?= $editCurso['id'] ?? '' ?>">

<input type="text" name="nombre" placeholder="Nombre"
value="<?= $editCurso['nombre'] ?? '' ?>" required><br><br>

<textarea name="descripcion" placeholder="Descripción"><?= $editCurso['descripcion'] ?? '' ?></textarea><br><br>

<input type="text" name="materia" placeholder="Materia"
value="<?= $editCurso['materia'] ?? '' ?>"><br><br>

<input type="text" name="grupo" placeholder="Grupo"
value="<?= $editCurso['grupo'] ?? '' ?>"><br><br>

<select name="profesor_id">
<option value="">-- Asignar profesor --</option>
<?php foreach ($profesores as $p): ?>
<option value="<?= $p['id'] ?>"
<?= (isset($editCurso['profesor_id']) && $editCurso['profesor_id']==$p['id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($p['nombre']) ?>
</option>
<?php endforeach; ?>
</select><br><br>

<button type="submit">
<?= $editCurso ? 'Actualizar' : 'Crear' ?>
</button>

</form>

<!-- =========================
     TABLA
========================= -->
<h2>📚 Cursos</h2>

<table border="1" cellpadding="10">
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Materia</th>
<th>Grupo</th>
<th>Acciones</th>
</tr>

<?php foreach ($cursos as $c): ?>
<tr>
<td><?= $c['id'] ?></td>
<td><?= htmlspecialchars($c['nombre']) ?></td>
<td><?= htmlspecialchars($c['materia']) ?></td>
<td><?= htmlspecialchars($c['grupo']) ?></td>

<td>
<a href="?edit=<?= $c['id'] ?>">✏️ Editar</a> |
<a href="?delete=<?= $c['id'] ?>"
onclick="return confirm('¿Eliminar?')">❌</a>
</td>
</tr>
<?php endforeach; ?>

</table>

</div>

</body>
</html>