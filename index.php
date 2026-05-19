<?php


require_once __DIR__ . '/config/config.php';
startSession();
redirectIfLoggedIn();  

$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);

$byeBye = isset($_GET['bye']);
$sessionExpired = (isset($_GET['error']) && $_GET['error'] === 'session_expired');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Aulas Mágicas — Acceso</title>
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;700;800;900&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="assets/css/styles.css"/>
</head>
<body>

<?php include __DIR__ . '/includes/decorations.php'; ?>

<div class="auth-wrapper">
  <div class="auth-box">

    <div style="text-align:center;margin-bottom:8px;">
      <div class="logo-badge" style="margin:0 auto 16px;">✨ Plataforma Educativa</div>
      <div class="auth-title">
        <span class="star-float" style="--fd:2.1s;--fs:1.4rem;">🌟</span>
        Aulas Mágicas
        <span class="star-float" style="--fd:2.8s;--fs:1.1rem;">✨</span>
      </div>
      <p class="auth-sub">¡Aprende, juega y descubre el mundo! 🚀</p>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type'] ?>">
        <?= htmlspecialchars($flash['msg']) ?>
      </div>
      <input type="hidden" id="active-tab-hint" value="<?= htmlspecialchars( isset($flash['tab']) ? $flash['tab'] : 'login' ) ?>"/>
    <?php endif; ?>

    <?php if ($byeBye): ?>
      <div class="alert alert-info">👋 Has cerrado sesión correctamente. ¡Hasta pronto!</div>
    <?php endif; ?>

    <?php if ($sessionExpired): ?>
      <div class="alert alert-error">⏰ Tu sesión expiró. Por favor inicia sesión de nuevo.</div>
    <?php endif; ?>

    <div class="tabs">
      <button class="tab-btn active" data-tab="login">🔑 Iniciar Sesión</button>
      <button class="tab-btn"        data-tab="register">✏️ Registrarse</button>
    </div>

    <div id="login-section" class="form-section active">
      <form method="POST" action="auth.php" novalidate>
        <input type="hidden" name="action" value="login"/>

        <div class="form-group">
          <label for="login-email">📧 Correo Electrónico</label>
          <div class="input-icon-wrap">
            <span class="icon">📧</span>
            <input type="email" id="login-email" name="email" placeholder="tu@correo.com" autocomplete="email" required/>
          </div>
        </div>

        <div class="form-group">
          <label for="login-pw">Contraseña</label>
          <div class="input-icon-wrap" style="display:flex;gap:8px;align-items:center;">
            <span class="icon">🔒</span>
            <input type="password" id="login-pw" name="password" placeholder="Tu contraseña" autocomplete="current-password" style="flex:1;" required/>
            <button type="button" class="toggle-pw" data-target="login-pw" style="background:none;border:none;cursor:pointer;font-size:1.2rem;padding:0 4px;">👁️</button>
          </div>
        </div>

        <button type="submit" class="btn-submit"> <span>🚀</span> ¡Entrar al Aula! </button>
      </form>

      <div class="divider">o</div>
      <p style="text-align:center;font-size:.82rem;color:#9A7050;font-weight:800;">
        ¿Eres nuevo? <button class="tab-btn" data-tab="register"
          style="background:none;border:none;color:#E06020;font-weight:900;cursor:pointer;font-size:.82rem;font-family:inherit;">
          Regístrate aquí </button>
      </p>
    </div>

    <div id="register-section" class="form-section">
      <form method="POST" action="auth.php" novalidate>
        <input type="hidden" name="action" value="register"/>
        <input type="hidden" name="rol"    id="reg-role" value=""/>
        <input type="hidden" name="nivel"  id="reg-level" value=""/>

      
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
          <div class="form-group" style="margin-bottom:0;">
            <label for="reg-nombre">👤 Nombre</label>
            <input type="text" id="reg-nombre" name="nombre" placeholder="Ana" required/>
          </div>
          <div class="form-group" style="margin-bottom:0;">
            <label for="reg-apellido">👤 Apellido</label>
            <input type="text" id="reg-apellido" name="apellido" placeholder="García" required/>
          </div>
        </div>

        <div class="form-group" style="margin-top:18px;">
          <label for="reg-email">📧 Correo Electrónico</label>
          <div class="input-icon-wrap">
            <span class="icon">📧</span>
            <input type="email" id="reg-email" name="email" placeholder="tu@correo.com" autocomplete="off" required/>
          </div>
        </div>

        <div class="form-group">
          <label for="reg-pw">🔒 Contraseña <span style="color:#bbb;font-size:.75rem;">(mínimo 6 caracteres)</span></label>
          <div class="input-icon-wrap" style="display:flex;gap:8px;align-items:center;">
            <span class="icon">🔒</span>
            <input type="password" id="reg-pw" name="password" placeholder="Crea tu contraseña" style="flex:1;" required/>
            <button type="button" class="toggle-pw" data-target="reg-pw" style="background:none;border:none;cursor:pointer;font-size:1.2rem;padding:0 4px;">👁️</button>
          </div>
        </div>

        <div class="form-group">
          <label for="reg-confirm">🔒 Confirmar Contraseña</label>
          <div class="input-icon-wrap" style="display:flex;gap:8px;align-items:center;">
            <span class="icon">✅</span>
            <input type="password" id="reg-confirm" name="confirm" placeholder="Repite tu contraseña" style="flex:1;" required/>
            <button type="button" class="toggle-pw" data-target="reg-confirm" style="background:none;border:none;cursor:pointer;font-size:1.2rem;padding:0 4px;">👁️</button>
          </div>
        </div>

        <div class="form-group">
          <label>Tipo de Usuario</label>
          <div class="role-selector">
            <div class="role-card" data-role="admin">
              <span class="role-icon">🛡️</span>
              <span class="role-label">Admin</span>
            </div>
            <div class="role-card" data-role="teacher">
              <span class="role-icon">👩‍🏫</span>
              <span class="role-label">Maestro</span>
            </div>
            <div class="role-card" data-role="student">
              <span class="role-icon">🎒</span>
              <span class="role-label">Alumno</span>
            </div>
          </div>
        </div>

        
        <div class="form-group" id="level-group" style="display:none;">
          <label>📚 Nivel de Primaria</label>
          <div class="level-selector">
            <div class="level-card" data-level="low">
              <span class="level-icon">🌱</span>
              <span class="level-label">Primaria Baja</span>
              <span class="level-desc">1°, 2° y 3° grado</span>
            </div>
            <div class="level-card" data-level="high">
              <span class="level-icon">🌟</span>
              <span class="level-label">Primaria Alta</span>
              <span class="level-desc">4°, 5° y 6° grado</span>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-submit" style="background:var(--purple);box-shadow:0 5px 0 #4020A0;">
          <span>✨</span> ¡Crear mi Cuenta!
        </button>
      </form>

      <div class="divider">o</div>
      <p style="text-align:center;font-size:.82rem;color:#9A7050;font-weight:800;">
        ¿Ya tienes cuenta? <button class="tab-btn" data-tab="login"
          style="background:none;border:none;color:#E04030;font-weight:900;cursor:pointer;font-size:.82rem;font-family:inherit;">
          Inicia sesión 🔑</button>
      </p>
    </div>

  </div>
</div>

<script src="assets/js/bubbles.js"></script>
<script src="assets/js/auth.js"></script>
</body>
</html>
