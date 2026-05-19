<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/grok.php';

startSession();
requireLogin();

$db = getDB();

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$respuestaIA = '';

// ===================================
// GENERAR CUENTO CON GEMINI
// ===================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = clean($_POST['titulo'] ?? '');
    $prompt = clean($_POST['prompt'] ?? '');

    if (empty($titulo) || empty($prompt)) {
        $_SESSION['flash'] = ['type' => 'error', 'msg' => 'Completa todos los campos.'];
    } else {

        $gemini_key = 'AIzaSyApcJ0SH9p5M_0hrheDPPZehO_SfxfAsvQ';

        $mensaje = "Eres un escritor creativo especializado en cuentos para niños de primaria (6-11 años). 
Genera un cuento completo, divertido, educativo y con una moraleja clara.

Título: {$titulo}
Tema: {$prompt}";

        $data = [
            "contents" => [["parts" => [["text" => $mensaje]]]],
            "generationConfig" => [
                "temperature" => 0.85,
                "maxOutputTokens" => 1800
            ]
        ];

        // Probamos primero con gemini-1.5-flash (mejor cuota gratuita)
        $models = ['gemini-1.5-flash', 'gemini-2.0-flash'];
        $cuento = "Error: No se pudo generar el cuento.";

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $gemini_key;

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                    $cuento = $result['candidates'][0]['content']['parts'][0]['text'];
                    break;
                }
            } elseif ($httpCode == 429) {
                continue; // Intentar con el siguiente modelo
            }
        }

        $respuestaIA = $cuento;

        // Guardar en base de datos
        $stmt = $db->prepare("INSERT INTO cuentos_ia (usuario_id, titulo, prompt, cuento) VALUES (?, ?, ?, ?)");
        $stmt->execute([$userId, $titulo, $prompt, $cuento]);

        $_SESSION['flash'] = [
            'type' => ($httpCode === 200) ? 'success' : 'error',
            'msg'  => ($httpCode === 200) ? '¡Cuento generado con éxito!' : 'Error: Límite de Gemini alcanzado. Intenta más tarde.'
        ];
    }
}

// ===================================
// HISTORIAL
// ===================================
$stmt = $db->prepare("SELECT c.*, u.nombre FROM cuentos_ia c JOIN usuarios u ON c.usuario_id = u.id ORDER BY c.id DESC");
$stmt->execute();
$cuentos = $stmt->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Literatura IA</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
        .story-box { background:white; padding:25px; border-radius:20px; margin-top:25px; box-shadow:0 5px 20px rgba(0,0,0,.08); }
        textarea, input[type="text"] { width:100%; padding:15px; border-radius:15px; border:2px solid #ddd; font-size:16px; }
        textarea { min-height:160px; }
        .story { white-space:pre-wrap; line-height:1.8; margin-top:15px; }
        .btn-submit { width:100%; padding:18px; border:none; border-radius:18px; background:#34a853; color:white; font-size:18px; font-weight:bold; cursor:pointer; }
    </style>
</head>
<body>

<div class="wrapper">
    <h1>📖 Literatura con IA (Gemini)</h1>
    <a href="menu.php" class="btn">⬅ Volver al menú</a>

    <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?>">
            <?= htmlspecialchars($flash['msg']) ?>
        </div>
    <?php endif; ?>

    <div class="story-box">
        <h2>✨ Crear nuevo cuento</h2>
        <form method="POST">
            <input type="text" name="titulo" placeholder="Título del cuento" required><br><br>
            <textarea name="prompt" placeholder="Ejemplo: Un dragón que aprende matemáticas..." required></textarea><br><br>
            <button type="submit" class="btn-submit">🤖 Generar cuento</button>
        </form>
    </div>

    <?php if (!empty($respuestaIA)): ?>
    <div class="story-box">
        <h2>📚 Cuento generado</h2>
        <div class="story"><?= nl2br(htmlspecialchars($respuestaIA)) ?></div>
    </div>
    <?php endif; ?>

    <div class="story-box">
        <h2>🕘 Historial de cuentos</h2>
        <?php if (empty($cuentos)): ?>
            <p>No hay cuentos todavía.</p>
        <?php else: ?>
            <?php foreach ($cuentos as $c): ?>
            <div class="story-box">
                <h3><?= htmlspecialchars($c['titulo']) ?></h3>
                <small>👤 <?= htmlspecialchars($c['nombre'] ?? 'Usuario') ?> | <?= htmlspecialchars($c['prompt']) ?></small>
                <div class="story"><?= nl2br(htmlspecialchars(substr($c['cuento'], 0, 700))) ?>...</div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

</body>
</html>