<?php

function silvex_is_https() {
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }

    return !empty($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443;
}

function silvex_send_security_headers() {
    if (headers_sent()) {
        return;
    }

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
    header('Cross-Origin-Opener-Policy: same-origin');
}

function silvex_bootstrap_security() {
    static $bootstrapped = false;

    if ($bootstrapped) {
        return;
    }

    silvex_send_security_headers();

    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_secure', silvex_is_https() ? '1' : '0');

        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => silvex_is_https(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    if (empty($_SESSION['logout_token'])) {
        $_SESSION['logout_token'] = bin2hex(random_bytes(32));
    }

    $bootstrapped = true;
}

function silvex_csrf_token() {
    silvex_bootstrap_security();
    return $_SESSION['csrf_token'];
}

function silvex_verify_csrf_token($token) {
    silvex_bootstrap_security();

    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function silvex_logout_url($base_path = '') {
    silvex_bootstrap_security();
    return $base_path . 'logout.php?token=' . urlencode($_SESSION['logout_token']);
}

function silvex_verify_logout_token($token) {
    silvex_bootstrap_security();

    return is_string($token)
        && !empty($_SESSION['logout_token'])
        && hash_equals($_SESSION['logout_token'], $token);
}
