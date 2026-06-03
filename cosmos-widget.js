/**
 * ╔═══════════════════════════════════════════════════════╗
 * ║          COSMOS — SOAC AI Assistant Widget            ║
 * ║  Drop one line in any HTML page before </body>:       ║
 * ║  <script src="cosmos-widget.js"></script>             ║
 * ╚═══════════════════════════════════════════════════════╝
 *
 * SETUP:
 *  1. Go to https://openrouter.ai → sign up → Keys → Create Key
 *  2. Paste your key below (starts with sk-or-...)
 */

(function () {

  const OPENROUTER_API_KEY = "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"; // ← your key is already here

  const SYSTEM_PROMPT = `You are Cosmos, the friendly and knowledgeable AI assistant for the STEM October Astronomy Club (SOAC). You have a warm, enthusiastic, and scientifically curious personality — like a wise stargazer who loves sharing knowledge. You speak with confidence and passion about astronomy, and always represent SOAC positively. Keep responses concise and clear. Use emojis sparingly for warmth (🌌 🔭 ⭐). You are Cosmos, the voice of SOAC — not a generic chatbot.

== ABOUT SOAC ==
Full name: STEM October Astronomy Club (SOAC)
School: STEM High School for Boys - 6th of October, Egypt
Founded: October 2024
Founder: Farouk Diab
Co-Founder: Tareq Khalil
Mission: To explore the universe, foster scientific curiosity, and inspire the next generation of astronomers and space enthusiasts.
Description: A club uniting students who look at the night sky and see questions they are eager to answer. Activities include observation sessions, astronomy lectures, group discussions, stargazing nights, competitions, and collaborative projects.

== CLUB STATS (Season 1) ==
- 30 sessions completed
- 7 assignments given
- 11 simulations created and used
- 30+ student members

== LEADERSHIP TEAM ==
- Farouk Diab — Founder & President
- Mohanad Elagan — Founding President (2024)
- Aly Algendy — Vice-President
- Mohamed Osama — Vice-President
- Tareq Khalil — Web Development Manager, Co-Founder, Academic Mentor, main organizer of Cosmic Quest
- Mohammed Abdelaziz — Game Developer
- Loay Alaa — Academic Mentor
- Ahmed Awd — Academic Mentor (s'25)

== CURRICULUM (25+ Interactive Sessions) ==
Five major areas:
I.   Introducing Astronomy — Light & Telescopes, Celestial Motion, Gravitation, History
II.  Planets and Moons — Solar System, Planetary Geology, Atmospheres, Moons
III. Stars and Stellar Evolution — Stellar Birth, Nuclear Fusion, Supernovae, Black Holes
IV.  Galaxies and Cosmology — Milky Way, Dark Matter, Big Bang Theory, Exoplanets
V.   Modern Astrophysics — Quantum Physics, Relativity, Gravitational Waves, Research

== EVENTS ==
- Armageddon: Asteroid defense simulation using real orbital mechanics data
- Astro Hunt: Treasure hunt with astronomical puzzles and star maps
- Star Trek: Virtual voyage through the solar system using VR and simulation software
- Sambhar Lake Trip: Annual dark-sky trip for stargazing and astrophotography
- Night Camp: Stargazing under the midnight sky
- Presentation Series: Introducing new members to astronomy
- Telescope Workshops, Guest Lectures, Observatory Visits

== COSMIC QUEST COMPETITION ==
International astronomy competition founded by SOAC. Main organizer: Tareq Khalil.
- Open to: 9th grade and high school students worldwide
- Team size: 3 students
- Structure: Open Round (30 questions, 3 days, live leaderboard) then Invitational Round (Top 32 teams, live buzz session)
- Timeline: Registration Aug 16 to Oct 5 2025, Round 1 Oct 10, Finals Oct 25 2025
- Prizes:
  * 1st Place: VR 114-500 EQ Telescope
  * 2nd & 3rd: 3 AoPS course coupons each
  * Wolfram prizes worth $111,000+: all participants get 1-month Wolfram|One; top 16 get 1-year licenses; special awards include $500 Wolfram Summer Program scholarship
  * Gold, Silver, Bronze medals + Certificates for all
- Sponsors: Wolfram, Art of Problem Solving (AoPS)

== SIMULATIONS (11 total) ==
Curved Spacetime, Orbital Mechanics, Black Hole Visualization, Solar System 3D, Circumstellar Habitable Zone, Eclipsing Binary, Artificial Satellites, and more via the Astronomy Toolkit.

== GAMES ==
Space Odyssey, Astro Hunt, Planet Explorer, Space Mission Simulator, Asteroid Dodge.

== WEBSITE PAGES ==
Home, About Us / Meet the Team, Simulations, Events, Competitions (Cosmic Quest, Ambassador, Astrophotography, Rocket Design, Space Quiz), Games, Partners, Contact.

If asked something specific about SOAC not covered above, say so honestly and suggest contacting the club directly.`;

  // ── Styles ──────────────────────────────────────────────────────────────────
  const CSS = `
    #cosmos-btn {
      position: fixed; bottom: 28px; right: 28px;
      width: 58px; height: 58px; border-radius: 50%;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      border: 2px solid rgba(135,206,250,0.65);
      cursor: pointer; z-index: 2147483646;
      display: flex; align-items: center; justify-content: center;
      font-size: 25px;
      box-shadow: 0 0 22px rgba(100,180,255,0.45), 0 4px 16px rgba(0,0,0,0.55);
      transition: transform .25s ease, box-shadow .25s ease;
      animation: cmos-pulse 3s ease-in-out infinite;
    }
    #cosmos-btn:hover {
      transform: scale(1.11);
      box-shadow: 0 0 34px rgba(100,180,255,0.75), 0 4px 20px rgba(0,0,0,0.55);
    }
    @keyframes cmos-pulse {
      0%,100% { box-shadow: 0 0 22px rgba(100,180,255,.45),0 4px 16px rgba(0,0,0,.55); }
      50%      { box-shadow: 0 0 38px rgba(100,180,255,.8), 0 4px 20px rgba(0,0,0,.55); }
    }
    #cosmos-panel {
      position: fixed; bottom: 98px; right: 28px;
      width: 368px; height: 528px;
      background: linear-gradient(160deg,#080c24 0%,#0c1836 55%,#080c24 100%);
      border: 1px solid rgba(135,206,250,.28);
      border-radius: 22px; z-index: 2147483647;
      display: flex; flex-direction: column; overflow: hidden;
      box-shadow: 0 22px 65px rgba(0,0,0,.75), 0 0 45px rgba(100,180,255,.12);
      transform: scale(.88) translateY(18px); opacity: 0; pointer-events: none;
      transition: transform .38s cubic-bezier(.34,1.56,.64,1), opacity .28s ease;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #cosmos-panel.cmos-open { transform: scale(1) translateY(0); opacity: 1; pointer-events: all; }
    #cosmos-panel::before {
      content:''; position:absolute; inset:0; border-radius:22px; pointer-events:none;
      background-image:
        radial-gradient(1px 1px at 8%  12%, rgba(255,255,255,.75), transparent),
        radial-gradient(1px 1px at 28% 38%, rgba(255,255,255,.55), transparent),
        radial-gradient(1.5px 1.5px at 53% 18%, rgba(255,255,255,.65), transparent),
        radial-gradient(1px 1px at 68% 58%, rgba(255,255,255,.45), transparent),
        radial-gradient(1px 1px at 83% 28%, rgba(255,255,255,.7),  transparent),
        radial-gradient(1px 1px at 18% 72%, rgba(255,255,255,.5),  transparent),
        radial-gradient(1px 1px at 88% 78%, rgba(255,255,255,.45), transparent),
        radial-gradient(1.5px 1.5px at 43% 83%, rgba(255,255,255,.6), transparent),
        radial-gradient(1px 1px at 63% 48%, rgba(255,255,255,.35), transparent);
    }
    .cmos-header {
      padding: 15px 18px;
      background: linear-gradient(135deg,rgba(138,43,226,.28),rgba(72,187,255,.18));
      border-bottom: 1px solid rgba(135,206,250,.18);
      display: flex; align-items: center; gap: 11px; position: relative; z-index:1; flex-shrink:0;
    }
    .cmos-avatar {
      width:40px; height:40px; border-radius:50%;
      background: linear-gradient(135deg,#6a11cb,#2575fc);
      border: 2px solid rgba(135,206,250,.5);
      display:flex; align-items:center; justify-content:center;
      font-size:19px; flex-shrink:0; box-shadow: 0 0 14px rgba(100,150,255,.4);
    }
    .cmos-info { flex:1; }
    .cmos-name { color:#87ceeb; font-weight:700; font-size:14.5px; letter-spacing:.9px; }
    .cmos-sub  { color:rgba(135,206,250,.55); font-size:10.5px; margin-top:1px; }
    .cmos-dot  { width:8px; height:8px; border-radius:50%; background:#4ade80; box-shadow:0 0 6px #4ade80; flex-shrink:0; }
    .cmos-x {
      background:none; border:none; color:rgba(135,206,250,.55); font-size:21px;
      cursor:pointer; padding:1px 5px; border-radius:6px; line-height:1;
      transition: color .18s, background .18s;
    }
    .cmos-x:hover { color:#87ceeb; background:rgba(135,206,250,.1); }
    .cmos-msgs {
      flex:1; overflow-y:auto; padding:14px 14px 6px;
      display:flex; flex-direction:column; gap:11px;
      position:relative; z-index:1;
      scrollbar-width:thin; scrollbar-color:rgba(135,206,250,.28) transparent;
    }
    .cmos-msgs::-webkit-scrollbar { width:3px; }
    .cmos-msgs::-webkit-scrollbar-thumb { background:rgba(135,206,250,.28); border-radius:2px; }
    .cmos-bubble {
      max-width:90%; padding:9px 13px; border-radius:14px;
      font-size:13.2px; line-height:1.58; white-space:pre-wrap; word-break:break-word;
      animation: cmos-in .28s ease;
    }
    @keyframes cmos-in { from{opacity:0;transform:translateY(7px)} to{opacity:1;transform:translateY(0)} }
    .cmos-bubble.bot {
      background:linear-gradient(135deg,rgba(28,40,78,.92),rgba(18,28,62,.92));
      border:1px solid rgba(135,206,250,.18); color:#cfe6ff;
      align-self:flex-start; border-bottom-left-radius:4px;
    }
    .cmos-bubble.usr {
      background:linear-gradient(135deg,rgba(98,58,178,.88),rgba(68,38,148,.88));
      border:1px solid rgba(158,118,255,.28); color:#ead8ff;
      align-self:flex-end; border-bottom-right-radius:4px;
    }
    .cmos-typing {
      display:flex; align-items:center; gap:5px; padding:9px 13px;
      background:linear-gradient(135deg,rgba(28,40,78,.92),rgba(18,28,62,.92));
      border:1px solid rgba(135,206,250,.18); border-radius:14px;
      border-bottom-left-radius:4px; align-self:flex-start; width:58px;
    }
    .cmos-td {
      width:7px; height:7px; border-radius:50%; background:rgba(135,206,250,.7);
      animation: cmos-td 1.3s ease-in-out infinite;
    }
    .cmos-td:nth-child(2){animation-delay:.2s} .cmos-td:nth-child(3){animation-delay:.4s}
    @keyframes cmos-td { 0%,60%,100%{transform:translateY(0)} 30%{transform:translateY(-6px)} }
    .cmos-foot {
      padding:10px 12px; border-top:1px solid rgba(135,206,250,.13);
      display:flex; gap:7px; align-items:flex-end;
      position:relative; z-index:1; background:rgba(8,12,36,.65); flex-shrink:0;
    }
    .cmos-inp {
      flex:1; background:rgba(18,28,62,.82);
      border:1px solid rgba(135,206,250,.22); border-radius:11px;
      color:#cfe6ff; font-size:13px; padding:8px 12px;
      resize:none; font-family:inherit; outline:none;
      max-height:96px; min-height:36px; line-height:1.45; transition:border-color .18s;
    }
    .cmos-inp::placeholder { color:rgba(135,206,250,.38); }
    .cmos-inp:focus { border-color:rgba(135,206,250,.52); }
    .cmos-send {
      width:36px; height:36px; border-radius:9px;
      background:linear-gradient(135deg,#6a11cb,#2575fc);
      border:none; cursor:pointer; display:flex; align-items:center; justify-content:center;
      flex-shrink:0; transition:transform .18s, box-shadow .18s;
      box-shadow:0 2px 10px rgba(100,150,255,.3);
    }
    .cmos-send:hover { transform:scale(1.08); box-shadow:0 4px 16px rgba(100,150,255,.52); }
    .cmos-send:disabled { opacity:.38; cursor:default; transform:none; }
    .cmos-send svg { width:15px; height:15px; fill:#fff; }
    .cmos-chips {
      display:flex; flex-wrap:wrap; gap:6px;
      padding:0 14px 10px; position:relative; z-index:1;
    }
    .cmos-chip {
      background:rgba(100,60,180,.35); border:1px solid rgba(135,206,250,.25);
      color:rgba(200,225,255,.82); font-size:11.5px; padding:5px 10px;
      border-radius:20px; cursor:pointer; transition:background .18s, border-color .18s;
      font-family:inherit; white-space:nowrap;
    }
    .cmos-chip:hover { background:rgba(100,60,180,.6); border-color:rgba(135,206,250,.5); }
    @media(max-width:480px){
      #cosmos-panel{width:calc(100vw - 16px);right:8px;bottom:88px;height:475px;}
      #cosmos-btn{bottom:18px;right:18px;}
    }
  `;

  // ── Mount HTML ──────────────────────────────────────────────────────────────
  function mount() {
    const style = document.createElement('style');
    style.textContent = CSS;
    document.head.appendChild(style);

    const btn = document.createElement('button');
    btn.id = 'cosmos-btn';
    btn.setAttribute('aria-label', 'Open Cosmos – SOAC AI Assistant');
    btn.textContent = '🔭';
    document.body.appendChild(btn);

    const panel = document.createElement('div');
    panel.id = 'cosmos-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Cosmos AI Assistant');
    panel.innerHTML = `
      <div class="cmos-header">
        <div class="cmos-avatar">🌌</div>
        <div class="cmos-info">
          <div class="cmos-name">COSMOS</div>
          <div class="cmos-sub">SOAC AI Assistant</div>
        </div>
        <div class="cmos-dot" title="Online"></div>
        <button class="cmos-x" aria-label="Close">&times;</button>
      </div>
      <div class="cmos-msgs" id="cmos-msgs" role="log" aria-live="polite"></div>
      <div class="cmos-chips" id="cmos-chips"></div>
      <div class="cmos-foot">
        <textarea class="cmos-inp" id="cmos-inp"
          placeholder="Ask about SOAC, Cosmic Quest, astronomy…"
          rows="1" aria-label="Message"></textarea>
        <button class="cmos-send" id="cmos-send" aria-label="Send">
          <svg viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
        </button>
      </div>
    `;
    document.body.appendChild(panel);
  }

  // ── State ───────────────────────────────────────────────────────────────────
  let open = false;
  let busy = false;
  let history = []; // OpenAI-style: [{role:'user'|'assistant', content:'...'}]

  const CHIPS = ["What is SOAC?", "Tell me about Cosmic Quest", "Who founded the club?", "What topics do you cover?"];

  // ── Helpers ─────────────────────────────────────────────────────────────────
  function addBubble(role, text) {
    const box = document.getElementById('cmos-msgs');
    const el = document.createElement('div');
    el.className = 'cmos-bubble ' + (role === 'user' ? 'usr' : 'bot');
    el.textContent = text;
    box.appendChild(el);
    box.scrollTop = box.scrollHeight;
  }

  function showTyping() {
    const box = document.getElementById('cmos-msgs');
    const el = document.createElement('div');
    el.className = 'cmos-typing'; el.id = 'cmos-typing';
    el.innerHTML = '<div class="cmos-td"></div><div class="cmos-td"></div><div class="cmos-td"></div>';
    box.appendChild(el);
    box.scrollTop = box.scrollHeight;
  }

  function hideTyping() { const e = document.getElementById('cmos-typing'); if (e) e.remove(); }
  function setEnabled(on) {
    document.getElementById('cmos-inp').disabled = !on;
    document.getElementById('cmos-send').disabled = !on;
  }

  // ── API Call (OpenRouter — OpenAI-compatible) ────────────────────────────────
  async function ask(text) {
    text = text.trim();
    if (!text || busy) return;
    busy = true;
    setEnabled(false);

    // Hide chips after first message
    const chips = document.getElementById('cmos-chips');
    if (chips) chips.style.display = 'none';

    addBubble('user', text);
    history.push({ role: 'user', content: text });

    const inp = document.getElementById('cmos-inp');
    inp.value = ''; inp.style.height = 'auto';
    showTyping();

    try {
      const res = await fetch('https://openrouter.ai/api/v1/chat/completions', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Bearer ${OPENROUTER_API_KEY}`,
          'HTTP-Referer': window.location.origin,
          'X-Title': 'SOAC Cosmos Assistant',
        },
        body: JSON.stringify({
          model: 'meta-llama/llama-3.1-8b-instruct:free', // free model, no credits needed
          messages: [
            { role: 'system', content: SYSTEM_PROMPT },
            ...history,
          ],
          max_tokens: 800,
          temperature: 0.7,
        }),
      });

      const data = await res.json();

      if (!res.ok) {
        throw new Error(data?.error?.message || `HTTP ${res.status}`);
      }

      const reply = data?.choices?.[0]?.message?.content || "I couldn't get a response. Please try again!";
      history.push({ role: 'assistant', content: reply });
      hideTyping();
      addBubble('bot', reply);
    } catch (err) {
      hideTyping();
      addBubble('bot', '⚠️ I had trouble connecting to the stars. Please try again in a moment!');
      console.error('[Cosmos]', err.message);
    } finally {
      busy = false;
      setEnabled(true);
      document.getElementById('cmos-inp').focus();
    }
  }

  // ── Open / Close ─────────────────────────────────────────────────────────────
  function openPanel() {
    open = true;
    document.getElementById('cosmos-panel').classList.add('cmos-open');
    document.getElementById('cosmos-btn').textContent = '✕';
    document.getElementById('cmos-inp').focus();
    if (history.length === 0) {
      setTimeout(() => {
        addBubble('bot', "🌌 Hello! I'm Cosmos, SOAC's AI assistant. Ask me anything about our club, the Cosmic Quest competition, our curriculum, or any astronomy question!");
        const chips = document.getElementById('cmos-chips');
        CHIPS.forEach(label => {
          const b = document.createElement('button');
          b.className = 'cmos-chip'; b.textContent = label;
          b.addEventListener('click', () => ask(label));
          chips.appendChild(b);
        });
      }, 220);
    }
  }

  function closePanel() {
    open = false;
    document.getElementById('cosmos-panel').classList.remove('cmos-open');
    document.getElementById('cosmos-btn').textContent = '🔭';
  }

  // ── Events ───────────────────────────────────────────────────────────────────
  function wire() {
    document.getElementById('cosmos-btn').addEventListener('click', () => open ? closePanel() : openPanel());
    document.querySelector('.cmos-x').addEventListener('click', closePanel);
    const inp = document.getElementById('cmos-inp');
    document.getElementById('cmos-send').addEventListener('click', () => ask(inp.value));
    inp.addEventListener('keydown', e => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); ask(inp.value); } });
    inp.addEventListener('input', () => { inp.style.height = 'auto'; inp.style.height = Math.min(inp.scrollHeight, 96) + 'px'; });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && open) closePanel(); });
  }

  // ── Boot ─────────────────────────────────────────────────────────────────────
  function init() { mount(); wire(); }
  document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', init) : init();

})();
