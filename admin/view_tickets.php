<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$tickets = DataHelper::read('tickets.json');

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_ticket') {
        $ticketId = $_POST['ticketId'];
        $newStatus = $_POST['status'];
        
        foreach ($tickets as &$t) {
            if ($t['id'] === $ticketId) {
                $t['status'] = $newStatus;
                if ($newStatus === 'Resuelto') {
                    $t['resolved_at'] = date('Y-m-d H:i:s');
                }
                break;
            }
        }
        unset($t);
        DataHelper::write('tickets.json', $tickets);
        $success_msg = "Estado del ticket actualizado.";
    }
}

// Ensure array exists and is reversed (newest first)
if (!is_array($tickets)) $tickets = [];
$tickets = array_reverse($tickets);

$page_title = "Silvex | Soporte y Peticiones";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <?php if (isset($success_msg)): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            ✓ <?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1>Soporte y Peticiones</h1>
            <p>Atiende las solicitudes y problemas de tus clientes centralizadamente.</p>
        </div>
        <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver al Dashboard</a>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 1.5rem;">
        <?php foreach ($tickets as $ticket): 
            $statusColor = '#2ec8ef'; // Abierto
            if($ticket['status'] === 'En Proceso') $statusColor = '#fbc02d';
            if($ticket['status'] === 'Resuelto') $statusColor = '#81c784';
        ?>
        <div class="premium-glass" style="display: block; padding: 2rem; border-radius: 20px; position: relative;">
            <div style="position: absolute; top: 1.5rem; right: 1.5rem;">
                <span style="font-size: 0.65rem; padding: 0.3rem 0.8rem; border-radius: 10px; background: rgba(255,255,255,0.1); color: <?php echo $statusColor; ?>; font-weight: 700; text-transform: uppercase;">
                    <?php echo htmlspecialchars($ticket['status']); ?>
                </span>
            </div>
            
            <h3 style="margin-bottom: 0.5rem; font-size: 1.3rem; padding-right: 5rem;"><?php echo htmlspecialchars($ticket['subject']); ?></h3>
            <div style="font-size: 0.8rem; opacity: 0.6; margin-bottom: 1rem;">
                De: <strong><?php echo htmlspecialchars($ticket['clientName'] ?? 'Desconocido'); ?></strong> — <?php echo htmlspecialchars($ticket['created_at']); ?>
            </div>
            
            <div style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem; line-height: 1.4; color: #ddd; max-height: 150px; overflow-y: auto;">
                <?php echo nl2br(htmlspecialchars($ticket['message'])); ?>
            </div>
            
            <form method="POST" style="display: flex; gap: 0.5rem;">
                <input type="hidden" name="action" value="update_ticket">
                <input type="hidden" name="ticketId" value="<?php echo htmlspecialchars($ticket['id']); ?>">
                <select name="status" style="flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.5rem; color: #fff; font-size: 0.85rem; outline: none;">
                    <option value="Abierto" style="color: #111;" <?php echo $ticket['status']=='Abierto'?'selected':''; ?>>Abierto</option>
                    <option value="En Proceso" style="color: #111;" <?php echo $ticket['status']=='En Proceso'?'selected':''; ?>>En Proceso</option>
                    <option value="Resuelto" style="color: #111;" <?php echo $ticket['status']=='Resuelto'?'selected':''; ?>>Resuelto</option>
                </select>
                <button type="submit" class="cta" style="padding: 0.5rem 1rem; font-size: 0.8rem; min-width: auto; margin:0;">Actualizar</button>
            </form>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($tickets)): ?>
        <div class="premium-glass" style="grid-column: 1 / -1; padding: 4rem; text-align: center; justify-content: center;">
            <p style="opacity: 0.5; font-size: 1.1rem;">Todo brilla. No hay tickets de soporte activos.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../footer.php'; ?>
