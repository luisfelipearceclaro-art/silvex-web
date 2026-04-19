<?php
require_once __DIR__ . '/security.php';
silvex_bootstrap_security();

$base_path = "";
$page_title = html_entity_decode('Silvex | Iniciar Sesi&oacute;n', ENT_QUOTES, 'UTF-8');

// Simple mock for authentication logic
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';

    if (!silvex_verify_csrf_token($csrfToken)) {
        $error = html_entity_decode('La sesi&oacute;n expir&oacute;. Intenta de nuevo.', ENT_QUOTES, 'UTF-8');
    } else {
        $email = trim(strtolower($_POST['email'] ?? ''));
        $password = trim($_POST['password'] ?? '');

        require_once __DIR__ . '/data_helper.php';

        // 1. Check Admin
        if ($email === 'admin@silvex.com' && $password === 'admin123') {
            session_regenerate_id(true);
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_email'] = $email;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['logout_token'] = bin2hex(random_bytes(32));
            silvex_log_activity('Admin', 'admin@silvex.com', 'Login exitoso (Admin)');
            header('Location: admin/index.php');
            exit;
        } 
        
        // 2. Check Client (Dynamic)
        $client = DataHelper::findOneBy('clients.json', 'login_email', $email);
        if ($client && $client['password'] === $password) {
            session_regenerate_id(true);
            $_SESSION['user_role'] = 'cliente';
            $_SESSION['user_email'] = $client['login_email'];
            $_SESSION['client_id'] = $client['id'];
            $_SESSION['client_name'] = $client['name'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['logout_token'] = bin2hex(random_bytes(32));
            silvex_log_activity($client['name'], $client['login_email'], 'Login exitoso (Cliente)');
            header('Location: clientes/index.php');
            exit;
        }

        $error = html_entity_decode('Credenciales inv&aacute;lidas.', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?></title>
  <link rel="icon" type="image/png" href="assets/logo/silvex-logo.png">
  <link rel="preload" href="./assets/videos/login-outer-bg.mp4" as="video" type="video/mp4">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="login.css">
</head>
<body class="login-page">
    <video class="login-bg-video" autoplay muted loop playsinline preload="auto" poster="./assets/textures/login-outer-bg.jpg">
        <source src="./assets/videos/login-outer-bg.mp4" type="video/mp4">
    </video>
    <div class="login-bg-overlay" aria-hidden="true"></div>
    <div class="login-container">
        <div class="login-left">
            <h2>Nice to see you again</h2>
            <h1>WELCOME BACK</h1>
            <p>Accede a tu panel estrat&eacute;gico de Silvex Estudio para gestionar tus resultados.</p>
        </div>
        <div class="login-right">
            <h2>Login Account</h2>
            <?php if ($error): ?>
                <p style="color: red; font-size: 0.8rem;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(silvex_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="email" placeholder="email@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" required>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <label style="font-size: 0.8rem; color: #666; display: flex; align-items: center;">
                        <input type="checkbox" style="margin-right: 5px;"> Keep me signed in
                    </label>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
            </form>
            <div class="login-footer">
                <span>&iquest;No tienes cuenta? <a href="index.php">Ir al inicio</a></span>
            </div>
        </div>
    </div>
</body>
</html>
