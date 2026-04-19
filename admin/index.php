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

// Data for Project Chart
$project_status_counts = ['Iniciado' => 0, 'En Ejecución' => 0, 'Finalizado' => 0, 'Pausado' => 0];
foreach($projects as $p) {
    $stat = $p['status'] ?? 'Iniciado';
    if(isset($project_status_counts[$stat])) {
        $project_status_counts[$stat]++;
    } else {
        $project_status_counts[$stat] = 1;
    }
}

// Data for Leads Chart
$lead_status_counts = ['Nuevo' => 0, 'Contactado' => 0, 'Convertido' => 0, 'Descartado' => 0];
foreach($leads as $l) {
    $stat = $l['status'] ?? 'Nuevo';
    if(isset($lead_status_counts[$stat])) {
        $lead_status_counts[$stat]++;
    } else {
        $lead_status_counts[$stat] = 1;
    }
}

$page_title = "Silvex | Panel de Administración";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


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
    
    <!-- Visual Charts Grid -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 3rem;">
        <div class="premium-glass" style="display: block; padding: 2rem; border-radius: 20px;">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.2rem; color: #2ec8ef;">Estado de Proyectos</h3>
            <canvas id="projectsChart" style="max-height: 250px;"></canvas>
        </div>
        <div class="premium-glass" style="display: block; padding: 2rem; border-radius: 20px;">
            <h3 style="margin-bottom: 1.5rem; font-size: 1.2rem; color: #fbc02d;">Embudo de Leads</h3>
            <canvas id="leadsChart" style="max-height: 250px;"></canvas>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Data from PHP
        const projectData = <?php echo json_encode($project_status_counts); ?>;
        const leadData = <?php echo json_encode($lead_status_counts); ?>;

        // Color dinámico según tema
        const isLight = document.documentElement.classList.contains('theme-light');
        const textColor = isLight ? 'rgba(10,14,39,0.75)' : 'rgba(255,255,255,0.7)';
        const gridColor = isLight ? 'rgba(10,14,39,0.08)' : 'rgba(255,255,255,0.1)';

        Chart.defaults.color = textColor;
        Chart.defaults.font.family = "'Montserrat', sans-serif";

        // Projects Chart (Doughnut)
        new Chart(document.getElementById('projectsChart'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(projectData),
                datasets: [{
                    data: Object.values(projectData),
                    backgroundColor: ['#2ec8ef', '#fbc02d', '#81c784', '#ff6b6b'],
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                plugins: { legend: { position: 'right' } },
                cutout: '70%',
                maintainAspectRatio: false
            }
        });

        // Leads Chart (Bar)
        new Chart(document.getElementById('leadsChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(leadData),
                datasets: [{
                    label: 'Prospectos',
                    data: Object.values(leadData),
                    backgroundColor: ['#2ec8ef', '#fbc02d', '#81c784', '#ff6b6b'],
                    borderRadius: 8
                }]
            },
            options: {
                plugins: { legend: { display: false } },
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(255, 255, 255, 0.1)' }, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    });
    </script>
    
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
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Soporte de Clientes</h3>
            <p class="plan-card__subtitle">Atiende peticiones, dudas o incidencias reportadas por tus clientes.</p>
            <a href="view_tickets.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto; background: #2ec8ef;">Ver Tickets</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Configuración</h3>
            <p class="plan-card__subtitle">Ajustes globales del sistema, PIN de seguridad y parámetros.</p>
            <a href="settings.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ajustes</a>
        </div>

        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Registro de Actividad</h3>
            <p class="plan-card__subtitle">Historial de inicios de sesión y eventos de clientes en el portal.</p>
            <a href="activity_log.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto; background: rgba(251,192,45,0.2);">Ver Actividad 🕒</a>
        </div>
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Portafolio Web</h3>
            <p class="plan-card__subtitle">Edita casos de exito, metricas y rutas de imagen del portafolio publico.</p>
            <a href="portfolio_manage.php" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Gestionar Portafolio</a>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>




