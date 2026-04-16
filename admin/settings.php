<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$config_file = '../config.php';
$msg = "";
$error = "";

// Handle Configuration Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_config') {
    $new_pin = trim($_POST['admin_pin']);
    $new_brand = trim($_POST['brand_name']);
    
    if (empty($new_pin) || empty($new_brand)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        $config_content = file_get_contents($config_file);
        
        // Expresiones regulares para encontrar y reemplazar las definiciones
        $config_content = preg_replace("/define\('ADMIN_PIN', '.*'\);/", "define('ADMIN_PIN', '$new_pin');", $config_content);
        $config_content = preg_replace("/define\('BRAND_NAME', '.*'\);/", "define('BRAND_NAME', '$new_brand');", $config_content);
        
        if (file_put_contents($config_file, $config_content)) {
            $msg = "Configuración actualizada con éxito. Algunos cambios pueden requerir recargar la página.";
        } else {
            $error = "No se pudo escribir en el archivo de configuración. Verifica los permisos.";
        }
    }
}

$page_title = "Silvex | Ajustes del Sistema";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <?php if ($msg): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            ✓ <?php echo $msg; ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div style="background: rgba(244, 67, 54, 0.2); color: #e57373; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(244, 67, 54, 0.3);">
            ✗ <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1>Ajustes Globales</h1>
            <p>Configura los parámetros maestros de tu plataforma Silvex.</p>
        </div>
        <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <!-- Formulario de Configuración -->
        <div class="premium-glass" style="padding: 2.5rem; border-radius: 30px;">
            <h3 style="margin-bottom: 2rem; color: #2ec8ef;">Parámetros del Sistema</h3>
            <form method="POST">
                <input type="hidden" name="action" value="update_config">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.85rem; opacity: 0.6; margin-bottom: 0.6rem;">PIN de Seguridad (Borrado de Reuniones)</label>
                    <input type="text" name="admin_pin" value="<?php echo htmlspecialchars(ADMIN_PIN); ?>" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; color: #fff; font-family: monospace; font-size: 1.1rem; outline: none;">
                    <p style="font-size: 0.75rem; opacity: 0.4; margin-top: 0.5rem;">Este PIN se solicita al eliminar citas o realizar acciones críticas.</p>
                </div>

                <div style="margin-bottom: 2.5rem;">
                    <label style="display: block; font-size: 0.85rem; opacity: 0.6; margin-bottom: 0.6rem;">Nombre de la Marca</label>
                    <input type="text" name="brand_name" value="<?php echo htmlspecialchars(BRAND_NAME); ?>" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px; padding: 1rem; color: #fff; outline: none;">
                    <p style="font-size: 0.75rem; opacity: 0.4; margin-top: 0.5rem;">Aparecerá en el encabezado y en las comunicaciones del CRM.</p>
                </div>

                <button type="submit" class="cta" style="width: 100%; padding: 1.2rem;">Guardar Todos los Cambios</button>
            </form>
        </div>

        <!-- Información del Sistema -->
        <div>
            <div class="premium-glass" style="padding: 1.5rem; border-radius: 20px; margin-bottom: 1.5rem; text-align: center;">
                <div style="font-size: 0.7rem; opacity: 0.5; text-transform: uppercase;">Versión del Sistema</div>
                <div style="font-size: 2rem; font-weight: 800; color: #81c784;"><?php echo CRM_VERSION; ?></div>
            </div>

            <div class="premium-glass" style="padding: 1.5rem; border-radius: 20px;">
                <h4 style="margin-bottom: 1rem; font-size: 0.9rem;">Estado del Entorno</h4>
                <ul style="list-style: none; font-size: 0.8rem; opacity: 0.7; padding: 0;">
                    <li style="margin-bottom: 0.5rem;">🌐 Servidor: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></li>
                    <li style="margin-bottom: 0.5rem;">🐘 PHP: <?php echo phpversion(); ?></li>
                    <li style="margin-bottom: 0.5rem;">📁 Directorio: Silvex Web Core</li>
                    <li>💾 Persistencia: JSON Dinámico</li>
                </ul>
            </div>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
