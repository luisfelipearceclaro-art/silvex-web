<?php
session_start();
$base_path = "";
$page_title = "Silvex | Iniciar Sesión";

// Simple mock for authentication logic
$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Dummy logic: admin@silvex.com / client@silvex.com
    if ($email === 'admin@silvex.com' && $password === 'admin123') {
        $_SESSION['user_role'] = 'admin';
        header('Location: admin/index.php');
        exit;
    } elseif ($email === 'cliente@silvex.com' && $password === 'cliente123') {
        $_SESSION['user_role'] = 'cliente';
        header('Location: clientes/index.php');
        exit;
    } else {
        $error = "Credenciales inválidas.";
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
            <p>Accede a tu panel estratégico de Silvex Estudio para gestionar tus resultados.</p>
        </div>
        <div class="login-right">
            <h2>Login Account</h2>
            <?php if ($error): ?>
                <p style="color: red; font-size: 0.8rem;"><?php echo $error; ?></p>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email ID</label>
                    <input type="email" name="email" placeholder="email@ejemplo.com" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <label style="font-size: 0.8rem; color: #666; display: flex; align-items: center;">
                        <input type="checkbox" style="margin-right: 5px;"> Keep me signed in
                    </label>
                </div>
                <button type="submit" class="login-btn">LOGIN</button>
            </form>
            <div class="login-footer">
                <span>¿No tienes cuenta? <a href="index.php">Ir al inicio</a></span>
            </div>
        </div>
    </div>
</body>
</html>
