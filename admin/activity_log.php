<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$logs = [];
$logFile = DATA_PATH . 'activity_log.json';
if (file_exists($logFile)) {
    $logs = json_decode(file_get_contents($logFile), true) ?: [];
}
// Most recent first
$logs = array_reverse($logs);

$page_title = "Silvex | Registro de Actividad";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1>Registro de Actividad</h1>
            <p>Historial de sesiones e inicios de sesión en el sistema.</p>
        </div>
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <a href="export.php?type=activity_log" class="cta" style="min-width: auto; background: rgba(46,200,239,0.15); border: 1px solid rgba(46,200,239,0.3);">⬇ Exportar CSV</a>
            <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
        </div>
    </div>

    <div class="table-container premium-glass" style="padding: 0; overflow: hidden; border-radius: 20px;">
        <table style="width: 100%; border-collapse: collapse; color: #fff; text-align: left;">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th style="padding: 1.2rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; opacity: 0.7;">Fecha y Hora</th>
                    <th style="padding: 1.2rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; opacity: 0.7;">Usuario</th>
                    <th style="padding: 1.2rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; opacity: 0.7;">Email</th>
                    <th style="padding: 1.2rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; opacity: 0.7;">Evento</th>
                    <th style="padding: 1.2rem 1.5rem; font-size: 0.8rem; text-transform: uppercase; opacity: 0.7;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $i => $log): 
                    $isAdmin = strpos($log['event'], 'Admin') !== false;
                    $dotColor = $isAdmin ? '#fbc02d' : '#2ec8ef';
                ?>
                <tr style="border-top: 1px solid rgba(255,255,255,0.06); <?php echo $i === 0 ? 'background: rgba(46,200,239,0.04);' : ''; ?>">
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; opacity: 0.7; white-space: nowrap;">
                        <?php echo htmlspecialchars($log['timestamp']); ?>
                    </td>
                    <td style="padding: 1rem 1.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.6rem;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: <?php echo $dotColor; ?>; flex-shrink: 0; box-shadow: 0 0 6px <?php echo $dotColor; ?>;"></span>
                            <strong style="font-size: 0.95rem;"><?php echo htmlspecialchars($log['user']); ?></strong>
                        </div>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-size: 0.85rem; opacity: 0.6;">
                        <?php echo htmlspecialchars($log['email']); ?>
                    </td>
                    <td style="padding: 1rem 1.5rem;">
                        <span style="font-size: 0.78rem; padding: 0.25rem 0.7rem; border-radius: 20px; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.1);">
                            <?php echo htmlspecialchars($log['event']); ?>
                        </span>
                    </td>
                    <td style="padding: 1rem 1.5rem; font-size: 0.8rem; opacity: 0.5; font-family: monospace;">
                        <?php echo htmlspecialchars($log['ip']); ?>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="5" style="padding: 4rem; text-align: center; opacity: 0.4;">
                        Sin actividad registrada todavía. Inicia sesión como cliente para ver el primer registro.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../footer.php'; ?>
