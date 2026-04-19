<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$clients = DataHelper::read('clients.json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_client') {
    $name = trim($_POST['name']);

    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '', $name)));
    $login_email = $slug . "@silvex.com";

    $newClient = [
        'id' => 'c' . (count($clients) + 1) . '_' . time(),
        'name' => $name,
        'email' => trim($_POST['email']),
        'login_email' => $login_email,
        'password' => DEFAULT_CLIENT_PASSWORD,
        'company' => trim($_POST['company']),
        'status' => 'activo',
        'joined' => date('Y-m-d')
    ];

    $clients[] = $newClient;
    DataHelper::write('clients.json', $clients);

    $success_msg = "Cliente creado. Acceso generado: " . $login_email . " | Contrase&ntilde;a: " . DEFAULT_CLIENT_PASSWORD;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_client') {
    $clientId = $_POST['client_id'] ?? '';
    $updated = false;

    foreach ($clients as &$client) {
        if (($client['id'] ?? '') !== $clientId) {
            continue;
        }

        $client['company'] = trim($_POST['company'] ?? $client['company']);
        $client['name'] = trim($_POST['name'] ?? $client['name']);
        $client['email'] = trim($_POST['email'] ?? $client['email']);
        $client['status'] = trim($_POST['status'] ?? $client['status']);
        $updated = true;
        break;
    }
    unset($client);

    if ($updated) {
        DataHelper::write('clients.json', $clients);
        $success_msg = "Cliente actualizado correctamente.";
    }
}

$page_title = "Silvex | Listado de Clientes";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <?php if (isset($success_msg)): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            <?php echo html_entity_decode(htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; gap: 1rem; flex-wrap: wrap;">
        <div>
            <h1>Gesti&oacute;n de Clientes</h1>
            <p>Listado de marcas y contactos bajo gesti&oacute;n activa.</p>
        </div>
        <div style="flex-shrink: 0; display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <button type="button" onclick="document.getElementById('client-modal').style.display='flex'" class="cta" style="min-width: auto; box-shadow: none; border: none;">+ Nuevo Cliente</button>
            <a href="export.php?type=clients" class="cta" style="min-width: auto; background: rgba(46,200,239,0.15); border: 1px solid rgba(46,200,239,0.3);" title="Descargar CSV con todos los clientes">⬇ Exportar CSV</a>
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
                        <button
                            type="button"
                            onclick="openEditClientModal(this)"
                            style="color: #2ec8ef; text-decoration: none; font-weight: 600; font-size: 0.9rem; background: transparent; border: 0; cursor: pointer;"
                            data-client-id="<?php echo htmlspecialchars($client['id'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-company="<?php echo htmlspecialchars($client['company'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-name="<?php echo htmlspecialchars($client['name'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-email="<?php echo htmlspecialchars($client['email'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-status="<?php echo htmlspecialchars($client['status'], ENT_QUOTES, 'UTF-8'); ?>"
                            data-login-email="<?php echo htmlspecialchars($client['login_email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                        >Gestionar</button>
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

<div id="edit-client-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; justify-content: center; align-items: center; padding: 1rem; overflow-y: auto;">
    <div class="premium-glass" style="width: 100%; max-width: 500px; max-height: calc(100vh - 2rem); padding: 2rem; position: relative; border-radius: 30px; overflow-y: auto;">
        <h2 style="margin-bottom: 1.5rem; color: #2ec8ef;">Gestionar Cliente</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_client">
            <input type="hidden" name="client_id" id="edit-client-id">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Acceso al Portal</label>
                <input type="text" id="edit-client-login-email" readonly style="width: 100%; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 0.8rem; color: rgba(255,255,255,0.7); outline: none;">
            </div>
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Nombre de la Empresa</label>
                <input type="text" name="company" id="edit-client-company" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Nombre del Contacto</label>
                <input type="text" name="name" id="edit-client-name" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Correo Electr&oacute;nico</label>
                <input type="email" name="email" id="edit-client-email" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>
            <div style="margin-bottom: 2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Estado</label>
                <select name="status" id="edit-client-status" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                    <option value="activo" style="color: #111; background: #fff;">Activo</option>
                    <option value="pausado" style="color: #111; background: #fff;">Pausado</option>
                    <option value="inactivo" style="color: #111; background: #fff;">Inactivo</option>
                </select>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="cta" style="flex: 1;">Guardar Cambios</button>
                <button type="button" onclick="closeEditClientModal()" class="cta" style="flex: 1; background: rgba(255,255,255,0.1);">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditClientModal(button) {
    document.getElementById('edit-client-id').value = button.dataset.clientId || '';
    document.getElementById('edit-client-company').value = button.dataset.company || '';
    document.getElementById('edit-client-name').value = button.dataset.name || '';
    document.getElementById('edit-client-email').value = button.dataset.email || '';
    document.getElementById('edit-client-status').value = button.dataset.status || 'activo';
    document.getElementById('edit-client-login-email').value = button.dataset.loginEmail || '';
    document.getElementById('edit-client-modal').style.display = 'flex';
}

function closeEditClientModal() {
    document.getElementById('edit-client-modal').style.display = 'none';
}
</script>

<?php include '../footer.php'; ?>
