<?php
$base_path = "../";
include '../auth.php';
include '../data_helper.php';
check_auth('admin');

// Obtener métricas reales
$clients = DataHelper::read('clients.json');
$meetings = DataHelper::read('meetings.json');
$leads = DataHelper::read('leads.json');
$projects = DataHelper::read('projects.json');

$count_clients = count($clients);
$count_leads = count($leads);
$count_meetings = count($meetings);
$count_projects = count($projects);

$page_title = "Silvex | Panel de Administración";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel page-panel--full animate-liquid">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
        <div>
            <h1>Panel de Administración</h1>
            <p>Estado actual de tu ecosistema empresarial.</p>
        </div>
        <div style="text-align: right; opacity: 0.6; font-size: 0.8rem;">
            Versión <?php echo CRM_VERSION; ?> | <?php echo date('d/m/Y H:i'); ?>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem; margin-bottom: 3rem;">
        <div class="premium-glass" style="padding: 1.5rem; text-align: center; border-radius: 20px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 0.5rem;">Clientes</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #2ec8ef;"><?php echo $count_clients; ?></div>
        </div>
        <div class="premium-glass" style="padding: 1.5rem; text-align: center; border-radius: 20px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 0.5rem;">Citas Sivaro</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #fbc02d;"><?php echo $count_meetings; ?></div>
        </div>
        <div class="premium-glass" style="padding: 1.5rem; text-align: center; border-radius: 20px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 0.5rem;">Leads / Chat</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #fff;"><?php echo $count_leads; ?></div>
        </div>
        <div class="premium-glass" style="padding: 1.5rem; text-align: center; border-radius: 20px;">
            <div style="font-size: 0.8rem; text-transform: uppercase; opacity: 0.6; margin-bottom: 0.5rem;">Proyectos</div>
            <div style="font-size: 2.5rem; font-weight: 800; color: #81c784;"><?php echo $count_projects; ?></div>
        </div>
    </div>
    
    <div class="plans-grid admin-options-grid" style="margin-top: 3rem;">
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Gestión de Clientes</h3>
            <p class="plan-card__subtitle">Administra los clientes activos y sus campañas publicitarias.</p>
            <a href="clients_list.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ver Clientes</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Reuniones Agendadas</h3>
            <p class="plan-card__subtitle">Consulta las citas programadas por el asistente virtual Sivaro.</p>
            <a href="view_meetings.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ver Reuniones</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Leads & Prospectos</h3>
            <p class="plan-card__subtitle">Ver mensajes y prospectos generados por el asistente virtual.</p>
            <a href="view_leads.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ver Leads</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Gestión de Proyectos</h3>
            <p class="plan-card__subtitle">Crea proyectos, asigna tareas y actualiza el % de avance.</p>
            <a href="projects_manage.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Administrar Proyectos</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Configuración</h3>
            <p class="plan-card__subtitle">Ajustes globales del sistema, PIN de seguridad y parámetros.</p>
            <a href="#" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ajustes</a>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
