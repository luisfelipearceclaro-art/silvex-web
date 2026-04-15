(function () {
  // Silvex Virtual Advisor (frontend only, no backend/APIs)
  // Mantiene compatibilidad con el HTML actual.

  const BRAND = {
    name: "Silvex Estudio",
    phone: "3133423327",
    email: "luisdavid.arcecortes@gmail.com",
    instagram: "https://www.instagram.com/silvex_estudio/",
  };
  const AVATAR_SRC = "assets/images/buho-silvex.png";

  const LEADS_KEY = "silvex_assistant_leads_v2";
  const CHAT_KEY = "silvex_assistant_chat_v2";

  const PLANS = [
    {
      id: "lanzamiento",
      name: "Plan Lanzamiento",
      price: 180000,
      priceLabel: "$180.000 COP",
      summary: "Ideal para emprendimientos que están iniciando presencia digital.",
    },
    {
      id: "crecimiento",
      name: "Plan Crecimiento",
      price: 350000,
      priceLabel: "$350.000 COP",
      summary: "Para marcas con presencia inicial que quieren crecer con consistencia.",
    },
    {
      id: "posicionamiento",
      name: "Plan Posicionamiento",
      price: 600000,
      priceLabel: "$600.000 COP",
      summary: "Para marcas que buscan resultados medibles y diferenciación.",
    },
    {
      id: "impacto-premium",
      name: "Plan Impacto Premium",
      price: 950000,
      priceLabel: "$950.000 COP",
      summary: "Plan integral para consolidar marca y escalar con estrategia avanzada.",
    },
  ];

  const QUICK_BY_STAGE = {
    welcome: ["Quiero una recomendación", "Quiero cotizar", "Ver planes"],
    discovery: ["Aumentar ventas", "Mejorar redes", "Generar leads"],
    recommendation: ["Quiero este plan", "Comparar otro plan", "Agendar llamada"],
    capture_name: ["Prefiero WhatsApp", "Quiero solo información"],
    capture_contact: ["Mi WhatsApp es...", "Mi correo es..."],
    done: ["Nueva consulta", "Ver contacto", "Ir a Instagram"],
  };

  function normalize(text) {
    return (text || "")
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
  }

  function saveLead(lead) {
    const leads = JSON.parse(localStorage.getItem(LEADS_KEY) || "[]");
    leads.push({ ...lead, createdAt: new Date().toISOString() });
    localStorage.setItem(LEADS_KEY, JSON.stringify(leads));
  }

  function saveChat(messages) {
    localStorage.setItem(CHAT_KEY, JSON.stringify(messages.slice(-40)));
  }

  function loadChat() {
    try {
      return JSON.parse(localStorage.getItem(CHAT_KEY) || "[]");
    } catch {
      return [];
    }
  }

  function parseBudget(text) {
    const clean = normalize(text).replace(/\./g, "");
    const m = clean.match(/\$?\s?(\d{2,7})/);
    if (!m) return null;
    const value = Number(m[1]);
    if (!Number.isFinite(value)) return null;
    return value;
  }

  function pickPlan(state, text) {
    const n = normalize(text);
    const budget = state.lead.budget || parseBudget(text);
    if (budget) state.lead.budget = budget;

    if (/premium|empresa grande|alto impacto|escala/.test(n)) return PLANS[3];
    if (/resultados medibles|posicionamiento|anuncios|metricas|metricas/.test(n)) return PLANS[2];
    if (/crecer|profesionalizar|alcance|engagement/.test(n)) return PLANS[1];
    if (/emprend|empez|inici|nuevo/.test(n)) return PLANS[0];

    if (budget) {
      if (budget <= 220000) return PLANS[0];
      if (budget <= 420000) return PLANS[1];
      if (budget <= 750000) return PLANS[2];
      return PLANS[3];
    }

    return PLANS[1];
  }

  function extractContact(text) {
    const emailMatch = text.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
    const phoneMatch = text.match(/(?:\+?57)?\s?(\d{10})/);
    return {
      email: emailMatch ? emailMatch[0] : null,
      phone: phoneMatch ? phoneMatch[1] : null,
    };
  }

  function createWhatsAppUrl(state) {
    const planName = state.recommendedPlan ? state.recommendedPlan.name : "un plan";
    const name = state.lead.name || "Cliente";
    const objective = state.lead.objective || "potenciar mi marca";
    const message =
      `Hola Silvex, soy ${name}. ` +
      `Quiero avanzar con ${planName}. ` +
      `Objetivo: ${objective}.`;
    return `https://wa.me/57${BRAND.phone}?text=${encodeURIComponent(message)}`;
  }

  function getCommonAnswer(text) {
    const n = normalize(text);
    if (/(hola|buenas|hey)/.test(n)) {
      return "Hola. Soy el asesor virtual de Silvex. Te ayudo a elegir el plan ideal para tu marca y avanzar a cotización.";
    }
    if (/(como te llamas|quien eres|tu nombre|nombre del asistente|como se llama)/.test(n)) {
      return "Me llamo SIVARO, el asesor virtual comercial de Silvex Estudio.";
    }
    if (/(en que pais|de que pais|donde esta silvex|donde queda silvex|pais de silvex|pa[í]s de silvex)/.test(n)) {
      return "Silvex está en Colombia y atiende principalmente por canales digitales.";
    }
    if (/(que es silvex|que hacen|a que se dedican|de que trata silvex)/.test(n)) {
      return "Silvex es una agencia creativa y estratégica de publicidad y marketing. Diseñamos campañas con enfoque en resultados reales.";
    }
    if (/(trabajan con|tipo de clientes|a quien ayudan|publico objetivo)/.test(n)) {
      return "Trabajamos con emprendedores, pymes y marcas en crecimiento que buscan mejorar su presencia digital, captar clientes y vender más.";
    }
    if (/(cuanto tiempo llevan|experiencia|trayectoria)/.test(n)) {
      return "Silvex está enfocado en construir procesos estratégicos de marca con enfoque profesional, creativo y comercial.";
    }
    if (/(como trabajan|metodologia|metodología|proceso)/.test(n)) {
      return "Trabajamos con diagnóstico inicial, propuesta estratégica, ejecución creativa y seguimiento de resultados para optimizar el impacto.";
    }
    if (/(atienden fuera de colombia|atienden en otros paises|atienden internacional)/.test(n)) {
      return "Sí, Silvex puede atender proyectos digitales en otros países, coordinando por WhatsApp, correo y reuniones virtuales.";
    }
    if (/(que incluyen los planes|que incluye cada plan|diferencia entre planes)/.test(n)) {
      return "Los planes se diferencian por nivel estratégico, cantidad de piezas y profundidad de acompañamiento. Si me dices tu objetivo, te recomiendo el más conveniente.";
    }
    if (/(con que redes trabajan|instagram|facebook|tiktok|redes sociales)/.test(n)) {
      return "Podemos trabajar estrategias para redes sociales como Instagram, Facebook y otras plataformas según el objetivo de tu marca.";
    }
    if (/(pueden ayudarme a vender|quiero vender mas|aumentar ventas)/.test(n)) {
      return "Sí. Podemos orientarte con una estrategia enfocada en ventas, contenidos de conversión y plan publicitario según tu presupuesto.";
    }
    if (/(contacto|telefono|whatsapp|correo|email|instagram)/.test(n)) {
      return `Contacto Silvex: WhatsApp ${BRAND.phone}, correo ${BRAND.email}, Instagram @silvex_estudio.`;
    }
    if (/(mision|vision|quienes son|marca|silvex)/.test(n)) {
      return "Silvex es una agencia creativa que une elegancia y rapidez para lograr campañas con impacto real y resultados medibles.";
    }
    if (/(gracias)/.test(n)) {
      return "Con gusto. Si quieres, te recomiendo un plan y te dejo listo el mensaje para WhatsApp.";
    }
    return null;
  }

  function nextByState(state, input) {
    const text = (input || "").trim();
    const n = normalize(text);

    const common = getCommonAnswer(text);
    if (common && state.stage === "welcome") {
      return { reply: common, stage: "discovery" };
    }

    if (/ver planes/.test(n)) {
      return {
        reply:
          "Tenemos 4 planes: Lanzamiento ($180k), Crecimiento ($350k), Posicionamiento ($600k) e Impacto Premium ($950k). ¿Cuál es tu objetivo principal o presupuesto?",
        stage: "discovery",
      };
    }

    if (/cotiz|recomend|plan|ventas|leads|instagram|redes|publicidad/.test(n) && state.stage === "welcome") {
      return {
        reply:
          "Excelente. Para recomendarte con precisión, cuéntame: 1) tipo de negocio, 2) objetivo principal y 3) presupuesto aproximado.",
        stage: "discovery",
      };
    }

    if (state.stage === "discovery") {
      if (!state.lead.businessType) state.lead.businessType = text;
      if (/venta|vender/.test(n)) state.lead.objective = "Aumentar ventas";
      if (/lead/.test(n)) state.lead.objective = "Generar leads";
      if (/marca|posicion/.test(n)) state.lead.objective = "Posicionamiento de marca";

      const plan = pickPlan(state, text);
      state.recommendedPlan = plan;

      return {
        reply:
          `Te recomiendo ${plan.name} (${plan.priceLabel}). ${plan.summary} ` +
          `Si quieres avanzar, te pido tu nombre y luego tu WhatsApp/correo para enviarte el siguiente paso.`,
        stage: "capture_name",
      };
    }

    if (state.stage === "capture_name") {
      if (text.length >= 2 && !/\d/.test(text)) {
        state.lead.name = text;
        return {
          reply: `Perfecto, ${state.lead.name}. Ahora compárteme tu WhatsApp o correo para continuar.`,
          stage: "capture_contact",
        };
      }
      return {
        reply: "Para continuar necesito tu nombre (solo nombre está bien).",
        stage: "capture_name",
      };
    }

    if (state.stage === "capture_contact") {
      const contact = extractContact(text);
      if (!contact.email && !contact.phone) {
        return {
          reply: "Necesito un dato de contacto válido: WhatsApp (10 dígitos) o correo.",
          stage: "capture_contact",
        };
      }

      state.lead.email = contact.email || state.lead.email || null;
      state.lead.phone = contact.phone || state.lead.phone || null;
      saveLead({
        source: "web-assistant",
        name: state.lead.name || null,
        email: state.lead.email,
        phone: state.lead.phone,
        objective: state.lead.objective || null,
        businessType: state.lead.businessType || null,
        recommendedPlan: state.recommendedPlan ? state.recommendedPlan.name : null,
      });

      return {
        reply:
          "Excelente. Ya registré tu solicitud. Te dejo un botón para abrir WhatsApp con el mensaje listo y continuar más rápido.",
        stage: "done",
      };
    }

    if (state.stage === "done") {
      if (/nueva|otra|consulta/.test(n)) {
        state.stage = "discovery";
        return {
          reply: "Perfecto. Cuéntame el nuevo objetivo o presupuesto y te recomiendo el plan ideal.",
          stage: "discovery",
        };
      }
      if (/instagram/.test(n)) {
        return {
          reply: `Aquí tienes el perfil de Instagram: ${BRAND.instagram}`,
          stage: "done",
        };
      }
      return {
        reply: "Si quieres, también puedo armarte una recomendación alternativa de plan según otro presupuesto.",
        stage: "done",
      };
    }

    if (common) return { reply: common, stage: state.stage };
    return {
      reply: "Puedo ayudarte a recomendarte el mejor plan según tu objetivo y presupuesto. ¿Quieres que empecemos?",
      stage: "discovery",
    };
  }

  function createMessageEl(role, text) {
    const item = document.createElement("div");
    // Cambiado para que coincida con el CSS inyectado abajo (línea 635 y 650)
    item.className = role === "assistant" ? "assistant-message" : "user-message";
    
    if (role === "assistant") {
      const avatar = document.createElement("img");
      avatar.className = "svx-chat__msg-avatar";
      avatar.src = AVATAR_SRC;
      avatar.alt = "Asistente Silvex";
      const content = document.createElement("span");
      content.textContent = text;
      item.appendChild(avatar);
      item.appendChild(content);
    } else {
      item.textContent = text;
    }
    return item;
  }

  function createWhatsAppCta(url) {
    const wrap = document.createElement("div");
    wrap.className = "svx-chat__cta-wrap";
    const a = document.createElement("a");
    a.className = "svx-chat__cta-whatsapp";
    a.href = url;
    a.target = "_blank";
    a.rel = "noopener noreferrer";
    a.textContent = "Continuar por WhatsApp";
    wrap.appendChild(a);
    return wrap;
  }

  function initChat(root, { fixed = false } = {}) {
    const thread = root.querySelector("[data-chat-thread]");
    const form = root.querySelector("[data-chat-form]");
    const input = root.querySelector("[data-chat-input]");
    const toggle = root.querySelector("[data-chat-toggle]");
    const closeButton = root.querySelector("[data-chat-close]");
    const panel = root.querySelector("[data-chat-panel]");
    const quickWrap = root.querySelector(".svx-chat__quick");

    let messages = fixed ? [] : loadChat();
    let typingEl = null;
    let typingTimer = null;

    const state = {
      stage: "welcome",
      lead: {
        name: null,
        email: null,
        phone: null,
        objective: null,
        budget: null,
        businessType: null,
      },
      recommendedPlan: null,
    };

    function renderQuickButtons() {
      if (!quickWrap) return;
      quickWrap.innerHTML = "";
      const options = QUICK_BY_STAGE[state.stage] || QUICK_BY_STAGE.welcome;
      options.forEach((label) => {
        const btn = document.createElement("button");
        btn.type = "button";
        btn.dataset.chatQuick = label;
        btn.textContent = label;
        btn.addEventListener("click", () => send(label));
        quickWrap.appendChild(btn);
      });
    }

    function render() {
      thread.innerHTML = "";
      const list = messages.length
        ? messages
        : [
            {
              role: "assistant",
              text: "Hola. Soy el asesor virtual de Silvex. Te ayudo a elegir el plan ideal para tu marca.",
            },
          ];

      list.forEach((m) => thread.appendChild(createMessageEl(m.role, m.text)));
      if (typingEl) thread.appendChild(typingEl);
      if (state.stage === "done" && state.recommendedPlan) {
        thread.appendChild(createWhatsAppCta(createWhatsAppUrl(state)));
      }
      thread.scrollTop = thread.scrollHeight;
      renderQuickButtons();
      if (!fixed) saveChat(messages);
    }

    function setThinking(active) {
      if (active) {
        typingEl = document.createElement("div");
        typingEl.className = "svx-chat__msg svx-chat__msg--assistant svx-chat__msg--thinking";
        const avatar = document.createElement("img");
        avatar.className = "svx-chat__msg-avatar";
        avatar.src = AVATAR_SRC;
        avatar.alt = "Asistente Silvex";
        const text = document.createElement("span");
        text.textContent = "Pensando";
        typingEl.appendChild(avatar);
        typingEl.appendChild(text);
      } else {
        typingEl = null;
      }
      render();
    }

    function typeAssistantMessage(fullText, onDone) {
      const msg = { role: "assistant", text: "" };
      messages.push(msg);
      // Forzar render para mostrar la burbuja vacía antes de escribir
      render();

      const chars = Array.from(fullText);
      let i = 0;
      const speed = 10; // Un poco más rápido
      typingTimer = window.setInterval(() => {
        i += 1;
        msg.text = chars.slice(0, i).join("");
        render();
        if (i >= chars.length) {
          window.clearInterval(typingTimer);
          typingTimer = null;
          if (onDone) onDone();
        }
      }, speed);
    }

    function getApiCandidates() {
      const candidates = [];
      if (window.SILVEX_API_BASE) candidates.push(window.SILVEX_API_BASE);
      if (window.location.protocol === "http:" || window.location.protocol === "https:") {
        candidates.push(window.location.origin);
      }
      candidates.push("http://localhost:3034", "http://127.0.0.1:3034", "http://localhost:3002", "http://127.0.0.1:3002");
      return [...new Set(candidates)];
    }

    async function requestChatFromApi(payload) {
      const apiCandidates = getApiCandidates();
      let lastErr = "";

      for (const base of apiCandidates) {
        try {
          const response = await fetch(`${base}/api/chat`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            mode: "cors",
            body: JSON.stringify(payload),
            signal: AbortSignal.timeout(5000) // Timeout de 5s para evitar esperas infinitas
          });

          const data = await response.json().catch(() => ({}));
          if (!response.ok) {
            lastErr = data.error || `Error HTTP ${response.status}`;
            continue;
          }

          if (!data.reply || typeof data.reply !== "string") {
            lastErr = "La API no devolvio una respuesta valida.";
            continue;
          }

          return data;
        } catch (_) {
          // try next endpoint
        }
      }

      throw new Error(lastErr || "No se pudo establecer conexión con el servidor IA. Asegúrate de que el backend (Node.js) esté corriendo.");
    }

    async function send(text) {
      const cleaned = (text || "").trim();
      if (!cleaned) return;

      if (typingTimer) {
        window.clearInterval(typingTimer);
        typingTimer = null;
      }

      const historyToSend = messages.map(m => ({ role: m.role, text: m.text }));

      messages.push({ role: "user", text: cleaned });
      render();
      setThinking(true);

      try {
        // Obtener o crear un ID numÃ©rico Ãºnico para el visitante
        let visitorId = localStorage.getItem("silvex_visitor_id");
        if (!visitorId) {
          visitorId = Math.floor(Math.random() * 10000).toString();
          localStorage.setItem("silvex_visitor_id", visitorId);
        }

        const data = await requestChatFromApi({
          message: cleaned,
          history: historyToSend,
          visitorId: visitorId
        });
        setThinking(false);
        
        typeAssistantMessage(data.reply, () => render());
      } catch (err) {
        console.error("Chat API Error:", err);
        setThinking(false);
        typeAssistantMessage("No se pudo conectar con Sivaro (IA). Asegúrate de que el servidor de Node.js esté corriendo en el puerto 3034.", () => render());
      }
    }

    form.addEventListener("submit", (e) => {
      e.preventDefault();
      send(input.value);
      input.value = "";
      input.focus();
    });

    if (toggle && panel) {
      toggle.addEventListener("click", () => {
        root.classList.toggle("is-open");
      });
    }

    if (closeButton) {
      closeButton.addEventListener("click", () => {
        root.classList.remove("is-open");
      });
    }

    render();
  }

  function buildWidgetMarkup() {
    const wrapper = document.createElement("section");
    wrapper.className = "svx-chat-widget";
    wrapper.setAttribute("aria-label", "Asistente virtual Silvex");
    wrapper.innerHTML = `
      <div class="svx-chat-widget__panel" data-chat-panel>
        <div class="svx-chat__header">
          <img class="svx-chat__avatar" src="${AVATAR_SRC}" alt="Asistente Silvex">
          <div>
            <strong>Asesor Virtual Silvex</strong>
            <p>Estrategia comercial para tu marca</p>
          </div>
          <button type="button" class="svx-chat__close" data-chat-close aria-label="Cerrar chat">Ã—</button>
        </div>
        <div class="svx-chat__quick"></div>
        <div class="svx-chat__thread" data-chat-thread></div>
        <form class="svx-chat__form" data-chat-form>
          <input type="text" placeholder="Cuéntame tu objetivo..." data-chat-input>
          <button type="submit">Enviar</button>
        </form>
      </div>
    `;
    return wrapper;
  }

  function initFloatingWidget() {
    const widget = buildWidgetMarkup();
    document.body.appendChild(widget);
    initChat(widget, { fixed: false });
    return widget;
  }

  function initFixedAssistant() {
    const host = document.querySelector("[data-silvex-assistant-fixed]");
    if (!host) return;
    host.innerHTML = `
      <section class="svx-chat-fixed" aria-label="Asistente virtual Silvex">
        <div class="svx-chat__header svx-chat__header--fixed">
          <img class="svx-chat__avatar" src="${AVATAR_SRC}" alt="Asistente Silvex">
          <div>
            <strong>Asesor Virtual Silvex</strong>
            <p>Recomendación de planes y conversión comercial</p>
          </div>
        </div>
        <div class="svx-chat__quick"></div>
        <div class="svx-chat__thread" data-chat-thread></div>
        <form class="svx-chat__form" data-chat-form>
          <input type="text" placeholder="Pregunta o describe tu negocio..." data-chat-input>
          <button type="submit">Enviar</button>
        </form>
      </section>
    `;
    initChat(host, { fixed: true });
  }

  function initFullscreenAssistant() {
    const host = document.querySelector("[data-silvex-assistant-full]");
    if (!host) return;
    host.innerHTML = `
      <section class="svx-chat-fullscreen" aria-label="Asistente virtual Silvex">
        <div class="svx-chat__header svx-chat__header--fixed">
          <img class="svx-chat__avatar" src="${AVATAR_SRC}" alt="Asistente Silvex">
          <div>
            <strong>Asesor Virtual Silvex</strong>
            <p>Chat comercial en pantalla completa</p>
          </div>
        </div>
        <div class="svx-chat__quick"></div>
        <div class="svx-chat__thread" data-chat-thread></div>
        <form class="svx-chat__form" data-chat-form>
          <input type="text" placeholder="Pregunta o describe tu negocio..." data-chat-input>
          <button type="submit">Enviar</button>
        </form>
      </section>
    `;
    initChat(host, { fixed: false });
  }

  document.addEventListener("DOMContentLoaded", () => {
    const hasFixedAssistant = Boolean(document.querySelector("[data-silvex-assistant-fixed]"));
    const hasFullscreenAssistant = Boolean(document.querySelector("[data-silvex-assistant-full]"));

    let floatingWidget = null;
    if (!hasFixedAssistant && !hasFullscreenAssistant) {
      floatingWidget = initFloatingWidget();
    }
    initFixedAssistant();
    initFullscreenAssistant();

    const owlTriggers = document.querySelectorAll("[data-open-chat]");
    owlTriggers.forEach((trigger) => {
      trigger.addEventListener("click", () => {
        if (!floatingWidget) return;
        const isOpen = floatingWidget.classList.contains("is-open");
        floatingWidget.classList.toggle("is-open");
        const input = floatingWidget.querySelector("[data-chat-input]");
        if (!isOpen && input) input.focus();
      });
    });
  });
})();
