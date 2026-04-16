import express from "express";
import cors from "cors";
import dotenv from "dotenv";
import fs from "fs";
import path from "path";
import { google } from "googleapis";

dotenv.config();

const app = express();
const rawAllowedOrigins = String(process.env.CORS_ALLOWED_ORIGINS || "");
const allowedOrigins = rawAllowedOrigins
  .split(",")
  .map((item) => item.trim())
  .filter(Boolean);
const defaultAllowedOrigins = [
  "http://localhost",
  "http://127.0.0.1",
  "http://localhost:3000",
  "http://127.0.0.1:3000",
  "http://localhost:3001",
  "http://127.0.0.1:3001",
  "http://localhost:3002",
  "http://127.0.0.1:3002",
  "http://localhost:3034",
  "http://127.0.0.1:3034"
];
const corsAllowlist = new Set(allowedOrigins.length ? allowedOrigins : defaultAllowedOrigins);
app.use(
  cors({
    origin(origin, callback) {
      if (!origin) return callback(null, true);
      if (corsAllowlist.has(origin)) return callback(null, true);
      return callback(new Error("CORS origin blocked"));
    }
  })
);
app.use(express.json({ limit: "1mb" }));
app.use((req, res, next) => {
  res.setHeader("X-Content-Type-Options", "nosniff");
  res.setHeader("X-Frame-Options", "DENY");
  res.setHeader("Referrer-Policy", "strict-origin-when-cross-origin");
  next();
});

const port = Number(process.env.PORT || 3001);
const geminiApiKey = process.env.GEMINI_API_KEY || "";

const brandContext = `
Eres el asistente comercial premium de Silvex Estudio (agencia de publicidad y marketing).

Objetivo principal:
- Priorizar ventas y captacion de leads.
- Resolver dudas y convertir conversaciones en contacto comercial.

Contacto:
- WhatsApp/Telefono: 3133423327
- Correo: luisdavid.arcecortes@gmail.com
- Instagram: @silvex_estudio

Reglas:
- Nunca inventes informacion no confirmada.
- Responde siempre en espanol.
`;



const zoomMeetingUrl = process.env.ZOOM_MEETING_URL || "";
const calendarId = process.env.GOOGLE_CALENDAR_ID || "";
const calendarClientEmail = process.env.GOOGLE_SERVICE_ACCOUNT_EMAIL || "";
const calendarPrivateKeyRaw = process.env.GOOGLE_PRIVATE_KEY || "";
const calendarPrivateKey = calendarPrivateKeyRaw.replace(/\\n/g, "\n");

// Webhook de automatizacion para Instagram (ManyChat / Make / Zapier / Meta flow)
const igNotifyWebhookUrl = process.env.IG_NOTIFY_WEBHOOK_URL || "";
const igNotifyWebhookToken = process.env.IG_NOTIFY_WEBHOOK_TOKEN || "";

// Integración Telegram Bot
const telegramBotToken = process.env.TELEGRAM_BOT_TOKEN || "";
const telegramChatId = process.env.TELEGRAM_CHAT_ID || "";
const isPublicAgendaEnabled = String(process.env.PUBLIC_MEETINGS_AGENDA || "true").toLowerCase() === "true";

const rateStore = new Map();
function isRateLimited(req, bucket, { windowMs, maxHits }) {
  const now = Date.now();
  const ip = String(req.headers["x-forwarded-for"] || req.socket.remoteAddress || "unknown")
    .split(",")[0]
    .trim();
  const key = `${bucket}:${ip}`;
  const entry = rateStore.get(key);
  if (!entry || now > entry.resetAt) {
    rateStore.set(key, { count: 1, resetAt: now + windowMs });
    return false;
  }
  entry.count += 1;
  if (entry.count > maxHits) return true;
  return false;
}

// Persistencia en JSON local
const DATA_FILE = path.join(process.cwd(), "data", "meetings.json");
if (!fs.existsSync(path.dirname(DATA_FILE))) {
  fs.mkdirSync(path.dirname(DATA_FILE), { recursive: true });
}
if (!fs.existsSync(DATA_FILE)) {
  fs.writeFileSync(DATA_FILE, "[]", "utf-8");
}

function getMeetings() {
  try {
    const data = fs.readFileSync(DATA_FILE, "utf-8");
    return JSON.parse(data);
  } catch (error) {
    console.error("Error reading meetings.json:", error);
    return [];
  }
}

function isSameMeetingSlot(a, b) {
  return a.meetingDate === b.meetingDate && a.meetingTime === b.meetingTime;
}

// Persistencia de Leads
const LEADS_FILE = path.join(process.cwd(), "data", "leads.json");
if (!fs.existsSync(LEADS_FILE)) {
  fs.writeFileSync(LEADS_FILE, "[]", "utf-8");
}

