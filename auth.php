<?php
require_once __DIR__ . '/security.php';
silvex_bootstrap_security();

function check_auth($required_role = null) {
    if (!isset($_SESSION['user_role'])) {
        header('Location: ' . $GLOBALS['base_path'] . 'login.php');
        exit;
    }

    $sessionAgent = $_SESSION['user_agent'] ?? null;
    $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    if ($sessionAgent !== null && !hash_equals($sessionAgent, $currentAgent)) {
        $_SESSION = [];
        session_destroy();
        header('Location: ' . $GLOBALS['base_path'] . 'login.php');
        exit;
    }

    if ($required_role && $_SESSION['user_role'] !== $required_role) {
        // Redirigir si no tiene el rol necesario
        header('Location: ' . $GLOBALS['base_path'] . 'index.php');
        exit;
    }
}
?>
