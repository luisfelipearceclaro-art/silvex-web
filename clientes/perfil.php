<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('cliente');

$clientId = $_SESSION['client_id'] ?? null;
$clientName = $_SESSION['client_name'] ?? 'Cliente';

if (!$clientId) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    $clients = DataHelper::read('clients.json');
    $updated = false;
    
    foreach ($clients as &$c) {
        if ($c['id'] === $clientId) {
            if ($c['password'] === $current_password) {
                $c['password'] = $new_password;
                $updated = true;
            } else {
                $error_msg = "La contraseña actual es incorrecta.";
            }
            break;
        }
    }
    unset($c);
    
    if ($updated) {
        DataHelper::write('clients.json', $clients);
        $success_msg = "Tu contraseña ha sido actualizada correctamente.";
    }
}

$page_title = "Silvex | Mi Perfil";
$current_page = "perfil";
$body_class = "page-clients";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <?php if (isset($success_msg)): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            ✓ <?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($error_msg)): ?>
        <div style="background: rgba(244, 67, 54, 0.2); color: #e57373; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(244, 67, 54, 0.3);">
            ✗ <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Mi Perfil y Seguridad</h1>
            <p>Ajustes de tu cuenta <strong><?php echo htmlspecialchars($clientName); ?></strong>.</p>
        </div>
        <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver al Portal</a>
    </div>

    <div style="max-width: 500px; margin: 0 auto;">
        <div class="premium-glass" style="display: block; padding: 2.5rem; border-radius: 25px;">
            <h3 style="margin-bottom: 1.5rem; color: #2ec8ef;">Cambiar Contraseña</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_password">
                
                <div style="margin-bottom: 1.2rem;">
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Contraseña Actual</label>
                    <input type="password" name="current_password" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Nueva Contraseña</label>
                    <input type="password" name="new_password" required minlength="6" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                
                <button type="submit" class="cta" style="width: 100%;">Actualizar Credenciales</button>
            </form>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
