<?php
$base_path = "../";
include '../auth.php';
check_auth('admin');

$page_title = "Silvex | Panel de Administración";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="page-panel animate-liquid">
    <h1>Administrador</h1>
    <p>Bienvenido al Panel de Control de Silvex.</p>
    
    <div class="plans-grid" style="margin-top: 3rem;">
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Gestión de Clientes</h3>
            <p class="plan-card__subtitle">Administra los clientes activos y sus campañas publicitarias.</p>
            <a href="#" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ver Clientes</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Métricas de Publicidad</h3>
            <p class="plan-card__subtitle">Visualiza el rendimiento global de todas las marcas bajo gestión.</p>
            <a href="#" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ver Reportes</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Configuración</h3>
            <p class="plan-card__subtitle">Ajustes generales del sistema, accesos y parámetros globales.</p>
            <a href="#" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ajustes</a>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
