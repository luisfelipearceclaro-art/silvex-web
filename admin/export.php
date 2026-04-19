<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$type = $_GET['type'] ?? '';
$allowed = ['clients', 'leads', 'projects'];

if (!in_array($type, $allowed)) {
    http_response_code(400);
    die('Tipo no válido.');
}

$data = DataHelper::read($type . '.json');

if (!is_array($data) || empty($data)) {
    http_response_code(404);
    die('No hay datos para exportar.');
}

// Obtener columnas del primer registro
$columns = array_keys($data[0]);

// Eliminar columnas sensibles
$exclude = ['password', 'login_email'];
$columns = array_filter($columns, fn($c) => !in_array($c, $exclude));
$columns = array_values($columns);

// Headers de descarga CSV
$filename = 'silvex_' . $type . '_' . date('Y-m-d') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache');

$output = fopen('php://output', 'w');
// BOM para Excel (UTF-8)
fputs($output, "\xEF\xBB\xBF");

// Cabeceras en mayúscula
fputcsv($output, array_map('strtoupper', $columns));

foreach ($data as $row) {
    $line = [];
    foreach ($columns as $col) {
        $val = $row[$col] ?? '';
        // Si es array (ej. metrics), convertir a JSON string
        if (is_array($val)) {
            $val = json_encode($val, JSON_UNESCAPED_UNICODE);
        }
        $line[] = $val;
    }
    fputcsv($output, $line);
}

fclose($output);
exit;
