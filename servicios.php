<?php
$page_title = "Silvex Estudio | Servicios";
$current_page = "servicios";
$hero_class = "hero--page hero--long-bg";
include 'header.php';
?>

    <section class="page-panel page-panel--services">
      <h1>¿Qué hacemos?</h1>
      <p>
        En Silvex nos dedicamos a crear estrategias de publicidad y marketing que
        combinan creatividad, diseño y tecnología para ayudar a las marcas a
        crecer y conectar con su público. Lo que nos hace diferentes es nuestra
        capacidad de unir velocidad y calidad, ofreciendo soluciones innovadoras
        y medibles que no solo llaman la atención, sino que también generan
        resultados reales.
      </p>
    </section>

    <section class="plans-section" aria-label="Planes de servicios">
      <h2>Planes</h2>
      <div class="plans-grid">
        <button
          type="button"
          class="plan-card"
          data-plan-open
          data-plan-title="PLAN LANZAMIENTO"
          data-plan-price="$180.000 COP"
          data-plan-body="Este plan está diseñado para emprendimientos que están iniciando y necesitan comenzar a construir presencia digital de forma profesional. Incluye la creación de 5 piezas publicitarias estratégicas para redes sociales, acompañadas de redacción persuasiva (copywriting) enfocada en atraer clientes y generar interacción. Además, se desarrolla un calendario de publicación mensual que organiza el contenido de manera coherente y alineada con los objetivos de la marca. Se incluye una asesoría inicial donde analizamos el negocio, el público objetivo y las oportunidades de comunicación, permitiendo que cada publicación tenga intención estratégica y no sea solo contenido visual. Ideal para comenzar a posicionarse en el mercado con una base sólida."
        >
          <span class="plan-card__title">Plan Lanzamiento</span>
          <span class="plan-card__price">$180.000 COP</span>
          <span class="plan-card__subtitle">Ideal para emprendimientos que están empezando.</span>
          <span class="plan-card__meta">5 piezas + copys estratégicos</span>
        </button>

        <button
          type="button"
          class="plan-card"
          data-plan-open
          data-plan-title="PLAN CRECIMIENTO"
          data-plan-price="$350.000 COP"
          data-plan-body="Este plan está pensado para marcas que ya tienen presencia digital, pero desean aumentar su alcance y profesionalizar su comunicación publicitaria. Incluye el diseño de 10 piezas publicitarias mensuales, desarrolladas bajo una mini campaña temática que mantiene coherencia visual y conceptual. Se construye una estrategia de contenido enfocada en mejorar el engagement, fortalecer la identidad de marca y aumentar la conexión con el público. También se realiza una optimización del perfil digital (biografía, descripción estratégica y enfoque comunicativo) y una reunión estratégica mensual para evaluar avances y ajustar acciones. Este plan permite escalar la presencia digital con mayor impacto y consistencia."
        >
          <span class="plan-card__title">Plan Crecimiento</span>
          <span class="plan-card__price">$350.000 COP</span>
          <span class="plan-card__subtitle">Para marcas que quieren aumentar alcance y profesionalizar su imagen.</span>
          <span class="plan-card__meta">10 piezas + estrategia mensual</span>
        </button>

        <button
          type="button"
          class="plan-card"
          data-plan-open
          data-plan-title="PLAN POSICIONAMIENTO"
          data-plan-price="$600.000 COP"
          data-plan-body="Diseñado para marcas que buscan diferenciarse en el mercado y obtener resultados medibles. Incluye 15 piezas publicitarias desarrolladas dentro de una campaña estratégica completa, con objetivos claros como reconocimiento de marca, generación de leads o incremento de ventas. Se incorpora gestión básica de anuncios pagos (sin incluir el presupuesto de pauta), donde se orienta la segmentación adecuada del público. Además, se realiza análisis de métricas para evaluar el rendimiento de las publicaciones y se plantea una estrategia de crecimiento personalizada basada en datos. Este plan combina creatividad con estrategia y análisis para lograr impacto real."
        >
          <span class="plan-card__title">Plan Posicionamiento</span>
          <span class="plan-card__price">$600.000 COP</span>
          <span class="plan-card__subtitle">Para marcas que buscan impacto real y resultados medibles.</span>
          <span class="plan-card__meta">15 piezas + métricas + pauta básica</span>
        </button>

        <button
          type="button"
          class="plan-card"
          data-plan-open
          data-plan-title="PLAN IMPACTO PREMIUM"
          data-plan-price="$950.000 COP"
          data-plan-body="Es el plan más completo y estratégico, enfocado en empresas que desean consolidar su presencia en el mercado. Incluye una campaña publicitaria integral que abarca 20 piezas gráficas mensuales, estrategia avanzada de anuncios digitales, análisis constante de resultados y optimización de campañas para mejorar el rendimiento. Se brinda acompañamiento estratégico continuo, permitiendo ajustes en tiempo real según el comportamiento del público y los objetivos comerciales de la marca. Este plan está orientado a marcas que buscan crecimiento sostenido, posicionamiento fuerte y resultados medibles a corto y mediano plazo."
        >
          <span class="plan-card__title">Plan Impacto Premium</span>
          <span class="plan-card__price">$950.000 COP</span>
          <span class="plan-card__subtitle">Para empresas que quieren una presencia sólida y profesional.</span>
          <span class="plan-card__meta">20 piezas + campaña integral</span>
        </button>
      </div>
    </section>

    <div class="plan-modal" id="plan-modal" aria-hidden="true">
      <div class="plan-modal__backdrop" data-plan-close></div>
      <div class="plan-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="plan-modal-title">
        <button class="plan-modal__close" type="button" aria-label="Cerrar" data-plan-close>×</button>
        <p class="plan-modal__eyebrow">Detalle del plan</p>
        <h3 id="plan-modal-title"></h3>
        <p class="plan-modal__price" id="plan-modal-price"></p>
        <p class="plan-modal__body" id="plan-modal-body"></p>
      </div>
    </div>
  </main>

  <script>
    (function () {
      const modal = document.getElementById("plan-modal");
      if (!modal) return;
      const titleEl = document.getElementById("plan-modal-title");
      const priceEl = document.getElementById("plan-modal-price");
      const bodyEl = document.getElementById("plan-modal-body");
      const closeButtons = modal.querySelectorAll("[data-plan-close]");

      function openPlan(card) {
        if (!card) return;
        titleEl.textContent = card.dataset.planTitle || "";
        priceEl.textContent = card.dataset.planPrice || "";
        bodyEl.textContent = card.dataset.planBody || "";
        modal.classList.add("is-open");
        modal.setAttribute("aria-hidden", "false");
        document.body.classList.add("plan-modal-open");
        modal.scrollTop = 0;
        const dialog = modal.querySelector(".plan-modal__dialog");
        if (dialog) dialog.scrollTop = 0;
      }

      function closePlan() {
        modal.classList.remove("is-open");
        modal.setAttribute("aria-hidden", "true");
        document.body.classList.remove("plan-modal-open");
        modal.scrollTop = 0;
      }

      document.addEventListener("click", (e) => {
        const card = e.target.closest("[data-plan-open]");
        if (card) {
          openPlan(card);
        }
      });

      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closePlan();
      });

      closeButtons.forEach((btn) => btn.addEventListener("click", closePlan));
    })();
  </script>

<?php include 'footer.php'; ?>
