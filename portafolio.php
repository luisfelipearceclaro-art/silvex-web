<?php
$portfolioCases = require __DIR__ . "/data/portfolio.php";
$page_title = "Silvex Estudio | Portafolio";
$current_page = "portafolio";
$hero_class = "hero--page";
include 'header.php';
?>

    <section class="page-panel page-panel--portfolio">
      <h1>Nuestros Casos de Éxito</h1>
      <p>
        En Silvex no solo diseñamos, resolvemos problemas de negocio.
        Aquí tienes una selección de marcas que han transformado su presencia digital con nosotros.
      </p>
    </section>

    <section class="portfolio-grid">
      <?php foreach ($portfolioCases as $case): ?>
        <article class="portfolio-card">
          <div class="portfolio-card__image-container">
            <img
              src="<?php echo htmlspecialchars($case["image"], ENT_QUOTES, "UTF-8"); ?>"
              alt="<?php echo htmlspecialchars($case["alt"], ENT_QUOTES, "UTF-8"); ?>"
            >
            <div class="portfolio-card__overlay">
              <span class="portfolio-tag">
                <?php echo htmlspecialchars($case["category"], ENT_QUOTES, "UTF-8"); ?>
              </span>
            </div>
          </div>
          <div class="portfolio-card__content">
            <h3><?php echo htmlspecialchars($case["title"], ENT_QUOTES, "UTF-8"); ?></h3>
            <p><?php echo htmlspecialchars($case["summary"], ENT_QUOTES, "UTF-8"); ?></p>
            <div class="portfolio-metrics">
              <?php foreach ($case["metrics"] as $metric): ?>
                <div class="metric">
                  <span class="metric__value"><?php echo htmlspecialchars($metric["value"], ENT_QUOTES, "UTF-8"); ?></span>
                  <span class="metric__label"><?php echo htmlspecialchars($metric["label"], ENT_QUOTES, "UTF-8"); ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </section>

    <section class="portfolio-cta">
        <h2>¿Quieres ser nuestro próximo éxito?</h2>
        <p>Llevamos tu marca del diseño al impacto real.</p>
        <a href="contactanos.php" class="cta">Habla con nosotros</a>
    </section>
  </main>

  <script src="config.js"></script>

<?php include 'footer.php'; ?>

