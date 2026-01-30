<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Monitor en vivo</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    /* ====== AURORA ====== */
    .aurora {
      position: absolute;
      width: 1200px;
      height: 1200px;
      filter: blur(80px);
      opacity: .45;
      border-radius: 9999px;
      mix-blend-mode: screen;
      animation: floaty 12s ease-in-out infinite;
    }

    .a1 {
      left: -260px;
      top: -280px;
      background: radial-gradient(circle at 30% 30%, rgba(56, 189, 248, .9), transparent 60%);
    }

    .a2 {
      right: -320px;
      top: 80px;
      background: radial-gradient(circle at 30% 30%, rgba(34, 197, 94, .85), transparent 60%);
      animation-duration: 16s;
    }

    .a3 {
      left: 20%;
      bottom: -420px;
      background: radial-gradient(circle at 30% 30%, rgba(249, 115, 22, .85), transparent 60%);
      animation-duration: 20s;
    }

    @keyframes floaty {
      0% {
        transform: translate3d(0, 0, 0) scale(1);
      }

      50% {
        transform: translate3d(60px, -40px, 0) scale(1.05);
      }

      100% {
        transform: translate3d(0, 0, 0) scale(1);
      }
    }

    /* ====== SHIMMER SUAVE ====== */
    .shimmer {
      position: relative;
      overflow: hidden;
    }

    .shimmer::after {
      content: "";
      position: absolute;
      inset: -40%;
      background: linear-gradient(120deg, transparent 30%, rgba(255, 255, 255, .10) 45%, transparent 60%);
      transform: translateX(-60%);
      animation: shimmerMove 3.5s ease-in-out infinite;
    }

    @keyframes shimmerMove {
      0% {
        transform: translateX(-60%) rotate(8deg);
      }

      50% {
        transform: translateX(10%) rotate(8deg);
      }

      100% {
        transform: translateX(-60%) rotate(8deg);
      }
    }

    /* ====== Pulso del reloj ====== */
    .clockPulse {
      animation: clockPulse 1.8s ease-in-out infinite;
    }

    @keyframes clockPulse {

      0%,
      100% {
        transform: scale(1);
        opacity: 1;
      }

      50% {
        transform: scale(1.01);
        opacity: .95;
      }
    }

    /* ====== Indicador “escuchando” ====== */
    .listeningDot {
      width: .7rem;
      height: .7rem;
      border-radius: 999px;
      background: rgba(34, 197, 94, .9);
      box-shadow: 0 0 0 rgba(34, 197, 94, .0);
      animation: pingDot 1.4s ease-out infinite;
    }

    @keyframes pingDot {
      0% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, .55);
      }

      70% {
        box-shadow: 0 0 0 14px rgba(34, 197, 94, 0);
      }

      100% {
        box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
      }
    }

    /* ====== Texto con puntos animados ====== */
    .dots::after {
      content: "";
      display: inline-block;
      width: 1.5em;
      text-align: left;
      animation: dots 1.2s steps(4, end) infinite;
    }

    @keyframes dots {
      0% {
        content: "";
      }

      25% {
        content: ".";
      }

      50% {
        content: "..";
      }

      75% {
        content: "...";
      }

      100% {
        content: "";
      }
    }

    /* Accesibilidad: si el usuario tiene reduce motion */
    @media (prefers-reduced-motion: reduce) {

      .aurora,
      .shimmer::after,
      .clockPulse,
      .listeningDot,
      .dots::after {
        animation: none !important;
      }
    }
  </style>

</head>

