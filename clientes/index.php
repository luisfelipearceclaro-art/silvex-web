<?php
$base_path = "../";
include '../auth.php';
check_auth('cliente');

$page_title = "Silvex | Portal de Clientes";
$current_page = "clientes";
$body_class = "page-clients";
include '../header.php';
?>

<section class="hero__content">
    <h1>Portal de Clientes</h1>
    <div class="hero__divider" aria-hidden="true"></div>
    <p>Aquí puedes ver el estado de tu publicidad y resultados en tiempo real.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 40px;">
        <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <h3>Mi Campaña</h3>
            <p>Estado actual y piezas creativas.</p>
        </div>
        <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <h3>Resultados</h3>
            <p>Métricas de alcance y conversiones.</p>
        </div>
        <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <h3>Soporte</h3>
            <p>Contacta con tu asesor asignado.</p>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
