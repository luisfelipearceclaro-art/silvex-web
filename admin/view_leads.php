<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$leads = DataHelper::read('leads.json');

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    foreach ($leads as &$l) {
        if ($l['createdAt'] === $_POST['leadId']) { // Usando createdAt como ID temporal único
            $l['status'] = $_POST['status'];
            break;
        }
    }
    DataHelper::write('leads.json', $leads);
    $success_msg = "Estado del prospecto actualizado.";
}

// Sort by date (descending)
usort($leads, function($a, $b) {
    return strcmp($b['createdAt'] ?? '', $a['createdAt'] ?? '');
});

$page_title = "Silvex | Prospectos (Leads)";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <?php if (isset($success_msg)): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            ✓ <?php echo $success_msg; ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Leads del Asistente</h1>
            <p>Personas interesadas captadas por Sivaro (IA).</p>
        </div>
        <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
    </div>

    <div class="leads-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 1.5rem;">
        <?php foreach ($leads as $lead): 
            $status = $lead['status'] ?? 'Nuevo';
            $statusColor = '#2ec8ef';
            if($status === 'Contactado') $statusColor = '#fbc02d';
            if($status === 'Convertido') $statusColor = '#81c784';
            if($status === 'Descartado') $statusColor = '#ff4b2b';
        ?>
        <div class="premium-glass" style="display: block; padding: 1.5rem; border-radius: 20px; position: relative;">
            <div style="position: absolute; top: 1rem; right: 1rem;">
                <span style="font-size: 0.65rem; padding: 0.2rem 0.6rem; border-radius: 10px; background: rgba(255,255,255,0.1); color: <?php echo $statusColor; ?>; font-weight: 700; text-transform: uppercase;">
                    <?php echo $status; ?>
                </span>
            </div>

            <div style="margin-bottom: 1rem;">
                <strong style="font-size: 1.25rem; color: #fff;">
                    <?php echo htmlspecialchars($lead['name'] ?: 'Prospecto Anónimo'); ?>
                </strong>
                <div style="font-size: 0.7rem; opacity: 0.4; margin-top: 0.2rem;">
                    Captado el <?php echo date('d/m/Y H:i', strtotime($lead['createdAt'])); ?>
                </div>
            </div>
            
            <div style="font-size: 0.9rem; margin-bottom: 1.2rem;">
                <p style="margin-bottom: 0.4rem; opacity: 0.8;">📧 <?php echo htmlspecialchars($lead['email'] ?: 'No provisto'); ?></p>
                <p style="opacity: 0.8;">📱 <?php echo htmlspecialchars($lead['phone'] ?: 'No provisto'); ?></p>
            </div>

            <div style="background: rgba(0,0,0,0.15); padding: 1rem; border-radius: 12px; font-size: 0.85rem; margin-bottom: 1.5rem; border: 1px solid rgba(255,255,255,0.03);">
                <p style="margin-bottom: 0.5rem;"><strong style="color: #2ec8ef;">Objetivo:</strong> <?php echo htmlspecialchars($lead['objective'] ?: '-'); ?></p>
                <p><strong style="color: #2ec8ef;">Plan Sugerido:</strong> <span style="color: #fbc02d;"><?php echo htmlspecialchars($lead['recommendedPlan'] ?: '-'); ?></span></p>
            </div>
            
            <div style="display: flex; gap: 0.8rem; align-items: center;">
                <form method="POST" style="flex: 1; display: flex; gap: 0.5rem;">
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="leadId" value="<?php echo $lead['createdAt']; ?>">
                    <select name="status" onchange="this.form.submit()" style="flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.4rem; color: #fff; font-size: 0.8rem;">
                        <option value="Nuevo" <?php echo $status == 'Nuevo' ? 'selected' : ''; ?>>Nuevo</option>
                        <option value="Contactado" <?php echo $status == 'Contactado' ? 'selected' : ''; ?>>Contactado</option>
                        <option value="Convertido" <?php echo $status == 'Convertido' ? 'selected' : ''; ?>>Convertido</option>
                        <option value="Descartado" <?php echo $status == 'Descartado' ? 'selected' : ''; ?>>Descartado</option>
                    </select>
                </form>
                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $lead['phone']); ?>" target="_blank" class="cta" style="margin: 0; padding: 0.5rem 1rem; font-size: 0.8rem; min-width: auto; background: #25D366; border: none;">WhatsApp</a>
            </div>
        </div>
        <?php endforeach; ?>

        <?php if (empty($leads)): ?>
        <div class="premium-glass" style="grid-column: 1 / -1; padding: 4rem; text-align: center;">
            <p style="opacity: 0.5;">No hay prospectos registrados aún.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../footer.php'; ?>
