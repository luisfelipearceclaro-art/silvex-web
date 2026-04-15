# Silvex Assistant API (OpenAI + Agenda)

## 1) Instalar dependencias
En PowerShell, dentro de esta carpeta:

```powershell
cd "c:\Users\luisd\Silvex web\server"
npm install
```

## 2) Crear archivo `.env`
Copia `server/.env.example` como `server/.env` y completa:

```env
OPENAI_API_KEY=tu_openai_api_key
PORT=3034
GOOGLE_CALENDAR_ID=tu_calendario@group.calendar.google.com
GOOGLE_SERVICE_ACCOUNT_EMAIL=tu-service-account@tu-proyecto.iam.gserviceaccount.com
GOOGLE_PRIVATE_KEY="-----BEGIN PRIVATE KEY-----\nTU_CLAVE\n-----END PRIVATE KEY-----\n"
ZOOM_MEETING_URL=https://zoom.us/j/tuMeetingId
IG_NOTIFY_WEBHOOK_URL=https://tu-webhook.com/ig-notify
IG_NOTIFY_WEBHOOK_TOKEN=tu_token_opcional
```

## 3) Ejecutar backend
```powershell
npm start
```

Debe mostrar algo como:
`Silvex Assistant API en http://localhost:3034`

## 4) Funciones
- `POST /api/chat`: respuestas del asistente IA.
- `POST /api/meetings`: agenda de reuniones, creación de evento en Google Calendar y notificación automática vía webhook de Instagram.

## 5) Configuración de Google Calendar
1. Crea un proyecto en Google Cloud.
2. Habilita Google Calendar API.
3. Crea una Service Account.
4. Descarga la clave JSON.
5. Comparte tu calendario con el correo de la Service Account (permiso: "Hacer cambios").
6. Copia los datos del JSON a `.env`:
   - `client_email` -> `GOOGLE_SERVICE_ACCOUNT_EMAIL`
   - `private_key` -> `GOOGLE_PRIVATE_KEY` (con `\n`)
   - ID del calendario -> `GOOGLE_CALENDAR_ID`

## 6) Configuración de notificación a Instagram
1. Crea un flujo en ManyChat / Make / Zapier o middleware propio conectado a Instagram Business.
2. Expón una URL webhook para recibir el payload de reserva.
3. Configura `IG_NOTIFY_WEBHOOK_URL` en `.env`.
4. Si tu webhook exige autenticación, usa `IG_NOTIFY_WEBHOOK_TOKEN`.

## Seguridad
- No compartas `OPENAI_API_KEY` ni la clave privada de Google.
- No subas `server/.env` a GitHub.
