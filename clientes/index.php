<?php
$base_path = "../";
include '../auth.php';
check_auth('cliente');

$page_title = "Silvex | Portal de Clientes";
$current_page = "clientes";
$body_class = "page-clients";
include '../header.php';
?>

<section class="page-panel animate-liquid">
    <h1>Portal de Clientes</h1>
    <p>Aquí puedes ver el estado de tu publicidad y resultados en tiempo real.</p>
    
    <div class="plans-grid" style="margin-top: 3rem;">
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Mi Campaña</h3>
            <p class="plan-card__subtitle">Visualiza el estado actual, las piezas creativas y el cronograma.</p>
            <a href="#" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ver Campaña</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Resultados</h3>
            <p class="plan-card__subtitle">Consulta métricas de alcance, engagement y conversiones de tus anuncios.</p>
            <a href="#" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Ver Métricas</a>
        </div>
        
        <div class="plan-card">
            <h3 class="plan-card__title" style="font-size: 1.8rem;">Soporte</h3>
            <p class="plan-card__subtitle">¿Tienes dudas? Contacta directamente con tu asesor de cuenta asignado.</p>
            <a href="#" class="cta" style="margin-top: 1rem; font-size: 1rem; min-width: auto;">Contactar</a>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
