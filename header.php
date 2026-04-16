<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($page_title)) {
    $page_title = "Silvex Estudio";
}
if (!isset($current_page)) {
    $current_page = "";
}
if (!isset($base_path)) {
    $base_path = "";
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $page_title; ?></title>
  <link rel="icon" type="image/png" href="<?php echo $base_path; ?>assets/logo/silvex-logo.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo $base_path; ?>styles.css?v=29">
  <link rel="stylesheet" href="<?php echo $base_path; ?>mobile.css">
  <?php echo isset($extra_head) ? $extra_head : ''; ?>
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>">
  <main class="hero <?php echo isset($hero_class) ? $hero_class : ''; ?>">
    <header class="topbar">
      <a class="brand" href="<?php echo $base_path; ?>index.php" aria-label="Silvex Estudio inicio">
        <img class="brand__logo" src="<?php echo $base_path; ?>assets/logo/silvex-logo.png" alt="Silvex Estudio">
      </a>

      <nav class="nav" aria-label="Principal">
        <a class="<?php echo ($current_page == 'index') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>index.php">Inicio</a>
        <a class="<?php echo ($current_page == 'nosotros') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>nosotros.php">Nosotros</a>
        <a class="<?php echo ($current_page == 'servicios') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>servicios.php">Servicios</a>
        <a class="<?php echo ($current_page == 'portafolio') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>portafolio.php">Portafolio</a>
        <a class="<?php echo ($current_page == 'contactanos') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>contactanos.php">Contáctanos</a>
        <?php if (isset($_SESSION['user_role'])): ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a class="<?php echo ($current_page == 'admin') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>admin/index.php">Admin</a>
            <?php else: ?>
                <a class="<?php echo ($current_page == 'clientes') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>clientes/index.php">Clientes</a>
            <?php endif; ?>
            <a href="<?php echo $base_path; ?>logout.php" style="color: #ff4b2b;">Cerrar Sesión</a>
        <?php else: ?>
            <a href="<?php echo $base_path; ?>login.php">Ingresar</a>
        <?php endif; ?>
      </nav>
    </header>
