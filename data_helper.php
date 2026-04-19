<?php

require_once __DIR__ . '/config.php';

class DataHelper {
    private static function getFilePath($filename) {
        return DATA_PATH . $filename;
    }

    private static function backup($filename, $content) {
        if (!is_dir(BACKUP_PATH)) {
            mkdir(BACKUP_PATH, 0777, true);
        }
        $backupFile = BACKUP_PATH . $filename . '.' . date('Ymd_His') . '.bak';
        file_put_contents($backupFile, $content);
        
        // Mantener solo los últimos 5 backups por archivo
        $backups = glob(BACKUP_PATH . $filename . '.*.bak');
        if (count($backups) > 5) {
            array_multisort(array_map('filemtime', $backups), SORT_ASC, $backups);
            unlink($backups[0]);
        }
    }

    public static function read($filename) {
        $path = self::getFilePath($filename);
        if (!file_exists($path)) {
            return [];
        }
        $content = file_get_contents($path);
        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("Silvex CRM Error: JSON corrupto en $filename: " . json_last_error_msg());
            return [];
        }
        
        return $data ?: [];
    }

    public static function write($filename, $data) {
        $path = self::getFilePath($filename);
        if (!is_dir(DATA_PATH)) {
            mkdir(DATA_PATH, 0777, true);
        }
        
        // Copia de seguridad del archivo actual antes de sobreescribir
        if (file_exists($path)) {
            self::backup($filename, file_get_contents($path));
        }

        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        
        if ($content === false) {
            error_log("Silvex CRM Error: No se pudo codificar JSON para $filename");
            return false;
        }

        return file_put_contents($path, $content);
    }

    public static function findBy($filename, $field, $value) {
        $data = self::read($filename);
        return array_filter($data, function($item) use ($field, $value) {
            return isset($item[$field]) && $item[$field] === $value;
        });
    }

    public static function findOneBy($filename, $field, $value) {
        $results = self::findBy($filename, $field, $value);
        return !empty($results) ? array_shift($results) : null;
    }
}

/**
 * Registra un evento de actividad en activity_log.json
 */
function silvex_log_activity(string $userName, string $userEmail, string $event): void {
    $logFile = DATA_PATH . 'activity_log.json';
    $logs = [];
    if (file_exists($logFile)) {
        $logs = json_decode(file_get_contents($logFile), true) ?: [];
    }
    $logs[] = [
        'user'      => $userName,
        'email'     => $userEmail,
        'event'     => $event,
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
        'timestamp' => date('Y-m-d H:i:s'),
    ];
    // Mantener los últimos 200 registros
    if (count($logs) > 200) {
        $logs = array_slice($logs, -200);
    }
    if (!is_dir(DATA_PATH)) mkdir(DATA_PATH, 0777, true);
    file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Añade una notificación interna para un cliente específico.
 */
function silvex_add_notification(string $clientId, string $title, string $message, string $type = 'info'): void {
    $notifFile = DATA_PATH . 'notifications.json';
    $notifs = [];
    if (file_exists($notifFile)) {
        $notifs = json_decode(file_get_contents($notifFile), true) ?: [];
    }
    
    $notifs[] = [
        'id' => uniqid('notif_'),
        'client_id' => $clientId,
        'title' => $title,
        'message' => $message,
        'type' => $type, // info, success, warning
        'is_read' => false,
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    if (!is_dir(DATA_PATH)) mkdir(DATA_PATH, 0777, true);
    file_put_contents($notifFile, json_encode($notifs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Obtiene notificaciones para un cliente.
 */
function silvex_get_notifications(string $clientId): array {
    $notifFile = DATA_PATH . 'notifications.json';
    if (!file_exists($notifFile)) return [];
    
    $notifs = json_decode(file_get_contents($notifFile), true) ?: [];
    $clientNotifs = array_filter($notifs, function($n) use ($clientId) {
        return isset($n['client_id']) && $n['client_id'] === $clientId;
    });
    
    // Sort by created_at desc
    usort($clientNotifs, function($a, $b) {
        return strtotime($b['created_at']) <=> strtotime($a['created_at']);
    });
    
    return $clientNotifs;
}

/**
 * Marca las notificaciones de un cliente como leídas.
 */
function silvex_mark_notifications_read(string $clientId): void {
    $notifFile = DATA_PATH . 'notifications.json';
    if (!file_exists($notifFile)) return;
    
    $notifs = json_decode(file_get_contents($notifFile), true) ?: [];
    $changed = false;
    
    foreach ($notifs as &$n) {
        if (isset($n['client_id']) && $n['client_id'] === $clientId && !$n['is_read']) {
            $n['is_read'] = true;
            $changed = true;
        }
    }
    unset($n);
    
    if ($changed) {
        file_put_contents($notifFile, json_encode($notifs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
