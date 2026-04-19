<?php
require_once __DIR__ . '/security.php';
silvex_bootstrap_security();
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
  <link rel="stylesheet" href="<?php echo $base_path; ?>styles.css?v=40">
  <link rel="stylesheet" href="<?php echo $base_path; ?>mobile.css">
  <link rel="stylesheet" href="<?php echo $base_path; ?>assets/css/ui-fixes.css?v=1">
  <?php echo isset($extra_head) ? $extra_head : ''; ?>
  <style>
    /* ── LOADER ─────────────────────────────────── */
    #silvex-loader {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(5, 10, 35, 0.88);
      z-index: 99999;
      align-items: center;
      justify-content: center;
    }
    #silvex-loader.is-active { display: flex; }

    .silvex-loader__ring {
      position: relative;
      width: 64px;
      height: 64px;
    }
    .silvex-loader__ring span {
      position: absolute;
      inset: 0;
      border-radius: 50%;
      border: 3px solid transparent;
      animation: loaderSpin 1.2s linear infinite;
    }
    .silvex-loader__ring span:nth-child(1) {
      border-top-color: #2ec8ef;
    }
    .silvex-loader__ring span:nth-child(2) {
      width: 46px; height: 46px;
      top: 9px; left: 9px;
      border-top-color: rgba(46,200,239,0.55);
      animation-direction: reverse;
      animation-delay: -0.3s;
    }
    .silvex-loader__ring span:nth-child(3) {
      width: 28px; height: 28px;
      top: 18px; left: 18px;
      border-top-color: rgba(46,200,239,0.25);
      animation-delay: 0.15s;
    }
    @keyframes loaderSpin { to { transform: rotate(360deg); } }

    /* ── LIGHT MODE ─────────────────────────────── */
    html.theme-light body,
    html.theme-light .hero { background: linear-gradient(135deg,#dce8ff,#eef2ff,#d5e3fb) !important; color: #0a0e27 !important; }
    html.theme-light .topbar { background: transparent !important; backdrop-filter: none !important; border-bottom: none !important; box-shadow: none !important; }
    /* Todos los textos en oscuro */
    html.theme-light *:not(script):not(style):not(.silvex-loader__ring):not(.silvex-loader__ring span) { color: #0a0e27 !important; }
    /* Cards y glassmorphism — mantener fondo blanco */
    html.theme-light .plan-card,
    html.theme-light .premium-glass { background: rgba(255,255,255,0.88) !important; border: 1px solid rgba(10,14,39,0.1) !important; box-shadow: 0 4px 20px rgba(0,0,0,0.07) !important; }
    /* Tabla */
    html.theme-light thead { background: rgba(0,0,0,0.04) !important; }
    html.theme-light tr { border-color: rgba(10,14,39,0.08) !important; }
    /* Inputs */
    html.theme-light input,
    html.theme-light select,
    html.theme-light textarea { background: rgba(255,255,255,0.85) !important; border-color: rgba(10,14,39,0.18) !important; }
    /* Logo */
    html.theme-light .brand__logo { filter: invert(1); transition: filter 0.3s ease; }
    /* Loader en modo claro */
    html.theme-light #silvex-loader { background: rgba(210,228,255,0.92) !important; }
    html.theme-light #silvex-loader .silvex-loader__ring span { color: transparent !important; }
    html.theme-light #silvex-loader .silvex-loader__ring span:nth-child(1) { border-top-color: #1a6fd4 !important; }

  </style>
  <script>
    // Failsafe to ensure page visibility if script fails or from cache
    (function() {
      const showPage = () => document.documentElement.classList.add('page-ready');
      window.addEventListener('pageshow', (e) => {
        if (e.persisted) showPage();
      });
      // Fallback if transitions.js doesn't run in 2 seconds
      setTimeout(showPage, 2000);
    })();
  </script>
</head>
<body class="<?php echo isset($body_class) ? $body_class : ''; ?>" data-base-path="<?php echo htmlspecialchars($base_path, ENT_QUOTES, 'UTF-8'); ?>">

  <!-- Global Page Loader -->
  <div id="silvex-loader" aria-hidden="true">
    <div class="silvex-loader__ring">
      <span></span><span></span><span></span>
    </div>
  </div>

  <main class="hero <?php echo isset($hero_class) ? $hero_class : ''; ?>">
    <header class="topbar">
      <a class="brand" href="<?php echo $base_path; ?>index.php" aria-label="Silvex Estudio inicio">
        <img class="brand__logo" src="<?php echo $base_path; ?>assets/logo/silvex-logo.png" alt="Silvex Estudio">
      </a>

      <nav class="nav" aria-label="Principal">
        <?php if ($current_page !== 'admin' && $current_page !== 'clientes'): ?>
            <a class="<?php echo ($current_page == 'index') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>index.php">Inicio</a>
            <a class="<?php echo ($current_page == 'nosotros') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>nosotros.php">Nosotros</a>
            <a class="<?php echo ($current_page == 'servicios') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>servicios.php">Servicios</a>
            <a class="<?php echo ($current_page == 'portafolio') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>portafolio.php">Portafolio</a>
            <a class="<?php echo ($current_page == 'contactanos') ? 'is-active' : ''; ?>" href="<?php echo $base_path; ?>contactanos.php">Cont&aacute;ctanos</a>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_role'])): ?>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
            <?php else: ?>
                <?php 
                    // Cargar helper si no existe
                    if (!class_exists('DataHelper')) {
                        @include_once __DIR__ . '/data_helper.php';
                    }
                    $notifs = function_exists('silvex_get_notifications') && isset($_SESSION['client_id']) 
                                ? silvex_get_notifications($_SESSION['client_id']) 
                                : [];
                    $unreadCount = count(array_filter($notifs, function($n) { return empty($n['is_read']); }));
                ?>
                <div class="notifications-container" style="position:relative; display:inline-flex; align-items: center; margin-right: 1.5rem; cursor: pointer;">
                    <span style="font-size: 1.2rem;">🔔</span>
                    <?php if ($unreadCount > 0): ?>
                    <span style="position:absolute; top:-8px; right:-10px; background:#e53935; color:white; border-radius:50%; padding:0.1rem 0.4rem; font-size:0.6rem; font-weight:bold; box-shadow: 0 0 5px rgba(229,57,53,0.5);"><?php echo $unreadCount; ?></span>
                    <?php endif; ?>
                    <div class="notifications-dropdown" style="display:none; position:absolute; right:0; top:40px; width:280px; background:rgba(10,14,39,0.95); backdrop-filter: blur(10px); border:1px solid rgba(255,255,255,0.1); border-radius:12px; padding:1rem; box-shadow: 0 10px 30px rgba(0,0,0,0.8); z-index: 1000; text-align: left; max-height: 400px; overflow-y: auto;">
                        <h4 style="margin:0 0 0.8rem 0; font-size:0.9rem; border-bottom:1px solid rgba(255,255,255,0.1); padding-bottom:0.5rem; color:#fff;">Notificaciones</h4>
                        
                        <?php if (empty($notifs)): ?>
                            <p style="font-size:0.8rem; opacity:0.6; text-align:center; padding: 1rem 0; color:#fff;">No tienes notificaciones recientes.</p>
                        <?php else: ?>
                            <?php foreach(array_slice($notifs, 0, 8) as $n): 
                                $color = $n['type'] === 'success' ? '#81c784' : ($n['type'] === 'warning' ? '#fbc02d' : '#2ec8ef');
                            ?>
                            <div style="padding: 0.5rem; background:rgba(255,255,255,0.05); border-radius: 8px; margin-bottom: 0.5rem; opacity: <?php echo $n['is_read'] ? '0.6' : '1'; ?>;">
                                <span style="font-size: 0.75rem; color: <?php echo $color; ?>;"><?php echo htmlspecialchars($n['created_at']); ?></span>
                                <?php if($n['title']): ?><strong><div style="font-size:0.8rem; color:#fff;"><?php echo htmlspecialchars($n['title']); ?></div></strong><?php endif; ?>
                                <p style="font-size:0.8rem; margin:0.2rem 0 0 0; opacity:0.9; color:#fff;"><?php echo htmlspecialchars($n['message']); ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <script>
                    document.querySelector('.notifications-container').addEventListener('click', function(e) {
                        e.stopPropagation();
                        const drop = this.querySelector('.notifications-dropdown');
                        drop.style.display = drop.style.display === 'none' ? 'block' : 'none';
                        // if opened and has unread, we should ideally mark them read via AJAX (Out of scope for this simple implementation, we'll just hide the badge for now)
                        const badge = this.querySelector('span:nth-child(2)');
                        if(badge && drop.style.display === 'block') badge.style.display = 'none';
                    });
                    document.addEventListener('click', function() {
                        const drop = document.querySelector('.notifications-dropdown');
                        if(drop) drop.style.display = 'none';
                    });
                </script>
            <?php endif; ?>

            <a class="nav-auth-button nav-logout-button" href="<?php echo htmlspecialchars(silvex_logout_url($base_path), ENT_QUOTES, 'UTF-8'); ?>">Cerrar Sesi&oacute;n</a>
        <?php else: ?>
            <a class="nav-auth-button nav-login-button" href="<?php echo $base_path; ?>login.php">Ingresar</a>
        <?php endif; ?>
        <!-- Botón Dark / Light Mode -->
        <button
            id="theme-toggle"
            onclick="silvexToggleTheme()"
            aria-label="Cambiar tema"
            title="Cambiar entre modo oscuro y claro"
            style="color: inherit; background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.15); border-radius: 50%; width: 36px; height: 36px; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; margin-right: 0.5rem; transition: background 0.2s, color 0.2s;"
        >
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
        </button>
      </nav>
    </header>

<script>
// ── LOADER ──────────────────────────────────────────
(function() {
    const loader = document.getElementById('silvex-loader');
    if (!loader) return;

    function showLoader() {
        loader.style.display = 'flex';   // instantáneo, sin fade
        loader.classList.add('is-active');
    }

    document.addEventListener('submit', function(e) {
        if (e.target.dataset.noLoader === 'true') return;
        showLoader();
    });

    document.addEventListener('click', function(e) {
        const anchor = e.target.closest('a[href]');
        if (!anchor) return;
        const href = anchor.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript') || href.startsWith('mailto') || href.startsWith('tel')) return;
        if (anchor.target === '_blank' || anchor.hasAttribute('download')) return;
        showLoader();
    });

    window.addEventListener('pageshow', function() {
        loader.classList.remove('is-active');
        loader.style.display = 'none';
    });
})();

// ── DARK / LIGHT MODE ───────────────────────────────
const iconMoon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>';
const iconSun = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>';

function silvexToggleTheme() {
    const html = document.documentElement;
    const btn  = document.getElementById('theme-toggle');
    const isLight = html.classList.toggle('theme-light');
    if (btn) btn.innerHTML = isLight ? iconSun : iconMoon;
    localStorage.setItem('silvex-theme', isLight ? 'light' : 'dark');
}

// Aplicar tema guardado al cargar
(function() {
    const saved = localStorage.getItem('silvex-theme');
    const btn   = document.getElementById('theme-toggle');
    if (saved === 'light') {
        document.documentElement.classList.add('theme-light');
        if (btn) btn.innerHTML = iconSun;
    }
})();
</script>
