(function () {
  "use strict";

  /* â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
     Silvex Booking â€“ Interactive Calendar
     â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â• */

  const form = document.getElementById("booking-form");
  if (!form) return;

  // â”€â”€â”€ Configuration â”€â”€â”€
  const SLOT_TIMES = ["15:00", "15:30", "16:00", "16:30", "17:00", "17:30"];
  const MIN_HOURS_AHEAD = 12;
  const MONTHS_ES = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
  ];
  const DAYS_SHORT = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];

  // â”€â”€â”€ DOM References â”€â”€â”€
  const dateInput = document.getElementById("meetingDate");
  const timeInput = document.getElementById("meetingTime");
  const typeSelect = document.getElementById("meetingType");
  const cityInput = document.getElementById("city");
  const resultBox = document.getElementById("agenda-result");

  const calGrid = document.getElementById("cal-grid");
  const calMonth = document.getElementById("cal-month");
  const calPrev = document.getElementById("cal-prev");
  const calNext = document.getElementById("cal-next");
  const calSelected = document.getElementById("cal-selected");

  const slotsGrid = document.getElementById("slots-grid");
  const slotsDateLabel = document.getElementById("slots-date-label");

  const btnToStep3 = document.getElementById("btn-to-step3");
  const btnToStep4 = document.getElementById("btn-to-step4");

  const sumDate = document.getElementById("sum-date");
  const sumTime = document.getElementById("sum-time");

  // â”€â”€â”€ State â”€â”€â”€
  const now = new Date();
  let viewYear = now.getFullYear();
  let viewMonth = now.getMonth();
  let selectedDate = null; // "YYYY-MM-DD"
  let selectedTime = null; // "HH:MM"
  let blockedDates = new Set(); // admin-managed blocked dates
  let occupiedSlots = []; // fetched from API per date

  // â”€â”€â”€ Utility â”€â”€â”€
  function pad2(n) { return String(n).padStart(2, "0"); }

  function toISO(y, m, d) {
    return `${y}-${pad2(m + 1)}-${pad2(d)}`;
  }

  function toLocalDateTime(dateISO, hhmm) {
    const [year, month, day] = dateISO.split("-").map(Number);
    const [hh, mm] = hhmm.split(":").map(Number);
    return new Date(year, month - 1, day, hh, mm, 0, 0);
  }

  function isWeekday(y, m, d) {
    const day = new Date(y, m, d).getDay();
    return day >= 1 && day <= 5;
  }

  function isPast(y, m, d) {
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const check = new Date(y, m, d);
    return check < today;
  }

  function isWithinAdvance(dateISO, hhmm) {
    const start = toLocalDateTime(dateISO, hhmm);
    const min = Date.now() + MIN_HOURS_AHEAD * 60 * 60 * 1000;
    return start.getTime() >= min;
  }

  function formatDateLong(dateISO) {
    const [y, m, d] = dateISO.split("-").map(Number);
    const dt = new Date(y, m - 1, d);
    return `${DAYS_SHORT[dt.getDay()]} ${d} de ${MONTHS_ES[m - 1]} ${y}`;
  }

  function formatTimeRange(hhmm) {
    const [hh, mm] = hhmm.split(":").map(Number);
    const endMm = mm + 30;
    const endHh = hh + Math.floor(endMm / 60);
    const endMmR = endMm % 60;
    return `${hh}:${pad2(mm)} â€“ ${endHh}:${pad2(endMmR)}`;
  }

  function formatTime12(hhmm) {
    const [hh, mm] = hhmm.split(":").map(Number);
    const suffix = hh >= 12 ? "p.m." : "a.m.";
    const h12 = hh > 12 ? hh - 12 : hh;
    return `${h12}:${pad2(mm)} ${suffix}`;
  }

  // â”€â”€â”€ Step Navigation â”€â”€â”€
  const stepDots = document.querySelectorAll("[data-step-dot]");
  const stepLines = document.querySelectorAll(".step-indicator__line");
  const stepPanels = document.querySelectorAll("[data-step]");

  function goToStep(n) {
    // Update panels
    stepPanels.forEach((el) => {
      el.classList.toggle("is-visible", Number(el.dataset.step) === n);
    });
    // Update dots
    stepDots.forEach((dot) => {
      const num = Number(dot.dataset.stepDot);
      dot.classList.remove("is-active", "is-done");
      if (num === n) dot.classList.add("is-active");
      else if (num < n) dot.classList.add("is-done");
    });
    // Update lines
    stepLines.forEach((line, i) => {
      line.classList.toggle("is-filled", i < n - 1);
    });
  }

  // Next/Prev buttons
  document.querySelectorAll("[data-next]").forEach((btn) => {
    btn.addEventListener("click", () => {
      const nextStep = Number(btn.dataset.next);
      // Validate step 1 before proceeding
      if (nextStep === 2 && !validateStep1()) return;
      // Update summary when entering step 4
      if (nextStep === 4) updateSummary();
      goToStep(nextStep);
    });
  });

  document.querySelectorAll("[data-prev]").forEach((btn) => {
    btn.addEventListener("click", () => {
      goToStep(Number(btn.dataset.prev));
    });
  });

  function validateStep1() {
    const fields = ["fullName", "email", "phone", "company"];
    let valid = true;
    fields.forEach((id) => {
      const el = document.getElementById(id);
      const isEmpty = !el.value.trim();
      el.classList.toggle("is-invalid", isEmpty);
      if (isEmpty) valid = false;
    });
    if (!valid) {
      // Shake effect
      const panel = document.querySelector('[data-step="1"]');
      panel.style.animation = "none";
      requestAnimationFrame(() => {
        panel.style.animation = "stepShake 400ms ease";
      });
    }
    return valid;
  }

  // â”€â”€â”€ Calendar Rendering â”€â”€â”€
  function renderCalendar() {
    calMonth.textContent = `${MONTHS_ES[viewMonth]} ${viewYear}`;
    calGrid.innerHTML = "";

    // First day of month (0=Sun)
    const firstDay = new Date(viewYear, viewMonth, 1).getDay();
    // Days in month
    const daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

    // Offset: convert Sun=0 to Mon-start grid
    const offset = (firstDay + 6) % 7;

    // Empty cells before first day
    for (let i = 0; i < offset; i++) {
      const empty = document.createElement("button");
      empty.type = "button";
      empty.className = "cal-day cal-day--empty";
      empty.disabled = true;
      calGrid.appendChild(empty);
    }

    const todayISO = toISO(now.getFullYear(), now.getMonth(), now.getDate());

    for (let d = 1; d <= daysInMonth; d++) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "cal-day";
      btn.textContent = d;

      const iso = toISO(viewYear, viewMonth, d);
      const weekend = !isWeekday(viewYear, viewMonth, d);
      const past = isPast(viewYear, viewMonth, d);
      const blocked = blockedDates.has(iso);

      if (weekend || past || blocked) {
        btn.classList.add("cal-day--disabled");
      } else {
        btn.addEventListener("click", () => selectDate(iso, d));
      }

      if (iso === todayISO) btn.classList.add("cal-day--today");
      if (iso === selectedDate) btn.classList.add("cal-day--selected");

      calGrid.appendChild(btn);
    }
  }

  function selectDate(iso, day) {
    selectedDate = iso;
    dateInput.value = iso;

    // Reset time selection
    selectedTime = null;
    timeInput.value = "";
    btnToStep4.disabled = true;

    // Update UI
    calSelected.textContent = formatDateLong(iso);
    calSelected.classList.add("is-set");
    btnToStep3.disabled = false;

    // Fetch occupied slots from API
    fetchOccupiedSlots(iso).then(() => {
      // Re-render slots to show taken times
      if (selectedDate === iso) { // Check if user hasn't clicked another date already
        renderSlots();
      }
    });

    // Re-render to show selected state
    renderCalendar();
    // Pre-build slots (will update when fetch finishes)
    renderSlots();
  }

  calPrev.addEventListener("click", () => {
    viewMonth--;
    if (viewMonth < 0) { viewMonth = 11; viewYear--; }
    renderCalendar();
  });

  calNext.addEventListener("click", () => {
    viewMonth++;
    if (viewMonth > 11) { viewMonth = 0; viewYear++; }
    renderCalendar();
  });

  // â”€â”€â”€ Time Slots â”€â”€â”€
  function renderSlots() {
    slotsGrid.innerHTML = "";
    if (!selectedDate) return;

    slotsDateLabel.textContent = formatDateLong(selectedDate);

    SLOT_TIMES.forEach((hhmm) => {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = "slot-btn";

      const available = isWithinAdvance(selectedDate, hhmm);
      const isOccupied = occupiedSlots.includes(hhmm);

      if (!available || isOccupied) {
        btn.disabled = true;
        if (isOccupied) btn.classList.add("is-occupied");
      }

      btn.innerHTML = `
        ${formatTime12(hhmm)}
        <span class="slot-btn__range">${isOccupied ? "Ocupado" : formatTimeRange(hhmm)}</span>
      `;

      if (hhmm === selectedTime && !isOccupied) btn.classList.add("is-selected");

      btn.addEventListener("click", () => selectTime(hhmm));
      slotsGrid.appendChild(btn);
    });
  }

  function selectTime(hhmm) {
    selectedTime = hhmm;
    timeInput.value = hhmm;
    btnToStep4.disabled = false;

    // Re-render to update selection
    renderSlots();
  }

  // â”€â”€â”€ Summary â”€â”€â”€
  function updateSummary() {
    sumDate.textContent = selectedDate ? formatDateLong(selectedDate) : "â€”";
    sumTime.textContent = selectedTime ? `${formatTime12(selectedTime)} (${formatTimeRange(selectedTime)})` : "â€”";
  }

  // â”€â”€â”€ City Requirement â”€â”€â”€
  function setCityRequirement() {
    const wrap = document.getElementById("city-wrap");
    if (typeSelect.value === "presencial") {
      cityInput.required = true;
      cityInput.placeholder = "Sogamoso";
      wrap.style.display = "";
    } else {
      cityInput.required = false;
      cityInput.placeholder = "Opcional";
    }
  }
  typeSelect.addEventListener("change", setCityRequirement);
  setCityRequirement();

  // â”€â”€â”€ API â”€â”€â”€
  function getApiCandidates() {
    const candidates = [];
    if (window.SILVEX_API_BASE) candidates.push(window.SILVEX_API_BASE);
    if (window.location.protocol === "http:" || window.location.protocol === "https:") {
      candidates.push(window.location.origin);
    }
    candidates.push("http://localhost:3034", "http://127.0.0.1:3034", "http://localhost:3002", "http://127.0.0.1:3002");
    return [...new Set(candidates)];
  }

  async function postMeeting(payload) {
    const apiCandidates = getApiCandidates();
    let lastErr = null;
    for (const base of apiCandidates) {
      try {
        const res = await fetch(`${base}/api/meetings`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          mode: "cors",
          body: JSON.stringify(payload),
          signal: AbortSignal.timeout(5000)
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) { lastErr = data.error || `Error HTTP ${res.status}`; continue; }
        return { data, base };
      } catch (_) { /* next */ }
    }
    if (lastErr) throw new Error(lastErr);
    throw new Error("No hay conexiÃ³n con el servidor. Enciende el backend (npm start en /server).");
  }

  async function fetchOccupiedSlots(dateISO) {
    const apiCandidates = getApiCandidates();
    for (const base of apiCandidates) {
      try {
        const res = await fetch(`${base}/api/meetings?date=${dateISO}`, { 
          mode: "cors",
          signal: AbortSignal.timeout(5000)
        });
        if (res.ok) {
          const data = await res.json();
          occupiedSlots = data.takenSlots || [];
          return;
        }
      } catch (_) { /* next */ }
    }
    occupiedSlots = []; // If we can't fetch, assume empty
  }

  function setResult(html, isError = false) {
    resultBox.hidden = false;
    resultBox.innerHTML = html;
    resultBox.style.borderColor = isError ? "rgba(255, 111, 111, 0.7)" : "rgba(46, 200, 239, 0.45)";
  }

  // â”€â”€â”€ Form Submit â”€â”€â”€
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    resultBox.hidden = true;

    const payload = {
      fullName: form.fullName.value.trim(),
      email: form.email.value.trim(),
      phone: form.phone.value.trim(),
      company: form.company.value.trim(),
      meetingType: form.meetingType.value,
      city: form.city.value.trim(),
      meetingDate: selectedDate,
      meetingTime: selectedTime,
      reason: form.reason.value.trim(),
      blockedDates: []
    };

    if (!payload.fullName || !payload.email || !payload.phone || !payload.company ||
        !payload.meetingDate || !payload.meetingTime || !payload.reason) {
      setResult("<p>Completa todos los campos obligatorios.</p>", true);
      return;
    }

    if (payload.meetingType === "presencial" && payload.city.toLowerCase() !== "sogamoso") {
      setResult("<p>Las reuniones presenciales solo estÃ¡n disponibles en Sogamoso.</p>", true);
      return;
    }

    const start = toLocalDateTime(payload.meetingDate, payload.meetingTime);
    if (!isWeekday(start.getFullYear(), start.getMonth(), start.getDate())) {
      setResult("<p>Solo puedes agendar de lunes a viernes.</p>", true);
      return;
    }

    if (!isWithinAdvance(payload.meetingDate, payload.meetingTime)) {
      setResult("<p>Debes reservar con mÃ­nimo 12 horas de anticipaciÃ³n.</p>", true);
      return;
    }

    try {
      const submitBtn = form.querySelector(".cta--submit");
      const oldText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.textContent = "Reservando...";

      const { data } = await postMeeting(payload);

      const summary = `${payload.fullName} - ${payload.company} - ${formatDateLong(payload.meetingDate)} ${formatTime12(payload.meetingTime)}`;
      const notifSent = data.notifications?.sent === true;
      const notifReason = data.notifications?.reason || "";
      let notifText = "La reuniÃ³n quedÃ³ registrada, pero la notificaciÃ³n automÃ¡tica a Instagram no estÃ¡ configurada.";
      if (notifSent) {
        notifText = "La notificaciÃ³n fue enviada automÃ¡ticamente al flujo de Instagram.";
      } else if (notifReason === "instagram_api_error") {
        notifText = "La reuniÃ³n quedÃ³ registrada, pero fallÃ³ el envÃ­o automÃ¡tico a Instagram.";
      }

      setResult(`
        <h3 class="agenda-result__title">Â¡Reserva confirmada! ðŸŽ‰</h3>
        <p><strong>Detalle:</strong> ${summary}</p>
        <p><strong>Modalidad:</strong> ${payload.meetingType === "virtual" ? "Virtual (Zoom)" : "Presencial (Sogamoso)"}</p>
        ${data.zoomLink ? `<p><strong>Enlace Zoom:</strong> <a class="agenda-link" href="${data.zoomLink}" target="_blank" rel="noreferrer">Abrir Zoom</a></p>` : ""}
        <p><strong>NotificaciÃ³n:</strong> ${notifText}</p>
      `);

      // Guardar en localStorage
      const bookingRecord = {
        dateISO: payload.meetingDate,
        time: payload.meetingTime,
        reason: payload.reason,
        timestamp: Date.now()
      };
      localStorage.setItem("silvex_booking", JSON.stringify(bookingRecord));

      // Reset form
      form.reset();
      selectedDate = null;
      selectedTime = null;
      dateInput.value = "";
      timeInput.value = "";
      calSelected.textContent = "Selecciona un dÃ­a disponible";
      calSelected.classList.remove("is-set");
      btnToStep3.disabled = true;
      btnToStep4.disabled = true;
      goToStep(1);
      renderCalendar();

      submitBtn.disabled = false;
      submitBtn.textContent = oldText;

      // Show reminder immediately
      checkExistingBooking();
    } catch (err) {
      setResult(`<p>No fue posible agendar: ${err.message}</p>`, true);
      const submitBtn = form.querySelector(".cta--submit");
      submitBtn.disabled = false;
      submitBtn.textContent = "Reservar reuniÃ³n âœ“";
    }
  });

  // â”€â”€â”€ LocalStorage Reminder â”€â”€â”€
  function checkExistingBooking() {
    try {
      const stored = localStorage.getItem("silvex_booking");
      if (!stored) return;

      const booking = JSON.parse(stored);
      if (!booking || !booking.dateISO || !booking.time) return;

      const bookingStart = toLocalDateTime(booking.dateISO, booking.time);
      if (Number.isNaN(bookingStart.getTime())) {
        localStorage.removeItem("silvex_booking");
        return;
      }

      const bookingEnd = new Date(bookingStart.getTime() + 30 * 60 * 1000);
      if (bookingEnd >= new Date()) {
        // Reunión vigente: ocultar formulario y mostrar recordatorio
        if (form) form.style.display = "none";

        const stepsBar = document.querySelector(".steps-bar");
        if (stepsBar) {
          stepsBar.style.display = "none";
          stepsBar.setAttribute("hidden", "true");
        }

        const subtitle = document.querySelector(".booking__subtitle");
        if (subtitle) {
          subtitle.style.display = "none";
          subtitle.setAttribute("hidden", "true");
        }

        const reminder = document.getElementById("existing-meeting");
        if (reminder) {
          const dateText = `${formatDateLong(booking.dateISO)} a las ${formatTime12(booking.time)}`;
          const reasonText = booking.reason || "Sin motivo especificado";
          reminder.hidden = false;
          document.getElementById("em-date").textContent = dateText;
          document.getElementById("em-reason").textContent = reasonText;

          const reminderBtn = document.getElementById("em-button");
          if (reminderBtn) {
            const shortReason = reasonText.length > 60 ? `${reasonText.slice(0, 57)}...` : reasonText;
            reminderBtn.textContent = `Reunión agendada: ${formatDateLong(booking.dateISO)} | ${shortReason}`;
          }
        }
      } else {
        // Ya pasó la reunión, limpiar storage
        localStorage.removeItem("silvex_booking");
      }
    } catch (e) {
      console.warn("Error leyendo recordatorio:", e);
    }
  }

  // â”€â”€â”€ Init â”€â”€â”€
  renderCalendar();
  checkExistingBooking();

  // Add shake animation
  const style = document.createElement("style");
  style.textContent = `
    @keyframes stepShake {
      0%, 100% { transform: translateX(0); }
      20% { transform: translateX(-6px); }
      40% { transform: translateX(6px); }
      60% { transform: translateX(-4px); }
      80% { transform: translateX(4px); }
    }
  `;
  document.head.appendChild(style);
})();

