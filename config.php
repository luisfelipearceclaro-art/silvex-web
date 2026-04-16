<?php
/**
 * Silvex CRM - Configuración Global
 * Este archivo centraliza los parámetros críticos del sistema.
 */

// Directorios de Datos
define('DATA_PATH', __DIR__ . '/server/data/');
define('BACKUP_PATH', DATA_PATH . 'backups/');

// Seguridad
define('ADMIN_PIN', '123'); // Puedes cambiar esto por una contraseña más fuerte
define('DEFAULT_CLIENT_PASSWORD', 'Silvex2024'); // Contraseña inicial para todos los clientes

// Estética y UI
define('CRM_VERSION', '2.0.0');
define('BRAND_NAME', 'Silvex Estudio');

// Configuración de PHP
date_default_timezone_set('America/Bogota'); // Ajustado a tu zona horaria (Colombia/Peru/Ecuador)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Desactivado para seguridad (puedes activar en desarrollo)
?>
