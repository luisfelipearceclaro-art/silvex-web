<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('cliente');

$clientId = $_SESSION['client_id'] ?? null;
$clientName = $_SESSION['client_name'] ?? 'Cliente';
$projects = [];

if ($clientId) {
    $client = DataHelper::findOneBy('clients.json', 'id', $clientId);
    $projects = DataHelper::findBy('projects.json', 'clientId', $clientId);
} else {
    // Fallback por si la sesión no tiene el ID (viejas sesiones)
    $userEmail = $_SESSION['user_email'] ?? '';
    $client = DataHelper::findOneBy('clients.json', 'email', $userEmail);
    if ($client) {
        $projects = DataHelper::findBy('projects.json', 'clientId', $client['id']);
        $clientName = $client['name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_ticket') {
    $tickets = DataHelper::read('tickets.json');
    if (!is_array($tickets)) $tickets = [];
    
    $tickets[] = [
        'id' => 't' . time(),
        'clientId' => $clientId,
        'clientName' => $clientName,
        'subject' => trim($_POST['subject']),
        'message' => trim($_POST['message']),
        'status' => 'Abierto',
        'created_at' => date('Y-m-d H:i:s')
    ];
    DataHelper::write('tickets.json', $tickets);
    $success_msg = "Tu ticket ha sido enviado correctamente. Responderemos pronto.";
}

$page_title = "Silvex | Portal de Clientes";
$current_page = "clientes";
$body_class = "page-clients";
include '../header.php';
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<section class="page-panel page-panel--full animate-liquid">
    <?php if (isset($success_msg)): ?>
        <div style="background: rgba(76, 175, 80, 0.2); color: #81c784; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; border: 1px solid rgba(76, 175, 80, 0.3);">
            ✓ <?php echo htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1>Portal de Clientes</h1>
            <p>Bienvenido, <strong><?php echo htmlspecialchars($client['name'] ?? 'Cliente'); ?></strong>. Aquí están tus resultados en tiempo real.</p>
        </div>
        <div>
            <a href="perfil.php" class="cta" style="min-width: auto; background: rgba(255,255,255,0.1);">Mi Perfil / Contraseña</a>
        </div>
    </div>

    <div class="projects-container" style="display: flex; flex-wrap: wrap; justify-content: center; gap: 2rem; margin-top: 2rem;">
        <?php foreach ($projects as $project): ?>
        <div class="plan-card" style="display: block; width: 100%; max-width: 450px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                <h3 class="plan-card__title" style="font-size: 1.6rem; margin: 0;"><?php echo htmlspecialchars($project['name']); ?></h3>
                <span style="font-size: 0.75rem; padding: 0.2rem 0.6rem; background: rgba(255,255,255,0.1); border-radius: 10px;">
                    <?php echo htmlspecialchars($project['status']); ?>
                </span>
            </div>
            
            <div style="margin: 1.5rem 0;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                    <span style="opacity: 0.8;">Progreso de campaña</span>
                    <strong><?php echo (int)$project['progress']; ?>%</strong>
                </div>
                <div style="width: 100%; height: 8px; background: rgba(255,255,255,0.1); border-radius: 4px; overflow: hidden;">
                    <div style="width: <?php echo (int)$project['progress']; ?>%; height: 100%; background: linear-gradient(90deg, #2ec8ef, #1a237e); box-shadow: 0 0 10px rgba(46, 200, 239, 0.5);"></div>
                </div>
            </div>

            <?php if(!empty($project['phases'])): ?>
            <div style="margin-bottom: 1.5rem;">
                <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 0.8rem; font-weight: 600;">Línea de Tiempo (Hitos)</div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php foreach($project['phases'] as $ph): 
                        $icon = '⏳'; $color = '#aaa';
                        if($ph['status'] == 'in_progress') { $icon = '🔄'; $color = '#2ec8ef'; }
                        if($ph['status'] == 'completed') { $icon = '✅'; $color = '#81c784'; }
                    ?>
                    <div style="display: flex; align-items: center; gap: 0.8rem; font-size: 0.85rem;">
                        <span><?php echo $icon; ?></span>
                        <span style="color: <?php echo $color; ?>; flex: 1;"><?php echo htmlspecialchars($ph['name']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php
            // Obtener entregables del proyecto
            $entregables = DataHelper::findBy('entregables.json', 'projectId', $project['id']);
            if (!empty($entregables)):
            ?>
            <div style="margin-bottom: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 0.8rem; font-weight: 600;">Entregables y Archivos</div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    <?php foreach($entregables as $file): ?>
                    <a href="../<?php echo htmlspecialchars($file['path'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" download class="cta" style="font-size: 0.8rem; min-width: auto; padding: 0.5rem 1rem; background: rgba(46, 200, 239, 0.15); display: flex; justify-content: space-between; align-items: center;">
                        <span>📄 <?php echo htmlspecialchars($file['name']); ?></span>
                        <span style="opacity:0.5; font-size:0.7rem;">Descargar</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if(!empty($project['metrics'])): ?>
            <div style="margin-bottom: 1.5rem; padding-top: 0.5rem;">
                <div style="font-size: 0.85rem; opacity: 0.8; margin-bottom: 1rem; font-weight: 600;">Rendimiento (Alcance)</div>
                <canvas id="chart-<?php echo $project['id']; ?>" width="400" height="200" style="width: 100%;"></canvas>
            </div>
            <?php endif; ?>

            <p class="plan-card__subtitle" style="font-size: 0.75rem; margin-bottom: 0;">Última actualización: <?php echo htmlspecialchars($project['lastUpdate']); ?></p>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($projects)): ?>
        <div class="premium-glass" style="grid-column: 1 / -1; padding: 4rem; text-align: center;">
            <p style="opacity: 0.5;">No tienes proyectos activos asignados en este momento.</p>
        </div>
        <?php endif; ?>
    </div>
    </div>

    <!-- Soporte Section -->
    <div style="margin-top: 4rem; max-width: 600px; margin-left: auto; margin-right: auto;">
        <div class="premium-glass" style="display: block; padding: 2.5rem; border-radius: 25px;">
            <h3 style="margin-bottom: 0.5rem; color: #2ec8ef;">Centro de Soporte</h3>
            <p style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 2rem;">¿Tienes alguna pregunta, necesitas un cambio o tienes problemas con una campaña? Abre un ticket de soporte y nuestro equipo lo revisará.</p>
            
            <form method="POST">
                <input type="hidden" name="action" value="new_ticket">
                
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Asunto / Requerimiento</label>
                    <input type="text" name="subject" required placeholder="Ej: Cambio de imagen en anuncio" style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none;">
                </div>
                
                <div style="margin-bottom: 2rem;">
                    <label style="display: block; font-size: 0.8rem; opacity: 0.7; margin-bottom: 0.4rem;">Describe tu solicitud detalladamente</label>
                    <textarea name="message" required rows="4" placeholder="Detalles..." style="width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 10px; padding: 0.8rem; color: #fff; outline: none; resize: vertical;"></textarea>
                </div>
                
                <button type="submit" class="cta" style="width: 100%;">Enviar Ticket de Soporte</button>
            </form>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const projects = <?php echo json_encode($projects); ?>;
    Chart.defaults.color = 'rgba(255, 255, 255, 0.7)';
    Chart.defaults.font.family = "'Inter', 'Roboto', sans-serif";

    projects.forEach(function(proj) {
        if (proj.metrics && proj.metrics.labels && proj.metrics.data) {
            const ctx = document.getElementById('chart-' + proj.id);
            if (ctx) {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: proj.metrics.labels,
                        datasets: [{
                            label: 'Interacciones',
                            data: proj.metrics.data,
                            borderColor: '#2ec8ef',
                            backgroundColor: 'rgba(46, 200, 239, 0.2)',
                            borderWidth: 2,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#2ec8ef',
                            pointRadius: 4,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: { display: false, beginAtZero: true },
                            x: {
                                grid: { color: 'rgba(255,255,255,0.05)' }
                            }
                        }
                    }
                });
            }
        }
    });
});
</script>
