<?php
$page_title = "Silvex Estudio | Contáctanos";
$current_page = "contactanos";
$hero_class = "hero--page hero--long-bg";
$extra_head = '<link rel="stylesheet" href="calendar.css">';
include 'header.php';
?>

    <section class="booking" aria-label="Agenda tu reunión">
      <h1 class="booking__title">Agenda tu reunión</h1>
      <p class="booking__subtitle">
        Reserva una reunión virtual (Zoom) o presencial en Sogamoso.<br>
        Duración de 30 min · Lunes a viernes · 3:00 p.m. a 6:00 p.m.
      </p>

      <!-- Step indicators -->
      <div class="steps-bar" aria-label="Progreso">
        <div class="step-indicator is-active" data-step-dot="1">
          <span class="step-indicator__num">1</span>
          <span class="step-indicator__label">Datos</span>
        </div>
        <div class="step-indicator__line"></div>
        <div class="step-indicator" data-step-dot="2">
          <span class="step-indicator__num">2</span>
          <span class="step-indicator__label">Fecha</span>
        </div>
        <div class="step-indicator__line"></div>
        <div class="step-indicator" data-step-dot="3">
          <span class="step-indicator__num">3</span>
          <span class="step-indicator__label">Hora</span>
        </div>
        <div class="step-indicator__line"></div>
        <div class="step-indicator" data-step-dot="4">
          <span class="step-indicator__num">4</span>
          <span class="step-indicator__label">Detalles</span>
        </div>
      </div>

      <!-- Existing meeting reminder -->
      <div id="existing-meeting" class="agenda-result" hidden style="margin-bottom: 2rem;">
        <h3 class="agenda-result__title">📅 Ya tienes una reunión agendada</h3>
        <p><strong>Fecha:</strong> <span id="em-date"></span></p>
        <p><strong>Motivo:</strong> <span id="em-reason"></span></p>
        <button type="button" id="em-button" class="cta cta--ghost" style="margin-top: 0.8rem;"></button>
        <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.8;">Si necesitas reprogramar, por favor contáctanos por WhatsApp.</p>
      </div>

      <form id="booking-form" novalidate>
        <!-- ─── STEP 1: Personal Info ─── -->
        <div class="step-panel is-visible" data-step="1">
          <h2 class="step-panel__heading">Tus datos</h2>
          <div class="step-fields">
            <label class="field">
              <span class="field__label">Nombre completo</span>
              <input type="text" id="fullName" name="fullName" required placeholder="Ej: María López">
            </label>
            <label class="field">
              <span class="field__label">Correo electrónico</span>
              <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
            </label>
            <label class="field">
              <span class="field__label">Celular</span>
              <input type="tel" id="phone" name="phone" required placeholder="3XX XXX XXXX">
            </label>
            <label class="field">
              <span class="field__label">Nombre de la empresa</span>
              <input type="text" id="company" name="company" required placeholder="Tu marca o negocio">
            </label>
          </div>
          <div class="step-actions">
            <button type="button" class="cta" data-next="2">Siguiente →</button>
          </div>
        </div>

        <!-- ─── STEP 2: Calendar ─── -->
        <div class="step-panel" data-step="2">
          <h2 class="step-panel__heading">Elige una fecha</h2>
          <div class="calendar" id="calendar">
            <div class="calendar__nav">
              <button type="button" class="calendar__arrow" id="cal-prev" aria-label="Mes anterior">‹</button>
              <span class="calendar__month" id="cal-month"></span>
              <button type="button" class="calendar__arrow" id="cal-next" aria-label="Mes siguiente">›</button>
            </div>
            <div class="calendar__weekdays">
              <span>Lun</span><span>Mar</span><span>Mié</span><span>Jue</span><span>Vie</span><span>Sáb</span><span>Dom</span>
            </div>
            <div class="calendar__grid" id="cal-grid"></div>
          </div>
          <input type="hidden" id="meetingDate" name="meetingDate">
          <p class="calendar__selected" id="cal-selected">Selecciona un día disponible</p>
          <div class="step-actions">
            <button type="button" class="cta cta--ghost" data-prev="1">← Atrás</button>
            <button type="button" class="cta" data-next="3" id="btn-to-step3" disabled>Siguiente →</button>
          </div>
        </div>

        <!-- ─── STEP 3: Time Slots ─── -->
        <div class="step-panel" data-step="3">
          <h2 class="step-panel__heading">Elige tu horario</h2>
          <p class="step-panel__hint" id="slots-date-label">—</p>
          <div class="slots" id="slots-grid"></div>
          <input type="hidden" id="meetingTime" name="meetingTime">
          <div class="step-actions">
            <button type="button" class="cta cta--ghost" data-prev="2">← Atrás</button>
            <button type="button" class="cta" data-next="4" id="btn-to-step4" disabled>Siguiente →</button>
          </div>
        </div>

        <!-- ─── STEP 4: Modality + Reason ─── -->
        <div class="step-panel" data-step="4">
          <h2 class="step-panel__heading">Detalles finales</h2>
          <div class="step-fields">
            <label class="field">
              <span class="field__label">Modalidad</span>
              <select id="meetingType" name="meetingType" required>
                <option value="virtual">Virtual (Zoom)</option>
                <option value="presencial">Presencial (Sogamoso)</option>
              </select>
            </label>
            <label class="field" id="city-wrap">
              <span class="field__label">Ciudad</span>
              <input type="text" id="city" name="city" placeholder="Opcional">
            </label>
            <label class="field field--full">
              <span class="field__label">Motivo de la reunión</span>
              <textarea id="reason" name="reason" rows="4" required placeholder="Cuéntanos brevemente qué necesitas..."></textarea>
            </label>
          </div>

          <!-- Summary card -->
          <div class="booking-summary" id="booking-summary">
            <p class="booking-summary__title">Resumen de tu reserva</p>
            <div class="booking-summary__row"><span>Fecha:</span> <strong id="sum-date">—</strong></div>
            <div class="booking-summary__row"><span>Hora:</span> <strong id="sum-time">—</strong></div>
            <div class="booking-summary__row"><span>Duración:</span> <strong>30 minutos</strong></div>
          </div>

          <div class="step-actions">
            <button type="button" class="cta cta--ghost" data-prev="3">← Atrás</button>
            <button type="submit" class="cta cta--submit">Reservar reunión ✓</button>
          </div>
        </div>
      </form>

      <!-- Result -->
      <div id="agenda-result" class="agenda-result" hidden></div>

      <!-- Public Agenda -->
      <div class="public-agenda" id="public-agenda" style="margin-top: 3rem; padding: 2rem; background: var(--bg-surface); border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
        <h3 style="font-family: 'Barlow Condensed'; font-size: 1.5rem; text-transform: uppercase; margin-bottom: 1.5rem; color: var(--color-gold);">Próximas Reuniones</h3>
        <div id="agenda-list" style="display: flex; flex-direction: column; gap: 1rem;">
           <p style="opacity: 0.7; font-size: 0.9rem;">Cargando agenda...</p>
        </div>
      </div>

      <div class="contact-inline-info">
        <p>WhatsApp / Teléfono: 3133423327</p>
        <p>Correo: luisdavid.arcecortes@gmail.com</p>
        <p>Instagram: @silvex_estudio</p>
        <a class="cta" href="asesor.php">Abrir asesor virtual</a>
      </div>
    </section>
  </main>

  <script src="config.js"></script>
  <script src="booking.js?v=21"></script>
  <script src="agenda.js?v=21"></script>
<?php
include 'footer.php';
?>