function getLeads() {
  try {
    const data = fs.readFileSync(LEADS_FILE, "utf-8");
    return JSON.parse(data);
  } catch (error) {
    return [];
  }
}

function saveMeetingIfSlotAvailable(meeting) {
  const meetings = getMeetings();
  const takenSlot = meetings.find((item) => isSameMeetingSlot(item, meeting));
  if (takenSlot) {
    return { saved: false, reason: "slot_taken" };
  }

  const storedMeeting = {
    ...meeting,
    id: Date.now().toString(),
    createdAt: new Date().toISOString()
  };

  meetings.push(storedMeeting);
  fs.writeFileSync(DATA_FILE, JSON.stringify(meetings, null, 2), "utf-8");
  return { saved: true, meeting: storedMeeting };
}

function sanitizeMeetingPayload(payload) {
  return {
    ...payload,
    fullName: String(payload.fullName || "").trim(),
    email: String(payload.email || "").trim(),
    phone: String(payload.phone || "").trim(),
    company: String(payload.company || "").trim(),
    meetingType: String(payload.meetingType || "").trim().toLowerCase(),
    city: String(payload.city || "").trim(),
    meetingDate: String(payload.meetingDate || "").trim(),
    meetingTime: String(payload.meetingTime || "").trim(),
    reason: String(payload.reason || "").trim()
  };
}

function isWeekday(date) {
  const day = date.getDay();
  return day >= 1 && day <= 5;
}

function parseLocalDateTime(dateISO, timeHHMM) {
  const [year, month, day] = dateISO.split("-").map(Number);
  const [hour, minute] = timeHHMM.split(":").map(Number);
  return new Date(year, month - 1, day, hour, minute, 0, 0);
}

function validateMeetingPayload(payload) {
  const required = ["fullName", "email", "phone", "company", "meetingType", "meetingDate", "meetingTime", "reason"];
  for (const field of required) {
    if (!payload[field] || typeof payload[field] !== "string") {
      return `Falta campo requerido: ${field}`;
    }
  }

  if (!["virtual", "presencial"].includes(payload.meetingType)) {
    return "meetingType debe ser virtual o presencial";
  }

  const start = parseLocalDateTime(payload.meetingDate, payload.meetingTime);
  if (Number.isNaN(start.getTime())) return "Fecha u hora invalida";

  if (!isWeekday(start)) return "Solo se agenda de lunes a viernes";

  const minStart = Date.now() + 12 * 60 * 60 * 1000;
  if (start.getTime() < minStart) return "Debes agendar con minimo 12 horas de anticipacion";

  const hour = start.getHours();
  const minute = start.getMinutes();
  if (hour < 15 || hour > 17 || (hour === 17 && minute > 30) || ![0, 30].includes(minute)) {
    return "Horario permitido: 3:00 p.m. a 6:00 p.m. (bloques de 30 min)";
  }

  if (payload.meetingType === "presencial" && String(payload.city || "").trim().toLowerCase() !== "sogamoso") {
    return "Las reuniones presenciales solo estan disponibles en Sogamoso";
  }
  return null;
}

async function createCalendarEvent(payload) {
  if (!calendarId || !calendarClientEmail || !calendarPrivateKey) {
    return { eventCreated: false, reason: "calendar_not_configured" };
  }

  const start = parseLocalDateTime(payload.meetingDate, payload.meetingTime);
  const end = new Date(start.getTime() + 30 * 60 * 1000);

  const auth = new google.auth.JWT({
    email: calendarClientEmail,
    key: calendarPrivateKey,
    scopes: ["https://www.googleapis.com/auth/calendar"]
  });

  const calendar = google.calendar({ version: "v3", auth });
  const modeLabel = payload.meetingType === "virtual" ? "Virtual (Zoom)" : "Presencial (Sogamoso)";

  const event = {
    summary: `Reunion Silvex - ${payload.company}`,
    description: [
      `Cliente: ${payload.fullName}`,
      `Correo: ${payload.email}`,
      `Celular: ${payload.phone}`,
      `Empresa: ${payload.company}`,
      `Modalidad: ${modeLabel}`,
      `Ciudad: ${payload.city || "N/A"}`,
      `Motivo: ${payload.reason}`,
      zoomMeetingUrl ? `Zoom: ${zoomMeetingUrl}` : ""
    ]
      .filter(Boolean)
      .join("\n"),
    start: { dateTime: start.toISOString(), timeZone: "America/Bogota" },
    end: { dateTime: end.toISOString(), timeZone: "America/Bogota" },
    attendees: [{ email: payload.email }]
  };

  const resp = await calendar.events.insert({
    calendarId,
    requestBody: event,
    sendUpdates: "all"
  });

  return {
    eventCreated: true,
    eventId: resp.data.id || null,
    eventHtmlLink: resp.data.htmlLink || null
  };
}

