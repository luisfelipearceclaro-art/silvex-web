<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

$projects = DataHelper::read('projects.json');
$clients = DataHelper::read('clients.json');

// Handle Project Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'new_project') {
        $progress = max(0, min(100, (int) $_POST['progress']));
        $newProject = [
            'id' => 'p' . (count($projects) + 1) . '_' . time(),
            'clientId' => $_POST['clientId'],
            'name' => trim($_POST['name']),
            'status' => $_POST['status'],
            'progress' => $progress,
            'lastUpdate' => date('Y-m-d')
        ];
        $projects[] = $newProject;
        DataHelper::write('projects.json', $projects);
        $success_msg = "Proyecto '" . $newProject['name'] . "' creado.";
    } elseif ($_POST['action'] === 'update_progress') {
        $progress = max(0, min(100, (int) $_POST['progress']));
        foreach ($projects as &$p) {
            if ($p['id'] === $_POST['projectId']) {
                $p['progress'] = $progress;
                $p['status'] = $_POST['status'];
                $p['lastUpdate'] = date('Y-m-d');
                break;
            }
        }
        unset($p);
        DataHelper::write('projects.json', $projects);
        $success_msg = "Progreso actualizado.";
    }
}

$page_title = "Silvex | Centro de Proyectos";
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
            <h1>Centro de Gesti&oacute;n de Proyectos</h1>
            <p>Monitorea y actualiza el avance de cada campa&ntilde;a activa.</p>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button onclick="document.getElementById('project-modal').style.display='flex'" class="cta" style="min-width: auto;">+ Nuevo Proyecto</button>
            <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
        </div>
    </div>

    <div class="projects-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 2rem;">
        <?php foreach ($projects as $project):
            $projectClient = DataHelper::findOneBy('clients.json', 'id', $project['clientId']);
        ?>
        <div class="premium-glass" style="padding: 2rem; border-radius: 25px; position: relative; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1rem;">
                <div>
                    <span style="font-size: 0.7rem; text-transform: uppercase; opacity: 0.5;"><?php echo htmlspecialchars($projectClient['company'] ?? 'Cliente Desconocido'); ?></span>
                    <h3 style="margin: 0.2rem 0 0; font-size: 1.5rem;"><?php echo htmlspecialchars($project['name']); ?></h3>
                </div>
                <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.7rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                    <?php echo htmlspecialchars($project['status']); ?>
                </span>
            </div>

            <form method="POST" style="background: rgba(0,0,0,0.2); padding: 1.5rem; border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
                <input type="hidden" name="action" value="update_progress">
                <input type="hidden" name="projectId" value="<?php echo htmlspecialchars($project['id'], ENT_QUOTES, 'UTF-8'); ?>">

                <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem; font-size: 0.85rem;">
                    <span style="opacity: 0.7;">Avance Actual:</span>
                    <strong><?php echo (int)$project['progress']; ?>%</strong>
                </div>

                <input type="range" name="progress" value="<?php echo (int)$project['progress']; ?>" min="0" max="100" style="width: 100%; margin-bottom: 1.2rem; cursor: pointer;">

                <div style="display: flex; gap: 0.8rem; align-items: center;">
                    <select name="status" style="flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; padding: 0.5rem; color: #fff; font-size: 0.85rem;">
                        <option value="Iniciado" style="color: #111; background: #fff;" <?php echo $project['status'] == 'Iniciado' ? 'selected' : ''; ?>>Iniciado</option>
                        <option value="En Ejecuci&oacute;n" style="color: #111; background: #fff;" <?php echo $project['status'] == 'En Ejecuci&oacute;n' ? 'selected' : ''; ?>>En Ejecuci&oacute;n</option>
                        <option value="Finalizado" style="color: #111; background: #fff;" <?php echo $project['status'] == 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                        <option value="Pausado" style="color: #111; background: #fff;" <?php echo $project['status'] == 'Pausado' ? 'selected' : ''; ?>>Pausado</option>
                    </select>
                    <button type="submit" class="cta" style="margin: 0; padding: 0.5rem 1rem; font-size: 0.8rem; min-width: auto;">Actualizar</button>
                </div>
            </form>

            <div style="margin-top: 1rem; font-size: 0.75rem; opacity: 0.4;">
                &Uacute;ltimo cambio: <?php echo htmlspecialchars($project['lastUpdate']); ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<div id="project-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; justify-content: center; align-items: center; padding: 1rem; overflow-y: auto;">
    <div class="premium-glass" style="width: 100%; max-width: 500px; max-height: calc(100vh - 2rem); padding: 2rem; border-radius: 30px; overflow-y: auto;">
        <h2 style="margin-bottom: 1.5rem; color: #2ec8ef;">Crear Proyecto</h2>
        <form method="POST">
            <input type="hidden" name="action" value="new_project">
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Vincular Cliente</label>
                <select name="clientId" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                    <?php foreach ($clients as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8'); ?>" style="color: #111; background: #fff;"><?php echo htmlspecialchars($c['company']); ?> (<?php echo htmlspecialchars($c['name']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Nombre del Proyecto</label>
                <input type="text" name="name" required placeholder="Ej: Redise&ntilde;o Web" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Estado Inicial</label>
                    <select name="status" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff;">
                        <option value="Iniciado" style="color: #111; background: #fff;">Iniciado</option>
                        <option value="En Ejecuci&oacute;n" style="color: #111; background: #fff;">En Ejecuci&oacute;n</option>
                        <option value="Pausado" style="color: #111; background: #fff;">Pausado</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Progreso (%)</label>
                    <input type="number" name="progress" value="0" min="0" max="100" inputmode="numeric" oninput="if (this.value === '') return; this.value = Math.min(100, Math.max(0, Number(this.value))); if (Number.isNaN(Number(this.value))) this.value = 0;" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff;">
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="cta" style="flex: 1;">Crear Ahora</button>
                <button type="button" onclick="document.getElementById('project-modal').style.display='none'" class="cta" style="flex: 1; background: rgba(255,255,255,0.1);">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<?php include '../footer.php'; ?>

