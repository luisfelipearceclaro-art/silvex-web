<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$meetingsPath = '../server/data/meetings.json';

// Handle Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && isset($_POST['pin'])) {
    if ($_POST['pin'] === ADMIN_PIN) {
        $idToDelete = $_POST['delete_id'];
        $currentMeetings = DataHelper::read('meetings.json');
        $filteredMeetings = array_filter($currentMeetings, function($m) use ($idToDelete) {
            return $m['id'] !== $idToDelete;
        });
        DataHelper::write('meetings.json', array_values($filteredMeetings));
        $success_msg = "Reunión eliminada correctamente del registro.";
    } else {
        $error_msg = "PIN de seguridad incorrecto. Inténtalo de nuevo.";
    }
}

$meetings = DataHelper::read('meetings.json');

// Sort by date and time
usort($meetings, function($a, $b) {
    $cmp = strcmp($a['meetingDate'], $b['meetingDate']);
    if ($cmp === 0) return strcmp($a['meetingTime'], $b['meetingTime']);
    return $cmp;
});

$page_title = "Silvex | Reuniones Agendadas";
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
    <?php if (isset($error_msg)): ?>
        <div style="background: rgba(244, 67, 54, 0.2); color: #e57373; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(244, 67, 54, 0.3);">
            ✗ <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Reuniones por Sivaro</h1>
            <p>Listado de citas agendadas por el asistente virtual.</p>
        </div>
        <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
    </div>

    <div class="table-container premium-glass" style="padding: 0; overflow-x: auto;">
        <table style="width: 100%; min-width: 950px; border-collapse: collapse; color: #fff; text-align: left;">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th style="padding: 1rem;">Fecha / Hora</th>
                    <th style="padding: 1rem;">Contacto</th>
                    <th style="padding: 1rem;">Empresa</th>
                    <th style="padding: 1rem;">Tipo</th>
                    <th style="padding: 1rem;">Motivo</th>
                    <th style="padding: 1rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($meetings as $m): ?>
                <tr style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <td style="padding: 1rem;">
                        <span style="display: block; font-weight: 700; color: #2ec8ef;"><?php echo htmlspecialchars($m['meetingDate']); ?></span>
                        <span style="font-size: 0.85rem; opacity: 0.7;"><?php echo htmlspecialchars($m['meetingTime']); ?></span>
                    </td>
                    <td style="padding: 1rem;">
                        <strong style="display: block;"><?php echo htmlspecialchars($m['fullName']); ?></strong>
                        <span style="font-size: 0.8rem; opacity: 0.6;"><?php echo htmlspecialchars($m['email']); ?> / <?php echo htmlspecialchars($m['phone']); ?></span>
                    </td>
                    <td style="padding: 1rem; opacity: 0.8;">
                        <?php echo htmlspecialchars($m['company']); ?>
                    </td>
                    <td style="padding: 1rem;">
                        <span style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.8;">
                            <?php echo $m['meetingType'] === 'virtual' ? '🌐 Virtual' : '🏙️ Presencial'; ?>
                        </span>
                    </td>
                    <td style="padding: 1rem;">
                        <p style="font-size: 0.85rem; opacity: 0.7; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($m['reason']); ?>">
                            <?php echo htmlspecialchars($m['reason']); ?>
                        </p>
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <form method="POST" style="display: inline;" onsubmit="return promptDelete(this);">
                            <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($m['id']); ?>">
                            <input type="hidden" name="pin" id="pin_<?php echo htmlspecialchars($m['id']); ?>">
                            <button type="submit" style="background: rgba(255, 75, 43, 0.15); border: 1px solid rgba(255, 75, 43, 0.3); border-radius: 20px; color: #ff6b6b; padding: 0.4rem 0.8rem; cursor: pointer; font-weight: 700; font-size: 0.7rem; transition: all 0.3s ease;">BORRAR</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($meetings)): ?>
                <tr>
                    <td colspan="5" style="padding: 3rem; text-align: center; opacity: 0.5;">No hay reuniones registradas.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../footer.php'; ?>

<script>
function promptDelete(form) {
    const pin = prompt("Por seguridad, ingresa el PIN de administrador para borrar esta reunión:");
    if (pin === null) return false; // Cancelado
    
    // Asignar el pin al input oculto correspondiente
    const id = form.querySelector('input[name="delete_id"]').value;
    document.getElementById('pin_' + id).value = pin;
    return true;
}
</script>
