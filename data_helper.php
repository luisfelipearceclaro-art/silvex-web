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
