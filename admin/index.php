<?php
$base_path = "../";
include '../auth.php';
check_auth('admin');

$page_title = "Silvex | Panel de Administración";
$current_page = "admin";
$body_class = "page-admin";
include '../header.php';
?>

<section class="hero__content">
    <h1>Administrador</h1>
    <div class="hero__divider" aria-hidden="true"></div>
    <p>Bienvenido al Panel de Control de Silvex.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 40px;">
        <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <h3>Gestión de Clientes</h3>
            <p>Administra los clientes activos y sus campañas.</p>
        </div>
        <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <h3>Métricas de Publicidad</h3>
            <p>Visualiza el rendimiento global de las marcas.</p>
        </div>
        <div style="background: rgba(255,255,255,0.05); padding: 20px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
            <h3>Configuración</h3>
            <p>Ajustes generales del sistema.</p>
        </div>
    </div>
</section>

<?php include '../footer.php'; ?>
