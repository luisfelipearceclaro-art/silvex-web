<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$clients = DataHelper::read('clients.json');

// Handle New Client Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_client') {
    $name = trim($_POST['name']);
    
    // Generar email de acceso (slugify nombre + @silvex.com)
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '', $name)));
    $login_email = $slug . "@silvex.com";

    $newClient = [
        'id' => 'c' . (count($clients) + 1) . '_' . time(),
        'name' => $name,
        'email' => trim($_POST['email']), // Email personal
        'login_email' => $login_email,    // Email de acceso al sistema
        'password' => DEFAULT_CLIENT_PASSWORD,
        'company' => trim($_POST['company']),
        'status' => 'activo',
        'joined' => date('Y-m-d')
    ];

    $clients[] = $newClient;
    DataHelper::write('clients.json', $clients);
    
    $success_msg = "✓ Cliente creado.<br><strong>Acceso generado:</strong> $login_email<br><strong>Contraseña:</strong> " . DEFAULT_CLIENT_PASSWORD;
}

$page_title = "Silvex | Listado de Clientes";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <?php if (isset($success_msg)): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            + <?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1>Gesti&oacute;n de Clientes</h1>
            <p>Listado de marcas y contactos bajo gesti&oacute;n activa.</p>
        </div>
        <div style="flex-shrink: 0; display: flex; gap: 1rem;">
            <button type="button" onclick="document.getElementById('client-modal').style.display='flex'" class="cta" style="min-width: auto; box-shadow: none; border: none;">+ Nuevo Cliente</button>
            <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
        </div>
    </div>

    <div class="table-container premium-glass" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; color: #fff; text-align: left;">
            <thead style="background: rgba(255,255,255,0.05);">
                <tr>
                    <th style="padding: 1.5rem;">Empresa</th>
                    <th style="padding: 1.5rem;">Contacto</th>
                    <th style="padding: 1.5rem;">Email</th>
                    <th style="padding: 1.5rem;">Estado</th>
                    <th style="padding: 1.5rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                <tr style="border-top: 1px solid rgba(255,255,255,0.1);">
                    <td style="padding: 1.5rem;">
                        <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($client['company']); ?></strong>
                    </td>
                    <td style="padding: 1.5rem; opacity: 0.9;">
                        <?php echo htmlspecialchars($client['name']); ?>
                    </td>
                    <td style="padding: 1.5rem; opacity: 0.7; font-size: 0.9rem;">
                        <?php echo htmlspecialchars($client['email']); ?>
                    </td>
                    <td style="padding: 1.5rem;">
                        <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; background: rgba(46, 200, 239, 0.2); color: #2ec8ef; border: 1px solid rgba(46, 200, 239, 0.3);">
                            <?php echo htmlspecialchars($client['status']); ?>
                        </span>
                    </td>
                    <td style="padding: 1.5rem; text-align: right;">
                        <a href="#" style="color: #2ec8ef; text-decoration: none; font-weight: 600; font-size: 0.9rem;">Gestionar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clients)): ?>
                <tr>
                    <td colspan="5" style="padding: 3rem; text-align: center; opacity: 0.5;">No hay clientes registrados a&uacute;n.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<div id="client-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; justify-content: center; align-items: center; padding: 1rem; overflow-y: auto;">
    <div class="premium-glass" style="width: 100%; max-width: 500px; max-height: calc(100vh - 2rem); padding: 2rem; position: relative; border-radius: 30px; overflow-y: auto;">
        <h2 style="margin-bottom: 1.5rem; color: #2ec8ef;">Agregar Nuevo Cliente</h2>
        <form method="POST">
            <input type="hidden" name="action" value="new_client">
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Nombre de la Empresa</label>
                <input type="text" name="company" required placeholder="Ej: Silvex Estudio" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Nombre del Contacto</label>
                <input type="text" name="name" required placeholder="Ej: Luis Arce" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Correo Electr&oacute;nico</label>
                <input type="email" name="email" required placeholder="cliente@ejemplo.com" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="cta" style="flex: 1;">Guardar Cliente</button>
                <button type="button" onclick="document.getElementById('client-modal').style.display='none'" class="cta" style="flex: 1; background: rgba(255,255,255,0.1);">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>

