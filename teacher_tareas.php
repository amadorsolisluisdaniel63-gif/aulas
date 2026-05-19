<?php
require_once __DIR__ . '/config/config.php';

startSession();
requireRole('teacher');

$db = getDB();

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $modulo_id      = $_POST['modulo_id'] ?? null;
    $titulo         = clean($_POST['titulo'] ?? '');
    $tipo           = clean($_POST['tipo'] ?? 'opcion_multiple');
    $dificultad     = clean($_POST['dificultad'] ?? 'facil');
    $puntos         = intval($_POST['puntos'] ?? 10);
    $tiempo_limite  = intval($_POST['tiempo_limite'] ?? 0);
    $instrucciones  = clean($_POST['instrucciones'] ?? '');

    if (!$modulo_id || empty($titulo)) {

        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Completa todos los campos.'
        ];

        header('Location: teacher_tareas.php');
        exit;
    }

    try {

        $stmt = $db->prepare(" 
            INSERT INTO actividades
            (
                modulo_id,
                tipo,
                titulo,
                instrucciones,
                dificultad,
                puntos,
                tiempo_limite
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $modulo_id,
            $tipo,
            $titulo,
            $instrucciones,
            $dificultad,
            $puntos,
            $tiempo_limite
        ]);

        $actividad_id = $db->lastInsertId();

     
        if (isset($_POST['preguntas'])) {

            foreach ($_POST['preguntas'] as $index => $preguntaData) {

                $pregunta = clean($preguntaData['texto'] ?? '');
                $respuestaCorrecta = clean($preguntaData['correcta'] ?? '');
                $explicacion = clean($preguntaData['explicacion'] ?? '');

                if (empty($pregunta)) {
                    continue;
                }

                $stmtPregunta = $db->prepare(" 
                    INSERT INTO preguntas
                    (
                        actividad_id,
                        pregunta,
                        respuesta_correcta,
                        explicacion
                    )
                    VALUES (?, ?, ?, ?)
                ");

                $stmtPregunta->execute([
                    $actividad_id,
                    $pregunta,
                    $respuestaCorrecta,
                    $explicacion
                ]);

                $pregunta_id = $db->lastInsertId();

                if (isset($preguntaData['opciones'])) {

                    foreach ($preguntaData['opciones'] as $opcionTexto) {

                        if (empty(trim($opcionTexto))) {
                            continue;
                        }

                        $esCorrecta = ($opcionTexto === $respuestaCorrecta)
                            ? 1
                            : 0;

                        $stmtOpcion = $db->prepare(" 
                            INSERT INTO opciones
                            (
                                pregunta_id,
                                texto,
                                es_correcta
                            )
                            VALUES (?, ?, ?)
                        ");

                        $stmtOpcion->execute([
                            $pregunta_id,
                            $opcionTexto,
                            $esCorrecta
                        ]);
                    }
                }
            }
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'msg'  => 'Actividad creada correctamente 🎉'
        ];

    } catch (PDOException $e) {

        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Error al guardar la actividad.'
        ];
    }

    header('Location: teacher_tareas.php');
    exit;
}