<body class="bg-slate-950 text-slate-100 overflow-hidden">
  <!-- Fondo animado: aurora -->
  <div class="fixed inset-0 z-10 overflow-hidden">
    <div class="aurora a1"></div>
    <div class="aurora a2"></div>
    <div class="aurora a3"></div>

    <!-- vignette para dar profundidad -->
    <div class="absolute inset-0 bg-gradient-to-b from-black/40 via-black/10 to-black/50"></div>

    <!-- Canvas partículas -->
    <canvas id="bgParticles" class="absolute inset-0 w-full h-full opacity-60"></canvas>
  </div>

  <!-- Fondo -->
  <div class="fixed inset-0 bg-gradient-to-br from-slate-950 via-slate-950 to-slate-900"></div>

  <!-- Estado (esquina) -->
  <div class="relative z-10 px-8 pt-6 flex items-start justify-end">
    <div class="text-right">
      <div class="text-sm text-slate-300">
        Estado: <span id="estado" class="text-emerald-300">conectando...</span>
      </div>
    </div>
  </div>

  <!-- CONTENIDO CENTRADO -->
  <div class="relative z-10 h-[calc(100vh-72px)] flex items-center justify-center">
    <div class="text-center px-8">
      <!-- Logo -->
      <div class="flex justify-center mb-6">
        <img id="logo" alt="Logo" class="h-20 w-auto opacity-90 hidden shimmer rounded-xl" />
      </div>
      <!-- Mensaje fijo importante (dashboard_sub) -->
      <!-- Aviso fijo importante (dashboard_sub) -->
      <div id="fixedSubWrap" class="hidden w-full flex justify-center mb-8 px-6">
        <div class="max-w-4xl w-full rounded-3xl
           bg-white/[0.06] border border-white/10
           backdrop-blur-xl shadow-2xl
           px-8 py-6 text-center">

          <div class="flex justify-center mb-3">
            <span class="text-3xl">📣</span>
          </div>

          <div id="fixedSub" class="text-[26px] sm:text-[30px] md:text-[34px]
             font-extrabold leading-tight tracking-tight text-white">
            —
          </div>

          <div class="mt-2 text-sm text-white/60">
            Aviso importante
          </div>
        </div>
      </div>


      <!-- Reloj grande -->
      <div id="clock" class="text-[110px] leading-none font-semibold tracking-tight clockPulse">
        --:--:--
      </div>

      <!-- Fecha -->
      <div id="date" class="mt-3 text-2xl text-slate-300 capitalize">
        ----
      </div>

      <!-- Clima grande -->
      <div class="mt-7 flex items-center justify-center gap-4">
        <div id="weatherIcon" class="text-6xl leading-none">⛅</div>
        <div class="text-left">
          <div id="climaTemp" class="text-5xl font-semibold">--°C</div>
          <div id="climaDesc" class="text-xl text-slate-300">Clima</div>
        </div>
      </div>

      <!-- Hint animado -->
      <!-- Mensaje rotativo -->
      <div class="mt-10 flex items-center justify-center gap-3 text-sm text-slate-300">
        <span class="listeningDot"></span>
        <div class="relative h-6 overflow-hidden">
          <div id="rotMsg" class="transition-opacity duration-400 ease-out opacity-100">
            Cargando mensaje…
          </div>
        </div>
      </div>


    </div>
  </div>

  <!-- OVERLAY: aparece solo cuando llega evento -->
  <div id="overlay" class="fixed inset-0 z-20 hidden">
    <div id="overlayBg" class="absolute inset-0"></div>

    <div class="relative h-full w-full flex items-center justify-center px-10">
      <div class="w-full max-w-5xl rounded-[36px] border border-white/10 shadow-2xl overflow-hidden
            bg-white/[0.03] backdrop-blur-xl">
        <div class="p-10 flex items-center gap-10" id="overlayCard">
          <!-- Foto -->
          <div class="shrink-0">
            <img id="ovFoto" class="h-56 w-56 rounded-3xl object-cover border border-white/15 bg-black/20" alt="Foto" />
          </div>

          <!-- Texto -->
          <!-- Texto -->
          <div class="min-w-0 flex-1">
            <!-- Título + badge -->
            <div class="flex items-center gap-3">
              <div id="ovTitulo" class="text-4xl font-semibold tracking-tight">---</div>

              <div id="ovBadge"
                class="hidden px-3 py-1 rounded-full text-sm font-semibold border border-white/15 bg-black/20">
                ---
              </div>
            </div>

            <!-- Nombre -->
            <div id="ovNombre" class="mt-3 text-5xl font-bold truncate">---</div>

            <!-- Línea principal -->
            <div id="ovSub" class="mt-4 text-2xl text-white/90 flex items-center gap-2">
              ---
            </div>

            <!-- Línea secundaria -->
            <div id="ovExtra" class="mt-2 text-lg text-white/75">---</div>

            <!-- Membresía (solo clientes) -->
            <div id="ovMembership" class="mt-7 hidden">
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- VENCE -->
                <div
                  class="rounded-2xl bg-black/25 border border-white/10 p-5 min-h-[140px] flex flex-col justify-between">
                  <div class="text-sm uppercase tracking-widest text-white/60 flex items-center gap-2">
                    🗓️ Vence
                  </div>
                  <div id="ovFin"
                    class="mt-2 font-extrabold leading-none whitespace-nowrap tracking-tight text-xl sm:text-2xl lg:text-3xl">
                    —
                  </div>
                  <div class="mt-2 text-base text-white/70">Fecha de expiración</div>
                </div>

                <!-- DÍAS -->
                <div
                  class="rounded-2xl bg-black/25 border border-white/10 p-5 min-h-[140px] flex flex-col justify-between">

                  <div class="text-sm uppercase tracking-widest text-white/60 flex items-center gap-2">
                    ⏳ Restan
                  </div>
                  <div class="mt-2 flex items-end gap-3">
                    <div id="ovDaysNumber" class="text-5xl sm:text-6xl font-extrabold leading-none">—</div>
                    <div class="text-xl text-white/80 mb-1">días</div>
                  </div>
                  <div id="ovDaysText" class="mt-2 text-base text-white/70">—</div>
                </div>
              </div>
            </div>

          </div>


          <div class="ml-auto w-44 shrink-0 text-right flex flex-col items-end justify-between self-stretch">
            <div>
              <div id="ovTime" class="text-2xl text-white/80">--:--</div>
            </div>

            <button id="ovClose"
              class="mt-6 px-6 py-3 rounded-2xl bg-black/25 border border-white/15 text-white/90 hover:bg-black/35">
              Cerrar
            </button>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script>
    /** CONFIG **/
    const EVENT_MAP = {
      entrada: new Set([196893]),
      vencida: new Set([197384, 197633]),
      noreg: new Set([197151]),
    };

    // ✅ Overlay 5 segundos
    const OVERLAY_MS = 5000;

    let overlayTimer = null;

    /** UI refs **/
    const estado = document.getElementById("estado");
    const overlay = document.getElementById("overlay");
    const overlayBg = document.getElementById("overlayBg");
    const ovFoto = document.getElementById("ovFoto");
    const ovTitulo = document.getElementById("ovTitulo");
    const ovNombre = document.getElementById("ovNombre");
    const ovSub = document.getElementById("ovSub");
    const ovExtra = document.getElementById("ovExtra");
    const ovTime = document.getElementById("ovTime");
    const ovBadge = document.getElementById("ovBadge");
    const ovMembership = document.getElementById("ovMembership");
    const ovFin = document.getElementById("ovFin");
    const ovDaysNumber = document.getElementById("ovDaysNumber");
    const ovDaysText = document.getElementById("ovDaysText");



    /** Helpers **/
    function nowClock() {
      const d = new Date();
      const hh = String(d.getHours()).padStart(2, "0");
      const mm = String(d.getMinutes()).padStart(2, "0");
      const ss = String(d.getSeconds()).padStart(2, "0");
      document.getElementById("clock").textContent = `${hh}:${mm}:${ss}`;
      document.getElementById("date").textContent = d.toLocaleDateString("es-MX", {
        weekday: "long", year: "numeric", month: "long", day: "numeric"
      });
      ovTime.textContent = `${hh}:${mm}:${ss}`;
    }
    setInterval(nowClock, 250);
    nowClock();

    function normalizeTab(eventType) {
      const n = Number(eventType);
      if (EVENT_MAP.entrada.has(n)) return "entrada";
      if (EVENT_MAP.vencida.has(n)) return "vencida";
      if (EVENT_MAP.noreg.has(n)) return "noreg";
      return "otro";
    }

    function setOverlayTheme(tab) {
      if (tab === "entrada") {
        overlayBg.className = "absolute inset-0 bg-emerald-700/35 backdrop-blur-sm";
      } else if (tab === "vencida") {
        overlayBg.className = "absolute inset-0 bg-rose-700/40 backdrop-blur-sm";
      } else {
        overlayBg.className = "absolute inset-0 bg-slate-700/40 backdrop-blur-sm";
      }
    }

    function fallbackPhoto() {
      return "data:image/svg+xml;base64," + btoa(`
    <svg xmlns='http://www.w3.org/2000/svg' width='400' height='400'>
      <rect width='100%' height='100%' fill='rgba(0,0,0,0.25)'/>
      <text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle'
            fill='white' font-size='28'>SIN FOTO</text>
    </svg>
  `);
    }

    function showOverlay(tab, payload) {
      clearTimeout(overlayTimer);

      // Fondo según tipo (entrada / vencida / noreg)
      setOverlayTheme(tab);

      // ✅ Título (permite override desde payload)
      if (payload.title) {
        ovTitulo.textContent = payload.title;
      } else {
        if (tab === "entrada") ovTitulo.textContent = "ACCESO PERMITIDO";
        else if (tab === "vencida") ovTitulo.textContent = "SUSCRIPCIÓN VENCIDA";
        else ovTitulo.textContent = "USUARIO NO REGISTRADO";
      }

      // Texto principal
      ovNombre.textContent = payload.personName || (tab === "noreg" ? "—" : "(sin nombre)");
      ovSub.textContent = payload.msgLine || "—";
      ovExtra.textContent = payload.extraLine || "";
      ovFoto.src = payload.photoUrl || fallbackPhoto();

      // ✅ Badge (tipo)
      if (payload.badgeText && String(payload.badgeText).trim() !== "") {
        ovBadge.textContent = payload.badgeText;
        ovBadge.classList.remove("hidden");
      } else {
        ovBadge.classList.add("hidden");
      }

      // ✅ Membresía (solo cuando hay fin o daysLeft)
      const hasMembership =
        (payload.finText && String(payload.finText).trim() !== "") ||
        (payload.daysLeft != null);

      if (hasMembership) {
        ovMembership.classList.remove("hidden");

        // Vence (fecha)
        ovFin.textContent = payload.finText || "—";

        // Días restantes
        const dleft = (payload.daysLeft != null) ? Number(payload.daysLeft) : null;

        if (dleft != null && !Number.isNaN(dleft)) {
          ovDaysNumber.textContent = String(dleft);

          if (dleft <= 0) {
            ovDaysText.textContent = "Vence hoy";
          } else if (dleft === 1) {
            ovDaysText.textContent = "Te queda 1 día";
          } else {
            ovDaysText.textContent = `Te quedan ${dleft} días`;
          }
        } else {
          ovDaysNumber.textContent = "—";
          ovDaysText.textContent = "—";
        }

      } else {
        ovMembership.classList.add("hidden");
      }

      // Mostrar overlay
      overlay.classList.remove("hidden");
      // si ya está visible y llega otro evento, reinicia el timer sin ocultar entre eventos
      overlay.classList.remove("hidden");

      // Auto ocultar
      overlayTimer = setTimeout(() => {
        overlay.classList.add("hidden");
      }, OVERLAY_MS);
    }




    document.getElementById("ovClose").addEventListener("click", () => {
      overlay.classList.add("hidden");
      clearTimeout(overlayTimer);
    });

    /** Logo desde BD **/
    /** Logo + Branding desde BD **/
    /** Logo + Branding desde BD **/
    async function loadLogo() {
      try {
        const r = await fetch("get_logo.php", { cache: "no-store" });
        const j = await r.json();
        if (!j.ok) return;

        // Logo
        if (j.dataUrl) {
          const img = document.getElementById("logo");
          img.src = j.dataUrl;
          img.classList.remove("hidden");
        }

        // ✅ dashboard_sub fijo grande
        const sub = (j.dashboard_sub || "").trim();
        const fixedWrap = document.getElementById("fixedSubWrap");
        const fixedSub = document.getElementById("fixedSub");

        if (sub) {
          fixedSub.textContent = sub;
          fixedWrap.classList.remove("hidden");
        } else {
          fixedWrap.classList.add("hidden");
        }

      } catch (e) { }
    }
    loadLogo();



    /** Clima con iconos **/
    function weatherCodeToText(code) {
      if (code === 0) return "Despejado";
      if ([1, 2, 3].includes(code)) return "Parcial nublado";
      if ([45, 48].includes(code)) return "Niebla";
      if ([51, 53, 55, 56, 57].includes(code)) return "Llovizna";
      if ([61, 63, 65, 66, 67].includes(code)) return "Lluvia";
      if ([71, 73, 75, 77].includes(code)) return "Nieve";
      if ([80, 81, 82].includes(code)) return "Chubascos";
      if ([95, 96, 99].includes(code)) return "Tormenta";
      return "Clima";
    }

    function weatherCodeToIcon(code) {
      if (code === 0) return "☀️";
      if ([1, 2, 3].includes(code)) return "⛅";
      if ([45, 48].includes(code)) return "🌫️";
      if ([51, 53, 55, 56, 57].includes(code)) return "🌦️";
      if ([61, 63, 65, 66, 67].includes(code)) return "🌧️";
      if ([71, 73, 75, 77].includes(code)) return "❄️";
      if ([80, 81, 82].includes(code)) return "🌧️";
      if ([95, 96, 99].includes(code)) return "⛈️";
      return "⛅";
    }

    async function loadWeather() {
      if (!navigator.geolocation) return;

      navigator.geolocation.getCurrentPosition(async (pos) => {
        try {
          const lat = pos.coords.latitude;
          const lng = pos.coords.longitude;

          const r = await fetch(`weather.php?lat=${lat}&lng=${lng}`, { cache: "no-store" });
          const j = await r.json();
          if (j.ok) {
            const code = Number(j.code);
            document.getElementById("weatherIcon").textContent = weatherCodeToIcon(code);
            document.getElementById("climaTemp").textContent = (j.temp != null) ? `${j.temp}°C` : "--°C";
            document.getElementById("climaDesc").textContent = weatherCodeToText(code);
          }
        } catch (e) { }
      }, () => { }, { enableHighAccuracy: false, timeout: 5000, maximumAge: 60000 });
    }

    loadWeather();
    setInterval(loadWeather, 5 * 60 * 1000);

    const PERSONA_ENDPOINT = "get_persona.php";
    const personaCache = new Map(); // personCode -> { data, ts }
    const PERSONA_TTL_MS = 60_000;  // 1 min

    async function fetchPersona(personCode) {
      if (!personCode) return null;
      const key = String(personCode);

      const hit = personaCache.get(key);
      if (hit && (Date.now() - hit.ts) < PERSONA_TTL_MS) return hit.data;

      const r = await fetch(`${PERSONA_ENDPOINT}?personCode=${encodeURIComponent(key)}`, { cache: "no-store" });
      if (!r.ok) return null;
      const j = await r.json();
      if (!j.ok) return null;

      personaCache.set(key, { data: j, ts: Date.now() });
      return j;
    }

    /** =========================
     *  SSE (Stream en vivo)
     *  ========================= */
    const picCache = new Map(); // ✅ cache global

    async function fetchPicDataUri(picUri) {
      if (!picUri) return null;
      if (picCache.has(picUri)) return picCache.get(picUri);

      const url = `get_event_pic.php?uri=${encodeURIComponent(picUri)}`;
      const r = await fetch(url, { cache: "no-store" });
      if (!r.ok) return null;

      const txt = (await r.text()).trim();
      if (!txt.startsWith("data:image")) return null;

      picCache.set(picUri, txt);
      return txt;
    }

    // ✅ Usa ruta relativa para que funcione desde cualquier PC en la red
    const es = new EventSource("stream.php");

    es.addEventListener("hello", () => {
      estado.textContent = "conectado";
      estado.className = "text-emerald-300";
      setRotMsg("Conectado. Escuchando eventos en tiempo real…");
    });
    // ===== Control de ráfagas + anti-duplicados (evita flasheo) =====
    let pendingEv = null;
    let flushTimer = null;

    const BURST_WINDOW_MS = 250;   // junta eventos muy seguidos
    const DEDUPE_WINDOW_MS = 2000; // ignora duplicados por 2s

    // firma del evento (más confiable que eventId si llega duplicado con id distinto)
    function eventKey(ev) {
      return ev?.eventId || (
        (ev?.personCode || "") + "|" + (ev?.happenTime || "") + "|" + (ev?.eventType || "") + "|" + (ev?.srcName || ev?.readerName || "")
      );
    }

    const recentKeys = new Map(); // key -> timestamp

    function isDuplicate(ev) {
      const key = eventKey(ev);
      if (!key) return false;

      const now = Date.now();

      for (const [k, ts] of recentKeys) {
        if (now - ts > DEDUPE_WINDOW_MS) recentKeys.delete(k);
      }

      const last = recentKeys.get(key);
      if (last && (now - last) < DEDUPE_WINDOW_MS) return true;

      recentKeys.set(key, now);
      return false;
    }

    function queueLastEvent(ev) {
      if (!ev) return;

      // ✅ evita duplicados (esto mata el "flasheo" típico)
      if (isDuplicate(ev)) return;

      pendingEv = ev;

      if (flushTimer) clearTimeout(flushTimer);
      flushTimer = setTimeout(async () => {
        if (!pendingEv) return;

        const toShow = pendingEv;
        pendingEv = null;

        await renderEvent(toShow);
      }, BURST_WINDOW_MS);
    }


    es.addEventListener("hik", (e) => {
      let ev;
      try { ev = JSON.parse(e.data); } catch { return; }
      queueLastEvent(ev);
    });


    async function renderEvent(ev) {
      // Foto del evento
      const foto = await fetchPicDataUri(ev.picUri);

      // Buscar persona en BD por personCode
      const info = await fetchPersona(ev.personCode);

      const status = info?.status || "unknown";
      const device = ev.srcName || ev.readerName || "-";
      const nombre = info?.nombreCompleto || ev.personCode || "—";
      const tipo = info?.tipo ? info.tipo.toUpperCase() : "";

      if (status === "active") {
        showOverlay("entrada", {
          title: "ACCESO PERMITIDO",
          badgeText: tipo ? `👤 ${tipo}` : "",
          personName: nombre,
          msgLine: "✅ Membresía activa",
          extraLine: `🚪 ${device}`,
          photoUrl: foto,
          finText: info.finHuman || "-",
          daysLeft: info.daysLeft ?? null
        });

      } else if (status === "expired") {
        const fin = info.finHuman ? `Venció: ${info.finHuman}` : "Membresía vencida";
        showOverlay("vencida", {
          title: "SUSCRIPCIÓN VENCIDA",
          badgeText: tipo ? `👤 ${tipo}` : "",
          personName: nombre,
          msgLine: "❌ Pasa a mostrador a pagar",
          extraLine: `🗓️ ${fin} · 🚪 ${device}`,
          photoUrl: foto
        });

      } else if (status === "staff") {
        showOverlay("entrada", {
          title: info.title || "BIENVENIDO(A)",
          badgeText: tipo ? `🛡️ ${tipo}` : "",
          personName: nombre,
          msgLine: "👋 Bienvenido(a)",
          extraLine: `🚪 ${device}`,
          photoUrl: foto
        });

      } else {
        showOverlay("noreg", {
          title: "NO REGISTRADO",
          badgeText: "",
          personName: "",
          msgLine: "📝 Pasa a inscribirte a mostrador",
          extraLine: `🚪 ${device}`,
          photoUrl: foto
        });
      }
    }


    es.onerror = () => {
      estado.textContent = "desconectado";
      estado.className = "text-rose-300";
      setRotMsg("Conexión perdida. Reintentando automáticamente…");
    };

    // ===== Fondo: partículas suaves (muy ligero) =====
    (function initParticles() {
      const canvas = document.getElementById("bgParticles");
      if (!canvas) return;

      const ctx = canvas.getContext("2d");
      let w, h, dpr;

      function resize() {
        dpr = Math.max(1, Math.min(2, window.devicePixelRatio || 1));
        w = canvas.clientWidth;
        h = canvas.clientHeight;
        canvas.width = Math.floor(w * dpr);
        canvas.height = Math.floor(h * dpr);
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      }
      window.addEventListener("resize", resize);
      resize();

      const N = 55;
      const pts = Array.from({ length: N }, () => ({
        x: Math.random() * w,
        y: Math.random() * h,
        r: 1.2 + Math.random() * 2.4,
        vx: (-0.15 + Math.random() * 0.3),
        vy: (-0.10 + Math.random() * 0.2),
        a: 0.10 + Math.random() * 0.22
      }));

      function step() {
        ctx.clearRect(0, 0, w, h);

        // puntos
        for (const p of pts) {
          p.x += p.vx; p.y += p.vy;

          if (p.x < -30) p.x = w + 30;
          if (p.x > w + 30) p.x = -30;
          if (p.y < -30) p.y = h + 30;
          if (p.y > h + 30) p.y = -30;

          ctx.beginPath();
          ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
          ctx.fillStyle = `rgba(148,163,184,${p.a})`; // slate-400 vibe
          ctx.fill();
        }

        // conexiones cercanas (sutil)
        for (let i = 0; i < pts.length; i++) {
          for (let j = i + 1; j < pts.length; j++) {
            const a = pts[i], b = pts[j];
            const dx = a.x - b.x, dy = a.y - b.y;
            const dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < 140) {
              const alpha = (1 - dist / 140) * 0.08;
              ctx.strokeStyle = `rgba(148,163,184,${alpha})`;
              ctx.lineWidth = 1;
              ctx.beginPath();
              ctx.moveTo(a.x, a.y);
              ctx.lineTo(b.x, b.y);
              ctx.stroke();
            }
          }
        }

        requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    })();
    // ===== Mensajes rotativos (usa dashboard_sub desde get_logo.php) =====
    let ROTATION_MS = 9000;
    let rotMessages = [
      "En espera de eventos de acceso…",
      "Sistema listo para capturar accesos.",
      "Tip: si no aparece un acceso, revisa conexión del lector."
    ];


    let rotIdx = 0;

    function setRotMsg(text) {
      const el = document.getElementById("rotMsg");
      if (!el) return;

      // fade out -> cambiar texto -> fade in
      el.style.opacity = "0";
      setTimeout(() => {
        el.textContent = text;
        el.style.opacity = "1";
      }, 220);
    }

    function startRotation() {
      if (!rotMessages.length) return;
      setRotMsg(rotMessages[0]);
      setInterval(() => {
        rotIdx = (rotIdx + 1) % rotMessages.length;
        setRotMsg(rotMessages[rotIdx]);
      }, ROTATION_MS);
    }

    // Llama esto una vez, al cargar la página
    startRotation();

  </script>


</body>

</html>