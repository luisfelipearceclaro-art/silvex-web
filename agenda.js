document.addEventListener("DOMContentLoaded", () => {
  const agendaList = document.getElementById("agenda-list");
  if (!agendaList) return;

  function formatDateAgenda(iso) {
    const [y, m, d] = iso.split("-");
    const date = new Date(y, m - 1, d);
    return date.toLocaleDateString("es-ES", { day: "numeric", month: "long" });
  }

  function formatTimeAgenda(timeStr) {
    const [h, m] = timeStr.split(":");
    let hours = parseInt(h, 10);
    const ampm = hours >= 12 ? "p.m." : "a.m.";
    hours = hours % 12;
    hours = hours ? hours : 12;
    return `${hours}:${m} ${ampm}`;
  }

  function fetchAgenda() {
    const baseUrl = window.SILVEX_API_BASE || "http://localhost:3034";
    fetch(`${baseUrl}/api/meetings/agenda`)
      .then(res => res.json())
      .then(data => {
        if (!data.agenda || data.agenda.length === 0) {
          agendaList.innerHTML = `<p style="opacity: 0.5; font-size: 0.9rem;">Aún no hay reuniones agendadas próximas.</p>`;
          return;
        }

        agendaList.innerHTML = data.agenda.map((slot, index) => {
          const delay = index * 0.1;
          return `
            <div class="agenda-item premium-glass animate-fade-in" style="animation-delay: ${delay}s">
                <div class="agenda-badge">${slot.initials}</div>
                <div class="agenda-card-text">
                    <span class="agenda-label">Reservado para el</span>
                    <strong class="agenda-date">${formatDateAgenda(slot.dateISO)}</strong>
                    <span class="agenda-time">${formatTimeAgenda(slot.time)}</span>
                </div>
                <div class="agenda-status-tag">Agendado</div>
            </div>
          `;
        }).join("");
      })
      .catch(err => {
        console.error("Error cargando agenda:", err);
        agendaList.innerHTML = `<p style="color: #ff6b6b; font-size: 0.9rem;">No se pudo cargar la agenda en este momento.</p>`;
      });
  }

  fetchAgenda();
});