async function sendInstagramNotification(payload, { zoomLink = "", calendarEventLink = "" } = {}) {
  if (!igNotifyWebhookUrl) {
    return { sent: false, reason: "instagram_webhook_not_configured" };
  }

  const modeLabel = payload.meetingType === "virtual" ? "Virtual (Zoom)" : "Presencial (Sogamoso)";
  const text = [
    "Nueva reunion agendada en Silvex",
    `Cliente: ${payload.fullName}`,
    `Empresa: ${payload.company}`,
    `Correo: ${payload.email}`,
    `Celular: ${payload.phone}`,
    `Modalidad: ${modeLabel}`,
    `Ciudad: ${payload.city || "N/A"}`,
    `Fecha/Hora: ${payload.meetingDate} ${payload.meetingTime}`,
    `Motivo: ${payload.reason}`,
    zoomLink ? `Zoom: ${zoomLink}` : "",
    calendarEventLink ? `Calendar: ${calendarEventLink}` : ""
  ]
    .filter(Boolean)
    .join("\n");

  const response = await fetch(igNotifyWebhookUrl, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      ...(igNotifyWebhookToken ? { Authorization: `Bearer ${igNotifyWebhookToken}` } : {})
    },
    body: JSON.stringify({
      channel: "instagram",
      username: "silvex_estudio",
      text,
      meeting: payload
    })
  });

  if (!response.ok) {
    const detail = await response.text().catch(() => "");
    throw new Error(`instagram_webhook_failed:${response.status}:${detail}`.slice(0, 220));
  }

  return { sent: true };
}

async function sendTelegramMessage(text) {
  if (!telegramBotToken || !telegramChatId) return;
  try {
    const url = `https://api.telegram.org/bot${telegramBotToken}/sendMessage`;
    await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        chat_id: telegramChatId,
        text: text
      })
    });
  } catch (error) {
    console.error("Error al enviar mensaje a Telegram:", error);
  }
}

app.get("/api/health", (_req, res) => {
  res.json({ ok: true, geminiConfigured: Boolean(geminiApiKey) });
});

