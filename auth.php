<?php
require_once __DIR__ . '/config/config.php';

startSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($action === 'login') {

    $email    = isset($_POST['email'])    ? clean($_POST['email'])    : '';
    $password = isset($_POST['password']) ? $_POST['password']        : '';

    if (empty($email) || empty($password)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Por favor completa todos los campos.',
            'tab'  => 'login',
        ];
        header('Location: index.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'El correo electrónico no es válido.',
            'tab'  => 'login',
        ];
        header('Location: index.php');
        exit;
    }

    try {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg'  => 'Correo o contraseña incorrectos. Intenta de nuevo.',
                'tab'  => 'login',
            ];
            header('Location: index.php');
            exit;
        }

       $db->prepare('UPDATE usuarios SET last_login = NOW() WHERE id = ?') ->execute([$user['id']]);

        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_name'] = $user['nombre'] . ' ' . $user['apellido'];
        $_SESSION['user_rol']  = $user['rol'];
        $_SESSION['user_nivel']= isset($user['nivel']) ? $user['nivel'] : null;
        $_SESSION['user_email']= $user['email'];

        header('Location: menu.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Error del sistema. Intenta más tarde.',
            'tab'  => 'login',
        ];
        header('Location: index.php');
        exit;
    }
}

if ($action === 'register') {

    $nombre    = isset($_POST['nombre'])   ? clean($_POST['nombre'])   : '';
    $apellido  = isset($_POST['apellido']) ? clean($_POST['apellido']) : '';
    $email     = isset($_POST['email'])    ? clean($_POST['email'])    : '';
    $password  = isset($_POST['password']) ? $_POST['password']        : '';
    $confirm   = isset($_POST['confirm'])  ? $_POST['confirm']         : '';
    $rol       = isset($_POST['rol'])      ? clean($_POST['rol'])      : '';
    $nivel     = isset($_POST['nivel'])    ? clean($_POST['nivel'])    : '';

    $errores = [];

    if (empty($nombre))   $errores[] = 'El nombre es obligatorio.';
    if (empty($apellido)) $errores[] = 'El apellido es obligatorio.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL))
                          $errores[] = 'Correo electrónico inválido.';
    if (strlen($password) < 6)
                          $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $confirm)
                          $errores[] = 'Las contraseñas no coinciden.';
    if (!in_array($rol, ['admin', 'teacher', 'student'], true))
                          $errores[] = 'Selecciona un tipo de usuario válido.';
    if ($rol === 'student' && !in_array($nivel, ['low', 'high'], true))
                          $errores[] = 'Selecciona el nivel (Primaria Baja o Alta).';

    if (!empty($errores)) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => implode(' · ', $errores),
            'tab'  => 'register',
        ];
        header('Location: index.php');
        exit;
    }

    try {
        $db = getDB();

        $check = $db->prepare('SELECT id FROM usuarios WHERE email = ? LIMIT 1');
        $check->execute([$email]);
        if ($check->fetch()) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'msg'  => 'Ese correo ya está registrado. ¿Quieres iniciar sesión?',
                'tab'  => 'register',
            ];
            header('Location: index.php');
            exit;
        }

        $hash      = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $nivelVal  = ($rol === 'student') ? $nivel : null;

        $ins = $db->prepare('INSERT INTO usuarios (nombre, apellido, email, password, rol, nivel) VALUES (?, ?, ?, ?, ?, ?)');
        $ins->execute([$nombre, $apellido, $email, $hash, $rol, $nivelVal]);
        $newId = $db->lastInsertId();

        session_regenerate_id(true);
        $_SESSION['user_id']    = $newId;
        $_SESSION['user_name']  = $nombre . ' ' . $apellido;
        $_SESSION['user_rol']   = $rol;
        $_SESSION['user_nivel'] = $nivelVal;
        $_SESSION['user_email'] = $email;
        $_SESSION['flash']      = [
            'type' => 'success',
            'msg'  => 'Bienvenido/a ' . htmlspecialchars($nombre) . '. Tu cuenta fue creada.',
        ];

        header('Location: menu.php');
        exit;

    } catch (PDOException $e) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'msg'  => 'Error al registrar. Intenta más tarde.',
            'tab'  => 'register',
        ];
        header('Location: index.php');
        exit;
    }
}

header('Location: index.php');
exit;