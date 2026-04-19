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
    } elseif ($_POST['action'] === 'update_project') {
        $projectId = $_POST['projectId'] ?? '';
        $progress = max(0, min(100, (int) $_POST['progress']));
        $updated = false;

        foreach ($projects as &$p) {
            if ($p['id'] !== $projectId) {
                continue;
            }

            $p['clientId'] = $_POST['clientId'] ?? $p['clientId'];
            $p['name'] = trim($_POST['name'] ?? $p['name']);
            $p['status'] = $_POST['status'] ?? $p['status'];
            $p['progress'] = $progress;
            $p['lastUpdate'] = date('Y-m-d');
            $updated = true;
            break;
        }
        unset($p);

        if ($updated) {
            DataHelper::write('projects.json', $projects);
            $success_msg = "Proyecto actualizado correctamente.";
        }
    } elseif ($_POST['action'] === 'update_progress') {
        $progress = max(0, min(100, (int) $_POST['progress']));
        foreach ($projects as &$p) {
            if ($p['id'] === $_POST['projectId']) {
                $p['progress'] = $progress;
                $p['status'] = $_POST['status'];
                $p['lastUpdate'] = date('Y-m-d');
                if (function_exists('silvex_add_notification')) {
                    silvex_add_notification($p['clientId'], "Actualización de Proyecto", "Tu proyecto '{$p['name']}' avanzó al {$progress}%. Estado: {$p['status']}.", "info");
                }
                break;
            }
        }
        unset($p);
        DataHelper::write('projects.json', $projects);
        $success_msg = "Progreso actualizado.";
    } elseif ($_POST['action'] === 'upload_file') {
        $projectId = $_POST['projectId'];
        $uploadDir = __DIR__ . '/../server/data/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $fileName = basename($_FILES['file']['name']);
            $targetName = time() . '_' . preg_replace('/[^a-zA-Z0-9.\-_]/', '', $fileName);
            $targetPath = $uploadDir . $targetName;
            
            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $entregables = DataHelper::read('entregables.json');
                $entregables[] = [
                    'id' => 'f' . time(),
                    'projectId' => $projectId,
                    'name' => trim($_POST['filename'] ?: $fileName),
                    'path' => 'server/data/uploads/' . $targetName,
                    'date' => date('Y-m-d H:i:s')
                ];
                DataHelper::write('entregables.json', $entregables);
                
                // Enviar notificación al cliente
                foreach ($projects as $p) {
                    if ($p['id'] === $projectId) {
                        if (function_exists('silvex_add_notification')) {
                            $docName = $entregables[count($entregables)-1]['name'];
                            silvex_add_notification($p['clientId'], "Nuevo Entregable", "Se ha subido un nuevo archivo a tu proyecto '{$p['name']}': {$docName}", "success");
                        }
                        break;
                    }
                }
                
                $success_msg = "✓ Archivo subido correctamente.";
            } else {
                $error_msg = "Error al mover el archivo al servidor.";
            }
        } else {
            $error_msg = "Error en la subida del archivo.";
        }
    } elseif ($_POST['action'] === 'add_phase') {
        $projectId = $_POST['projectId'];
        foreach ($projects as &$p) {
            if ($p['id'] === $projectId) {
                if (!isset($p['phases'])) $p['phases'] = [];
                $p['phases'][] = [
                    'id' => 'ph' . time(),
                    'name' => trim($_POST['phase_name']),
                    'status' => 'pending' // pending, in_progress, completed
                ];
                break;
            }
        }
        unset($p);
        DataHelper::write('projects.json', $projects);
        $success_msg = "✓ Fase añadida al proyecto.";
    } elseif ($_POST['action'] === 'update_phase') {
        $projectId = $_POST['projectId'];
        $phaseId = $_POST['phaseId'];
        $newStatus = $_POST['phaseStatus'];
        foreach ($projects as &$p) {
            if ($p['id'] === $projectId && isset($p['phases'])) {
                foreach ($p['phases'] as &$ph) {
                    if ($ph['id'] === $phaseId) {
                        $ph['status'] = $newStatus;
                        break;
                    }
                }
                break;
            }
        }
        unset($p);
        DataHelper::write('projects.json', $projects);
        $success_msg = "✓ Estado de fase actualizado.";
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
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <button onclick="document.getElementById('project-modal').style.display='flex'" class="cta" style="min-width: auto;">+ Nuevo Proyecto</button>
            <a href="export.php?type=projects" class="cta" style="min-width: auto; background: rgba(46,200,239,0.15); border: 1px solid rgba(46,200,239,0.3);">⬇ Exportar CSV</a>
            <a href="index.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Volver</a>
        </div>
    </div>

    <div class="projects-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); gap: 2rem;">
        <?php foreach ($projects as $project):
            $projectClient = DataHelper::findOneBy('clients.json', 'id', $project['clientId']);
        ?>
        <div class="premium-glass" style="display: block; padding: 2rem; border-radius: 25px; position: relative; overflow: hidden;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem; gap: 1rem;">
                <div>
                    <span style="font-size: 0.7rem; text-transform: uppercase; opacity: 0.5;"><?php echo htmlspecialchars($projectClient['company'] ?? 'Cliente Desconocido'); ?></span>
                    <h3 style="margin: 0.2rem 0 0; font-size: 1.5rem;"><?php echo htmlspecialchars($project['name']); ?></h3>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 0.5rem;">
                    <span style="padding: 0.3rem 0.8rem; border-radius: 20px; font-size: 0.7rem; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                        <?php echo htmlspecialchars($project['status']); ?>
                    </span>
                    <button
                        type="button"
                        onclick="openEditProjectModal(this)"
                        style="color: #2ec8ef; text-decoration: none; font-weight: 600; font-size: 0.8rem; background: transparent; border: 0; cursor: pointer; padding: 0;"
                        data-project-id="<?php echo htmlspecialchars($project['id'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-client-id="<?php echo htmlspecialchars($project['clientId'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-name="<?php echo htmlspecialchars($project['name'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-status="<?php echo htmlspecialchars($project['status'], ENT_QUOTES, 'UTF-8'); ?>"
                        data-progress="<?php echo (int) $project['progress']; ?>"
                    >Editar proyecto</button>
                </div>
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

            <details style="margin-top: 1rem; background: rgba(0,0,0,0.15); padding: 0.8rem; border-radius: 12px;">
                <summary style="cursor:pointer; color:#2ec8ef; font-size:0.85rem; font-weight: 600; outline:none;">Gestión Avanzada (Hitos y Archivos)</summary>
                
                <div style="margin-top: 1rem;">
                    <!-- Activos / Entregables -->
                    <form method="POST" enctype="multipart/form-data" style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(255,255,255,0.05);" id="upload-form-<?php echo $project['id']; ?>">
                        <input type="hidden" name="action" value="upload_file">
                        <input type="hidden" name="projectId" value="<?php echo htmlspecialchars($project['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div style="font-size:0.8rem; margin-bottom: 0.8rem; opacity:0.8;">1. Subir Entrega Final o Archivo:</div>
                        
                        <!-- Drop Zone -->
                        <div class="drop-zone" id="drop-<?php echo $project['id']; ?>"
                             style="border: 2px dashed rgba(46,200,239,0.4); border-radius: 12px; padding: 1.5rem; text-align: center; cursor: pointer; transition: all 0.2s; margin-bottom: 0.8rem; background: rgba(46,200,239,0.05);">
                            <div style="font-size: 1.5rem; margin-bottom: 0.3rem;">☁️</div>
                            <div style="font-size: 0.8rem; opacity: 0.7;">Arrastra tu archivo aquí o <span style="color:#2ec8ef; text-decoration: underline;">haz clic</span></div>
                            <div class="drop-filename" style="font-size: 0.75rem; color: #81c784; margin-top: 0.4rem; display: none;"></div>
                        </div>
                        <input type="file" name="file" required id="file-<?php echo $project['id']; ?>" style="display: none;">
                        
                        <input type="text" name="filename" placeholder="Nombre amigable (ej. Reporte Final)" id="fname-<?php echo $project['id']; ?>" style="width:100%; padding:0.5rem; border-radius:6px; border:1px solid rgba(255,255,255,0.1); margin-bottom: 0.5rem; background:rgba(255,255,255,0.05); color:#fff; font-size:0.8rem; outline:none;">
                        <button type="submit" class="cta" style="padding:0.4rem 0.8rem; font-size:0.75rem; min-width:auto; margin:0;">⬆ Cargar al Portal del Cliente</button>
                    </form>

                    <!-- Fases -->
                    <form method="POST">
                        <input type="hidden" name="action" value="add_phase">
                        <input type="hidden" name="projectId" value="<?php echo htmlspecialchars($project['id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <div style="font-size:0.8rem; margin-bottom: 0.5rem; opacity:0.8;">2. Añadir Fase / Hito:</div>
                        <div style="display:flex; gap:0.5rem;">
                            <input type="text" name="phase_name" placeholder="Ej: Fase de Diseño" required style="flex:1; padding:0.5rem; border-radius:6px; border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.05); color:#fff; font-size:0.8rem; outline:none;">
                            <button type="submit" class="cta" style="padding:0.4rem 0.8rem; font-size:0.75rem; min-width:auto; margin:0;">Añadir</button>
                        </div>
                    </form>

                    <?php if(!empty($project['phases'])): ?>
                    <div style="margin-top: 1rem;">
                        <div style="font-size:0.75rem; opacity:0.7; margin-bottom:0.4rem;">Fases Actuales:</div>
                        <?php foreach($project['phases'] as $ph): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; background:rgba(0,0,0,0.2); padding:0.4rem 0.6rem; border-radius:6px; margin-bottom:0.3rem;">
                            <span style="font-size:0.8rem;"><?php echo htmlspecialchars($ph['name']); ?></span>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="action" value="update_phase">
                                <input type="hidden" name="projectId" value="<?php echo $project['id']; ?>">
                                <input type="hidden" name="phaseId" value="<?php echo $ph['id']; ?>">
                                <select name="phaseStatus" onchange="this.form.submit()" style="background:rgba(255,255,255,0.1); border:none; color:#fff; font-size:0.7rem; padding:0.2rem; border-radius:4px; outline:none;">
                                    <option value="pending" style="color:#000;" <?php echo $ph['status']=='pending'?'selected':''; ?>>Pendiente</option>
                                    <option value="in_progress" style="color:#000;" <?php echo $ph['status']=='in_progress'?'selected':''; ?>>En Progreso</option>
                                    <option value="completed" style="color:#000;" <?php echo $ph['status']=='completed'?'selected':''; ?>>Completado</option>
                                </select>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </details>

            <div style="margin-top: 1.5rem; font-size: 0.75rem; opacity: 0.4;">
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

<div id="edit-project-modal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(10px); z-index: 1000; justify-content: center; align-items: center; padding: 1rem; overflow-y: auto;">
    <div class="premium-glass" style="width: 100%; max-width: 500px; max-height: calc(100vh - 2rem); padding: 2rem; border-radius: 30px; overflow-y: auto;">
        <h2 style="margin-bottom: 1.5rem; color: #2ec8ef;">Gestionar Proyecto</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_project">
            <input type="hidden" name="projectId" id="edit-project-id">
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Cliente</label>
                <select name="clientId" id="edit-project-client" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                    <?php foreach ($clients as $c): ?>
                        <option value="<?php echo htmlspecialchars($c['id'], ENT_QUOTES, 'UTF-8'); ?>" style="color: #111; background: #fff;"><?php echo htmlspecialchars($c['company']); ?> (<?php echo htmlspecialchars($c['name']); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 1.2rem;">
                <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Nombre del Proyecto</label>
                <input type="text" name="name" id="edit-project-name" required style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Estado</label>
                    <select name="status" id="edit-project-status" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff;">
                        <option value="Iniciado" style="color: #111; background: #fff;">Iniciado</option>
                        <option value="En Ejecución" style="color: #111; background: #fff;">En Ejecución</option>
                        <option value="Finalizado" style="color: #111; background: #fff;">Finalizado</option>
                        <option value="Pausado" style="color: #111; background: #fff;">Pausado</option>
                    </select>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Progreso (%)</label>
                    <input type="number" name="progress" id="edit-project-progress" min="0" max="100" inputmode="numeric" oninput="if (this.value === '') return; this.value = Math.min(100, Math.max(0, Number(this.value))); if (Number.isNaN(Number(this.value))) this.value = 0;" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff;">
                </div>
            </div>

            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="cta" style="flex: 1;">Guardar Cambios</button>
                <button type="button" onclick="closeEditProjectModal()" class="cta" style="flex: 1; background: rgba(255,255,255,0.1);">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEditProjectModal(button) {
    document.getElementById('edit-project-id').value = button.dataset.projectId || '';
    document.getElementById('edit-project-client').value = button.dataset.clientId || '';
    document.getElementById('edit-project-name').value = button.dataset.name || '';
    document.getElementById('edit-project-status').value = button.dataset.status || 'Iniciado';
    document.getElementById('edit-project-progress').value = button.dataset.progress || 0;
    document.getElementById('edit-project-modal').style.display = 'flex';
}

function closeEditProjectModal() {
    document.getElementById('edit-project-modal').style.display = 'none';
}

// Drag & Drop for all upload zones
document.querySelectorAll('.drop-zone').forEach(function(zone) {
    const projectId = zone.id.replace('drop-', '');
    const fileInput = document.getElementById('file-' + projectId);
    const fnameInput = document.getElementById('fname-' + projectId);
    const label = zone.querySelector('.drop-filename');

    zone.addEventListener('click', () => fileInput.click());

    zone.addEventListener('dragover', (e) => {
        e.preventDefault();
        zone.style.background = 'rgba(46,200,239,0.15)';
        zone.style.borderColor = 'rgba(46,200,239,0.8)';
    });

    zone.addEventListener('dragleave', () => {
        zone.style.background = 'rgba(46,200,239,0.05)';
        zone.style.borderColor = 'rgba(46,200,239,0.4)';
    });

    zone.addEventListener('drop', (e) => {
        e.preventDefault();
        zone.style.background = 'rgba(46,200,239,0.05)';
        zone.style.borderColor = 'rgba(46,200,239,0.4)';
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFilename(files[0].name, label, fnameInput);
        }
    });

    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            updateFilename(this.files[0].name, label, fnameInput);
        }
    });
});

function updateFilename(name, label, fnameInput) {
    label.textContent = '✓ ' + name;
    label.style.display = 'block';
    if (!fnameInput.value) {
        fnameInput.value = name.replace(/\.[^/.]+$/, '').toUpperCase();
    }
}
</script>

<?php include '../footer.php'; ?>