app.post("/api/chat", async (req, res) => {
  try {
    if (isRateLimited(req, "chat", { windowMs: 60_000, maxHits: 40 })) {
      return res.status(429).json({ error: "Demasiadas solicitudes. Intenta en un minuto." });
    }

    if (!geminiApiKey) {
      return res.status(503).json({ error: "Gemini no esta configurado aun" });
    }

    const { message, history = [], visitorId = "Anon" } = req.body || {};
    if (!message || typeof message !== "string") {
      return res.status(400).json({ error: "Falta message" });
    }

    const sanitizedHistory = Array.isArray(history)
      ? history
          .slice(-12)
          .filter((m) => m && (m.role === "user" || m.role === "assistant") && typeof m.text === "string")
      : [];

    const contents = [
      { role: "user", parts: [{ text: brandContext }] },
      { role: "model", parts: [{ text: "Entendido, soy el asesor." }] }
    ];

    for (const msg of sanitizedHistory) {
      contents.push({
        role: msg.role === "assistant" ? "model" : "user",
        parts: [{ text: msg.text }]
      });
    }

    contents.push({
      role: "user",
      parts: [{ text: message }]
    });

    const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=${geminiApiKey}`;
    const aiRes = await fetch(url, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ contents })
    });

    const data = await aiRes.json();
    if (!aiRes.ok) {
       console.error("Gemini Error:", data);
       throw new Error("Respuesta fallida de Gemini API");
    }

    const reply = data.candidates?.[0]?.content?.parts?.[0]?.text || "No pude generar una respuesta en este momento.";
    
    // Notificar a Telegram
    await sendTelegramMessage(`ðŸ‘¤ *Usuario ${visitorId}:*\n${message}\n\nðŸ¤– *SIVARO (IA):*\n${reply}`);

    res.json({ reply });
  } catch (error) {
    console.error("Error /api/chat", error);
    res.status(500).json({ error: "Error al consultar la IA" });
  }
});

app.post("/api/meetings", async (req, res) => {
  try {
    if (isRateLimited(req, "meetings_post", { windowMs: 60_000, maxHits: 20 })) {
      return res.status(429).json({ error: "Demasiadas solicitudes. Intenta en un minuto." });
    }

    const payload = sanitizeMeetingPayload(req.body || {});
    const validationError = validateMeetingPayload(payload);
    if (validationError) {
      return res.status(400).json({ error: validationError });
    }

    // Persistir la reunion
    try {
      const saveResult = saveMeetingIfSlotAvailable(payload);
      if (!saveResult.saved && saveResult.reason === "slot_taken") {
        return res.status(409).json({
          error: "Este horario de reunion ya ha sido reservado. Por favor, elige otro."
        });
      }
    } catch (saveError) {
      console.error("Save error /api/meetings", saveError);
      return res.status(500).json({ error: "No se pudo guardar la reunion localmente." });
    }

    let calendarResult = { eventCreated: false, reason: "not_attempted" };
    try {
      calendarResult = await createCalendarEvent(payload);
    } catch (calendarError) {
      console.error("Calendar error /api/meetings", calendarError);
      calendarResult = { eventCreated: false, reason: "calendar_error" };
    }

    let notificationsResult = { sent: false, reason: "not_attempted" };
    try {
      notificationsResult = await sendInstagramNotification(payload, {
        zoomLink: payload.meetingType === "virtual" ? zoomMeetingUrl : "",
        calendarEventLink: calendarResult.eventHtmlLink || ""
      });
    } catch (notifyError) {
      console.error("Instagram notify error /api/meetings", notifyError);
      notificationsResult = { sent: false, reason: "instagram_api_error" };
    }

    return res.json({
      ok: true,
      zoomLink: payload.meetingType === "virtual" ? zoomMeetingUrl : "",
      calendar: calendarResult,
      notifications: notificationsResult
    });
  } catch (error) {
    console.error("Error /api/meetings", error);
    return res.status(500).json({
      error: "No se pudo crear la reunion",
      detail: error?.message || String(error)
    });
  }
});

app.get("/api/meetings", (req, res) => {
  if (isRateLimited(req, "meetings_get", { windowMs: 60_000, maxHits: 60 })) {
    return res.status(429).json({ error: "Demasiadas solicitudes. Intenta en un minuto." });
  }

  const date = req.query.date;
  if (!date) {
    return res.status(400).json({ error: "Falta el parametro date" });
  }
  if (!/^\d{4}-\d{2}-\d{2}$/.test(String(date))) {
    return res.status(400).json({ error: "Formato de fecha invalido. Usa YYYY-MM-DD" });
  }

  try {
    const meetings = getMeetings();
    const takenSlots = [...new Set(
      meetings
        .filter((m) => m.meetingDate === date)
        .map((m) => m.meetingTime)
    )].sort();

    res.json({ takenSlots });
  } catch (error) {
    console.error("GET /api/meetings error", error);
    res.status(500).json({ error: "No se pudieron obtener los horarios ocupados" });
  }
});

app.get("/api/meetings/agenda", (req, res) => {
  try {
    if (!isPublicAgendaEnabled) {
      return res.status(403).json({ error: "Agenda publica deshabilitada" });
    }
    if (isRateLimited(req, "meetings_agenda", { windowMs: 60_000, maxHits: 60 })) {
      return res.status(429).json({ error: "Demasiadas solicitudes. Intenta en un minuto." });
    }

    const meetings = getMeetings();
    
    // Solo mostrar reuniones futuras
    const now = new Date();
    now.setHours(0, 0, 0, 0);

    const agenda = meetings
      .filter((m) => {
        const [y, mm, d] = m.meetingDate.split("-").map(Number);
        const mDate = new Date(y, mm - 1, d);
        return mDate >= now;
      })
      .map((m) => {
        // Extraer iniciales (Luis David Arce -> L.D.A.)
        const initials = (m.fullName || "")
          .split(" ")
          .filter(word => word.length > 0)
          .map(word => word[0].toUpperCase() + ".")
          .join("");

        return {
          dateISO: m.meetingDate,
          time: m.meetingTime,
          initials: initials
        };
      });

    // Ordenar cronolÃ³gicamente
    agenda.sort((a, b) => {
      const cmp = a.dateISO.localeCompare(b.dateISO);
      if (cmp !== 0) return cmp;
      return a.time.localeCompare(b.time);
    });

    res.json({ agenda });
  } catch (error) {
    console.error("GET /api/meetings/agenda error", error);
    res.status(500).json({ error: "No se pudo obtener la agenda pÃºblica" });
  }
});

app.get("/api/leads", (req, res) => {
  const leads = getLeads();
  res.json({ leads });
});

app.post("/api/leads", (req, res) => {
  const leads = getLeads();
  const newLead = {
    ...req.body,
    id: Date.now().toString(),
    createdAt: new Date().toISOString()
  };
  leads.push(newLead);
  fs.writeFileSync(LEADS_FILE, JSON.stringify(leads, null, 2), "utf-8");
  res.json({ ok: true, lead: newLead });
});

app.listen(port, () => {
  console.log(`Silvex Assistant API en http://localhost:${port}`);
});