$cursos = $db->query(" 
    SELECT *
    FROM cursos
    ORDER BY nombre
")->fetchAll();

$modulos = $db->query(" 
    SELECT m.*, c.nombre AS curso
    FROM modulos m
    JOIN cursos c ON m.curso_id = c.id
    ORDER BY c.nombre, m.titulo
")->fetchAll();

$actividades = $db->query(" 
    SELECT
        a.*,
        m.titulo AS modulo,
        c.nombre AS curso
    FROM actividades a
    JOIN modulos m ON a.modulo_id = m.id
    JOIN cursos c ON m.curso_id = c.id
    ORDER BY a.id DESC
")->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Profesor</title>
<link rel="stylesheet" href="assets/css/styles.css">

<style>

.dashboard{
    display:grid;
    grid-template-columns:280px 1fr;
    gap:25px;
}

.sidebar{
    background:white;
    border-radius:25px;
    padding:25px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
    height:fit-content;
}

.sidebar h2{
    margin-bottom:25px;
}

.sidebar a{
    display:block;
    padding:14px;
    margin-bottom:10px;
    border-radius:15px;
    text-decoration:none;
    color:#333;
    font-weight:700;
    background:#f5f5f5;
    transition:.3s;
}

.sidebar a:hover{
    transform:translateX(5px);
    background:#6C63FF;
    color:white;
}

.panel{
    background:white;
    border-radius:25px;
    padding:30px;
    box-shadow:0 5px 20px rgba(0,0,0,.08);
}

.creator-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px;
}

.input-group{
    margin-bottom:20px;
}

.input-group label{
    display:block;
    margin-bottom:8px;
    font-weight:800;
}

.input-group input,
.input-group select,
.input-group textarea{
    width:100%;
    padding:14px;
    border-radius:15px;
    border:2px solid #eee;
    font-size:1rem;
}

.question-card{
    background:#f9f9ff;
    border:2px solid #e8e8ff;
    border-radius:20px;
    padding:20px;
    margin-top:20px;
}

.option-input{
    margin-bottom:10px;
}

.btn-add{
    background:#00C896;
    color:white;
    border:none;
    padding:14px 20px;
    border-radius:15px;
    cursor:pointer;
    font-weight:800;
}

.btn-submit{
    background:#6C63FF;
    color:white;
    border:none;
    padding:16px 25px;
    border-radius:15px;
    font-size:1rem;
    cursor:pointer;
    font-weight:900;
}

.activity-card{
    background:#fafafa;
    padding:20px;
    border-radius:20px;
    margin-bottom:20px;
    border-left:8px solid #6C63FF;
}

.badge{
    display:inline-block;
    background:#6C63FF;
    color:white;
    padding:5px 12px;
    border-radius:20px;
    font-size:.8rem;
    margin-right:5px;
}

.preview-box{
    background:#fff8e7;
    border:2px dashed #ffbf47;
    padding:20px;
    border-radius:20px;
    margin-top:25px;
}

@media(max-width:900px){

.dashboard{
    grid-template-columns:1fr;
}

.creator-grid{
    grid-template-columns:1fr;
}

}

</style>
</head>
<body>

<div class="wrapper">

<h1>👩‍🏫 Dashboard del Profesor</h1>

<a href="menu.php" class="btn">
⬅ Volver al menú
</a>

<br><br>

<?php if ($flash): ?>

<div class="alert alert-<?= $flash['type'] ?>">
<?= htmlspecialchars($flash['msg']) ?>
</div>

<?php endif; ?>

<div class="dashboard">

<div class="sidebar">

<h2>📚 Panel</h2>

<a href="#crear">➕ Crear Actividad</a>
<a href="#preguntas">❓ Preguntas</a>
<a href="#preview">👀 Vista previa</a>
<a href="#actividades">📖 Actividades</a>
<a href="#calificaciones">📊 Calificaciones</a>
<a href="#boletas">📄 Boletas</a>

</div>

<div class="panel">

<h2 id="crear">🎮 Constructor Visual de Actividades</h2>

<form method="POST" id="activityForm">

<div class="creator-grid">

<div class="input-group">
<label>📘 Curso</label>
<select id="cursoSelect">
<option value="">Selecciona un curso</option>

<?php foreach ($cursos as $c): ?>
<option value="<?= $c['id'] ?>">
<?= htmlspecialchars($c['nombre']) ?>
</option>
<?php endforeach; ?>

</select>
</div>

<div class="input-group">
<label>🧩 Módulo</label>
<select name="modulo_id" required>
<option value="">Selecciona módulo</option>

<?php foreach ($modulos as $m): ?>
<option value="<?= $m['id'] ?>">
<?= htmlspecialchars($m['curso']) ?> →
<?= htmlspecialchars($m['titulo']) ?>
</option>
<?php endforeach; ?>

</select>
</div>

<div class="input-group">
<label>📝 Título</label>
<input type="text" name="titulo" required>
</div>

<div class="input-group">
<label>🎯 Tipo de actividad</label>
<select name="tipo" id="tipoActividad">
<option value="opcion_multiple">🎮 Opción múltiple</option>
<option value="verdadero_falso">✅ Verdadero/Falso</option>
<option value="completar">✏️ Completar</option>
<option value="lectura">📖 Lectura</option>
<option value="abierta">🧠 Abierta</option>
<option value="juego">🏆 Juego</option>
</select>
</div>

<div class="input-group">
<label>⭐ Dificultad</label>
<select name="dificultad">
<option value="facil">🟢 Fácil</option>
<option value="medio">🟡 Medio</option>
<option value="dificil">🔴 Difícil</option>
</select>
</div>

<div class="input-group">
<label>🏅 Puntos</label>
<input type="number" name="puntos" value="10">
</div>

<div class="input-group">
<label>⏰ Tiempo límite (minutos)</label>
<input type="number" name="tiempo_limite" value="0">
</div>

</div>

<div class="input-group">
<label>📋 Instrucciones</label>
<textarea name="instrucciones"></textarea>
</div>

<h2 id="preguntas">❓ Preguntas</h2>

<div id="questionsContainer"></div>

<br>

<button type="button" class="btn-add" onclick="addQuestion()">
➕ Agregar pregunta
</button>

<br><br>

<div class="preview-box" id="preview">

<h2>👀 Vista previa</h2>

<p>
Aquí el profesor puede visualizar cómo verá el alumno la actividad.
</p>

</div>

<br>

<button type="submit" class="btn-submit">
🚀 Publicar actividad
</button>

</form>

<br><br>

<h2 id="actividades">📚 Actividades publicadas</h2>

<?php if (empty($actividades)): ?>

<p>No hay actividades todavía.</p>

<?php else: ?>

<?php foreach ($actividades as $a): ?>

<div class="activity-card">

<h3>
<?= htmlspecialchars($a['titulo']) ?>
</h3>

<p>
📘 <?= htmlspecialchars($a['curso']) ?>
→
<?= htmlspecialchars($a['modulo']) ?>
</p>

<br>

<span class="badge">
<?= htmlspecialchars($a['tipo']) ?>
</span>

<span class="badge">
⭐ <?= $a['puntos'] ?> pts
</span>

<span class="badge">
⏰ <?= $a['tiempo_limite'] ?> min
</span>

</div>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>

</div>

<script>

let questionIndex = 0;

function addQuestion(){

    const container = document.getElementById('questionsContainer');

    const html = `

    <div class="question-card">

        <h3>❓ Pregunta ${questionIndex + 1}</h3>

        <div class="input-group">
            <label>Pregunta</label>
            <textarea
            name="preguntas[${questionIndex}][texto]"
            required></textarea>
        </div>

        <div class="input-group">
            <label>Opción A</label>
            <input
            type="text"
            name="preguntas[${questionIndex}][opciones][]">
        </div>

        <div class="input-group">
            <label>Opción B</label>
            <input
            type="text"
            name="preguntas[${questionIndex}][opciones][]">
        </div>

        <div class="input-group">
            <label>Opción C</label>
            <input
            type="text"
            name="preguntas[${questionIndex}][opciones][]">
        </div>

        <div class="input-group">
            <label>Opción D</label>
            <input
            type="text"
            name="preguntas[${questionIndex}][opciones][]">
        </div>

        <div class="input-group">
            <label>✅ Respuesta correcta</label>
            <input
            type="text"
            name="preguntas[${questionIndex}][correcta]"
            required>
        </div>

        <div class="input-group">
            <label>💡 Explicación</label>
            <textarea
            name="preguntas[${questionIndex}][explicacion]"></textarea>
        </div>

    </div>

    `;

    container.insertAdjacentHTML('beforeend', html);

    questionIndex++;
}

addQuestion();

</script>

</body>
</html>