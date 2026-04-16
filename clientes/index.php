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

$page_title = "Silvex | Portal de Clientes";
$current_page = "clientes";
$body_class = "page-clients";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <div style="margin-bottom: 2rem;">
        <div>
            <h1>Portal de Clientes</h1>
            <p>Bienvenido, <strong><?php echo htmlspecialchars($client['name'] ?? 'Cliente'); ?></strong>. Aquí están tus resultados en tiempo real.</p>
        </div>
    </div>
    
    <div class="projects-container" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(400px, 1fr)); gap: 2rem; margin-top: 2rem;">
        <?php foreach ($projects as $project): ?>
        <div class="plan-card" style="display: block;">
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
            
            <p class="plan-card__subtitle" style="font-size: 0.85rem; margin-bottom: 1.5rem;">Última actualización: <?php echo htmlspecialchars($project['lastUpdate']); ?></p>
            
            <a href="#" class="cta" style="font-size: 0.9rem; min-width: auto;">Ver Reporte Completo</a>
        </div>
        <?php endforeach; ?>
        
        <?php if (empty($projects)): ?>
        <div class="premium-glass" style="grid-column: 1 / -1; padding: 4rem; text-align: center;">
            <p style="opacity: 0.5;">No tienes proyectos activos asignados en este momento.</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include '../footer.php'; ?>
