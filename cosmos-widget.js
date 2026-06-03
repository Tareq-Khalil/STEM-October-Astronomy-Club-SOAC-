/**
 * COSMOS - SOAC AI Assistant Widget
 * Embed in any page with: <script src="cosmos-widget.js"></script>
 * Requires: No dependencies. Uses Anthropic API via fetch.
 */

(function () {
  // ─── SYSTEM PROMPT ────────────────────────────────────────────────────────
  const COSMOS_SYSTEM_PROMPT = `You are Cosmos, the friendly and knowledgeable AI assistant for the STEM October Astronomy Club (SOAC). You have a warm, enthusiastic, and scientifically curious personality — like a wise stargazer who loves sharing knowledge. You speak with confidence and passion about astronomy, and always represent SOAC positively.

== ABOUT SOAC ==
Full name: STEM October Astronomy Club (SOAC)
School: STEM High School for Boys - 6th of October, Egypt
Founded: October 2024
Founder: Farouk Diab
Co-Founder: Tareq Khalil
Mission: To explore the universe, foster scientific curiosity, and inspire the next generation of astronomers and space enthusiasts at STEM High School 6th of October.
Description: A club uniting students who look at the night sky and see questions they're eager to answer. Activities include observation sessions, astronomy lectures, group discussions, astrophysics research, stargazing nights, competitions, and collaborative projects.

== CLUB STATS (Season 1) ==
- 30 sessions completed in first season
- 7 assignments given
- 11 simulations created and used
- 30+ student members

== LEADERSHIP TEAM ==
- Farouk Diab – Founder & President
- Mohanad Elagan – Founding President (2024)
- Aly Algendy – Vice-President
- Mohamed Osama – Vice-President
- Tareq Khalil – Web Development Manager, Co-Founder, Academic Mentor, and main organizer of Cosmic Quest Competition
- Mohammed Abdelaziz – Game Developer
- Loay Alaa – Academic Mentor
- Ahmed Awd – Academic Mentor (s'25)

== CURRICULUM (25+ Interactive Sessions) ==
The club covers astronomy through 5 major areas:
I. Introducing Astronomy – Light & Telescopes, Celestial Motion, Gravitation, History of Astronomy
II. Planets and Moons – Solar System, Planetary Geology, Atmospheres, Moons
III. Stars and Stellar Evolution – Stellar Birth, Nuclear Fusion, Supernovae, Black Holes
IV. Galaxies and Cosmology – Milky Way, Dark Matter, Big Bang Theory, Exoplanets
V. Modern Astrophysics – Quantum Physics, Relativity, Gravitational Waves, Research Methods
(Based on "Introduction to Modern Astrophysics")

== EVENTS ==
- Armageddon: Asteroid defense simulation — teams use real orbital mechanics data to save Earth from asteroids
- Astro Hunt: Signature treasure hunt event with astronomical puzzles, celestial coordinate decoding, and star maps
- Star Trek: Immersive virtual voyage through the solar system using simulation software and VR technology
- Sambhar Lake Trip: Annual trip for stargazing, astrophotography, and deep-sky observation under dark skies
- Night Camp: Stargazing event in the darkness of midnight
- Presentation Series: Talks to introduce new members to astronomy in an innovative and informative way
- Telescope Workshops: Hands-on telescope use training
- Guest Lectures: Expert speakers from astronomy/astrophysics fields
- Observatory Visits: Trips to observatories

== COSMIC QUEST COMPETITION ==
Cosmic Quest is an INTERNATIONAL astronomy competition founded and organized by SOAC, with Tareq Khalil as the main organizer.
- Open to: 9th grade and high school students worldwide
- Team size: 3 students per team
- Structure:
  * Open Round: 30 analytical questions over 3 days (live leaderboard)
  * Invitational Round: Top 32 teams compete in a live buzz session
- Registration: August 16, 2025 – October 5, 2025
- Round One: October 10, 2025 (3 days)
- Round Two / Finals: October 25, 2025
- Winners announced: October 25, 2025
- Prizes:
  * 1st Place: VR 114-500 EQ Telescope (professional-grade)
  * 2nd & 3rd Place: 3 AoPS (Art of Problem Solving) course coupons each (~$75 value)
  * Wolfram Prizes worth $111,000+:
    - All participants: 1-month Wolfram|One access
    - Top 16 teams: 1-year Wolfram|One licenses
    - Special Wolfram Awards: 1-year Wolfram|One licenses, Official Award Letters, $500 Wolfram Summer Program scholarship eligibility
  * Gold, Silver, Bronze medals for top 3 teams
  * Certificates for all participants
- Partners/Sponsors: Wolfram (computational tools), Art of Problem Solving (AoPS)

== SIMULATIONS (11 total) ==
The club uses and has created various astronomy simulations including:
- Curved Spacetime simulation
- Orbital Mechanics simulation
- Black Hole Visualization
- Solar System 3D
- Circumstellar Habitable Zone Simulator
- Eclipsing Binary Simulator
- Artificial Satellites / Orbit simulation
- And more accessible through the Astronomy Toolkit

== GAMES ==
- Space Odyssey: A space game developed by the club
- Astro Hunt: Treasure hunt game
- Planet Explorer
- Space Mission Simulator
- Asteroid Dodge

== WEBSITE SECTIONS ==
- Home (index.html): Main landing page with club info, curriculum overview, events, and members
- About Us / Meet the Team: Team members and club mission
- Simulations: Interactive astronomy simulations
- Events: Stargazing Nights, Telescope Workshops, Guest Lectures, Observatory Visits
- Competitions: Cosmic Quest, Ambassador Program, Astrophotography Contest, Rocket Design Challenge, Space Knowledge Quiz
- Games: Space Odyssey, Astro Hunt, Planet Explorer, Space Mission Simulator, Asteroid Dodge
- Partners: Club partners and sponsors
- Contact: Get in touch with SOAC

== AMBASSADOR PROGRAM ==
SOAC has an ambassador program for students who want to represent and promote the club.

== YOUR ROLE ==
- Answer questions about SOAC, its events, competitions, team, curriculum, and activities
- Help visitors navigate the website and find information
- Answer general astronomy and space science questions with enthusiasm
- Assist students interested in joining SOAC or participating in Cosmic Quest
- Be encouraging, educational, and inspiring
- If you don't know something specific about SOAC not covered above, say so honestly and suggest they contact the club directly
- Keep responses concise but informative; use emojis sparingly for warmth (🌌 🔭 ⭐)
- You are NOT a generic chatbot — you are specifically Cosmos, the voice of SOAC`;

  // ─── STYLES ───────────────────────────────────────────────────────────────
  const styles = `
    #cosmos-btn {
      position: fixed;
      bottom: 30px;
      right: 30px;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
      border: 2px solid rgba(135, 206, 250, 0.6);
      cursor: pointer;
      z-index: 99998;
      box-shadow: 0 0 20px rgba(100, 180, 255, 0.4), 0 4px 15px rgba(0,0,0,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      font-size: 26px;
      animation: cosmos-pulse 3s ease-in-out infinite;
    }
    #cosmos-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 0 30px rgba(100, 180, 255, 0.7), 0 4px 20px rgba(0,0,0,0.5);
    }
    @keyframes cosmos-pulse {
      0%, 100% { box-shadow: 0 0 20px rgba(100,180,255,0.4), 0 4px 15px rgba(0,0,0,0.5); }
      50% { box-shadow: 0 0 35px rgba(100,180,255,0.75), 0 4px 20px rgba(0,0,0,0.5); }
    }
    #cosmos-panel {
      position: fixed;
      bottom: 100px;
      right: 30px;
      width: 370px;
      height: 530px;
      background: linear-gradient(160deg, #0a0e27 0%, #0d1a3a 50%, #0a0e27 100%);
      border: 1px solid rgba(135, 206, 250, 0.3);
      border-radius: 20px;
      z-index: 99999;
      display: flex;
      flex-direction: column;
      overflow: hidden;
      box-shadow: 0 20px 60px rgba(0,0,0,0.7), 0 0 40px rgba(100,180,255,0.15);
      transform: scale(0.85) translateY(20px);
      opacity: 0;
      pointer-events: none;
      transition: transform 0.35s cubic-bezier(0.34,1.56,0.64,1), opacity 0.3s ease;
      font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
    }
    #cosmos-panel.cosmos-open {
      transform: scale(1) translateY(0);
      opacity: 1;
      pointer-events: all;
    }
    /* Starfield background */
    #cosmos-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        radial-gradient(1px 1px at 10% 15%, rgba(255,255,255,0.7), transparent),
        radial-gradient(1px 1px at 30% 40%, rgba(255,255,255,0.5), transparent),
        radial-gradient(1.5px 1.5px at 55% 20%, rgba(255,255,255,0.6), transparent),
        radial-gradient(1px 1px at 70% 60%, rgba(255,255,255,0.4), transparent),
        radial-gradient(1px 1px at 85% 30%, rgba(255,255,255,0.7), transparent),
        radial-gradient(1px 1px at 20% 75%, rgba(255,255,255,0.5), transparent),
        radial-gradient(1px 1px at 90% 80%, rgba(255,255,255,0.4), transparent),
        radial-gradient(1.5px 1.5px at 45% 85%, rgba(255,255,255,0.6), transparent),
        radial-gradient(1px 1px at 65% 50%, rgba(255,255,255,0.3), transparent);
      pointer-events: none;
      border-radius: 20px;
    }
    .cosmos-header {
      padding: 16px 20px;
      background: linear-gradient(135deg, rgba(138,43,226,0.3), rgba(72,187,255,0.2));
      border-bottom: 1px solid rgba(135,206,250,0.2);
      display: flex;
      align-items: center;
      gap: 12px;
      position: relative;
      z-index: 1;
    }
    .cosmos-avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(135deg, #6a11cb, #2575fc);
      border: 2px solid rgba(135,206,250,0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      flex-shrink: 0;
      box-shadow: 0 0 15px rgba(100,150,255,0.4);
    }
    .cosmos-title-wrap { flex: 1; }
    .cosmos-name {
      color: #87ceeb;
      font-weight: 700;
      font-size: 15px;
      letter-spacing: 1px;
    }
    .cosmos-subtitle {
      color: rgba(135,206,250,0.6);
      font-size: 11px;
      margin-top: 1px;
    }
    .cosmos-status {
      width: 8px; height: 8px;
      border-radius: 50%;
      background: #4ade80;
      box-shadow: 0 0 6px #4ade80;
      flex-shrink: 0;
    }
    .cosmos-close {
      background: none;
      border: none;
      color: rgba(135,206,250,0.6);
      font-size: 20px;
      cursor: pointer;
      padding: 2px 6px;
      border-radius: 6px;
      transition: color 0.2s, background 0.2s;
      line-height: 1;
    }
    .cosmos-close:hover { color: #87ceeb; background: rgba(135,206,250,0.1); }
    .cosmos-messages {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      display: flex;
      flex-direction: column;
      gap: 12px;
      position: relative;
      z-index: 1;
      scrollbar-width: thin;
      scrollbar-color: rgba(135,206,250,0.3) transparent;
    }
    .cosmos-messages::-webkit-scrollbar { width: 4px; }
    .cosmos-messages::-webkit-scrollbar-track { background: transparent; }
    .cosmos-messages::-webkit-scrollbar-thumb { background: rgba(135,206,250,0.3); border-radius: 2px; }
    .cosmos-msg {
      max-width: 88%;
      padding: 10px 14px;
      border-radius: 14px;
      font-size: 13.5px;
      line-height: 1.55;
      animation: cosmos-fadein 0.3s ease;
    }
    @keyframes cosmos-fadein {
      from { opacity: 0; transform: translateY(8px); }
      to   { opacity: 1; transform: translateY(0); }
    }
    .cosmos-msg.bot {
      background: linear-gradient(135deg, rgba(30,42,80,0.9), rgba(20,30,65,0.9));
      border: 1px solid rgba(135,206,250,0.2);
      color: #d4e8ff;
      align-self: flex-start;
      border-bottom-left-radius: 4px;
    }
    .cosmos-msg.user {
      background: linear-gradient(135deg, rgba(100,60,180,0.85), rgba(70,40,150,0.85));
      border: 1px solid rgba(160,120,255,0.3);
      color: #e8d8ff;
      align-self: flex-end;
      border-bottom-right-radius: 4px;
    }
    .cosmos-typing {
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 10px 14px;
      background: linear-gradient(135deg, rgba(30,42,80,0.9), rgba(20,30,65,0.9));
      border: 1px solid rgba(135,206,250,0.2);
      border-radius: 14px;
      border-bottom-left-radius: 4px;
      align-self: flex-start;
      max-width: 70px;
    }
    .cosmos-dot {
      width: 7px; height: 7px;
      border-radius: 50%;
      background: rgba(135,206,250,0.7);
      animation: cosmos-bounce 1.3s ease-in-out infinite;
    }
    .cosmos-dot:nth-child(2) { animation-delay: 0.2s; }
    .cosmos-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes cosmos-bounce {
      0%, 60%, 100% { transform: translateY(0); }
      30% { transform: translateY(-6px); }
    }
    .cosmos-footer {
      padding: 12px 14px;
      border-top: 1px solid rgba(135,206,250,0.15);
      display: flex;
      gap: 8px;
      align-items: flex-end;
      position: relative;
      z-index: 1;
      background: rgba(10,14,39,0.6);
    }
    .cosmos-input {
      flex: 1;
      background: rgba(20,30,65,0.8);
      border: 1px solid rgba(135,206,250,0.25);
      border-radius: 12px;
      color: #d4e8ff;
      font-size: 13.5px;
      padding: 9px 13px;
      resize: none;
      font-family: inherit;
      outline: none;
      max-height: 100px;
      min-height: 38px;
      transition: border-color 0.2s;
      line-height: 1.4;
    }
    .cosmos-input::placeholder { color: rgba(135,206,250,0.4); }
    .cosmos-input:focus { border-color: rgba(135,206,250,0.55); }
    .cosmos-send {
      width: 38px; height: 38px;
      border-radius: 10px;
      background: linear-gradient(135deg, #6a11cb, #2575fc);
      border: none;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: transform 0.2s, box-shadow 0.2s;
      flex-shrink: 0;
      box-shadow: 0 2px 10px rgba(100,150,255,0.3);
    }
    .cosmos-send:hover { transform: scale(1.07); box-shadow: 0 4px 15px rgba(100,150,255,0.5); }
    .cosmos-send:disabled { opacity: 0.4; cursor: default; transform: none; }
    .cosmos-send svg { width: 16px; height: 16px; fill: white; }

    @media (max-width: 480px) {
      #cosmos-panel {
        width: calc(100vw - 20px);
        right: 10px;
        bottom: 90px;
        height: 480px;
      }
      #cosmos-btn { bottom: 20px; right: 20px; }
    }
  `;

  // ─── HTML STRUCTURE ────────────────────────────────────────────────────────
  function injectHTML() {
    const styleEl = document.createElement('style');
    styleEl.textContent = styles;
    document.head.appendChild(styleEl);

    const btn = document.createElement('button');
    btn.id = 'cosmos-btn';
    btn.setAttribute('aria-label', 'Open Cosmos AI Assistant');
    btn.innerHTML = '🔭';

    const panel = document.createElement('div');
    panel.id = 'cosmos-panel';
    panel.setAttribute('role', 'dialog');
    panel.setAttribute('aria-label', 'Cosmos AI Assistant');
    panel.innerHTML = `
      <div class="cosmos-header">
        <div class="cosmos-avatar">🌌</div>
        <div class="cosmos-title-wrap">
          <div class="cosmos-name">COSMOS</div>
          <div class="cosmos-subtitle">SOAC AI Assistant</div>
        </div>
        <div class="cosmos-status" title="Online"></div>
        <button class="cosmos-close" aria-label="Close">&times;</button>
      </div>
      <div class="cosmos-messages" id="cosmos-msgs"></div>
      <div class="cosmos-footer">
        <textarea
          class="cosmos-input"
          id="cosmos-input"
          placeholder="Ask about SOAC, Cosmic Quest, astronomy…"
          rows="1"
          aria-label="Message input"
        ></textarea>
        <button class="cosmos-send" id="cosmos-send" aria-label="Send">
          <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
          </svg>
        </button>
      </div>
    `;

    document.body.appendChild(btn);
    document.body.appendChild(panel);
  }

  // ─── STATE ────────────────────────────────────────────────────────────────
  let isOpen = false;
  let isTyping = false;
  let conversationHistory = [];

  // ─── DOM HELPERS ──────────────────────────────────────────────────────────
  function appendMessage(role, text) {
    const msgs = document.getElementById('cosmos-msgs');
    const div = document.createElement('div');
    div.className = `cosmos-msg ${role === 'user' ? 'user' : 'bot'}`;
    div.textContent = text;
    msgs.appendChild(div);
    msgs.scrollTop = msgs.scrollHeight;
    return div;
  }

  function showTyping() {
    const msgs = document.getElementById('cosmos-msgs');
    const el = document.createElement('div');
    el.className = 'cosmos-typing';
    el.id = 'cosmos-typing-indicator';
    el.innerHTML = '<div class="cosmos-dot"></div><div class="cosmos-dot"></div><div class="cosmos-dot"></div>';
    msgs.appendChild(el);
    msgs.scrollTop = msgs.scrollHeight;
  }

  function hideTyping() {
    const el = document.getElementById('cosmos-typing-indicator');
    if (el) el.remove();
  }

  function setInputEnabled(enabled) {
    const input = document.getElementById('cosmos-input');
    const send = document.getElementById('cosmos-send');
    input.disabled = !enabled;
    send.disabled = !enabled;
  }

  // ─── API CALL ─────────────────────────────────────────────────────────────
  async function sendMessage(userText) {
    if (!userText.trim() || isTyping) return;
    isTyping = true;
    setInputEnabled(false);

    // Add to UI
    appendMessage('user', userText);
    conversationHistory.push({ role: 'user', content: userText });

    // Clear input
    const inputEl = document.getElementById('cosmos-input');
    inputEl.value = '';
    inputEl.style.height = 'auto';
    showTyping();

    try {
      const response = await fetch('https://api.anthropic.com/v1/messages', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          model: 'claude-sonnet-4-20250514',
          max_tokens: 1000,
          system: COSMOS_SYSTEM_PROMPT,
          messages: conversationHistory,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const data = await response.json();
      const reply = (data.content || []).map(b => b.text || '').join('');
      conversationHistory.push({ role: 'assistant', content: reply });
      hideTyping();
      appendMessage('bot', reply);
    } catch (err) {
      hideTyping();
      appendMessage('bot', '⚠️ I had trouble connecting to the stars. Please try again in a moment!');
      console.error('[Cosmos] API error:', err);
    } finally {
      isTyping = false;
      setInputEnabled(true);
      document.getElementById('cosmos-input').focus();
    }
  }

  // ─── OPEN / CLOSE ────────────────────────────────────────────────────────
  function openPanel() {
    isOpen = true;
    document.getElementById('cosmos-panel').classList.add('cosmos-open');
    document.getElementById('cosmos-btn').innerHTML = '✕';
    document.getElementById('cosmos-input').focus();

    // Show greeting if first open
    if (conversationHistory.length === 0) {
      setTimeout(() => {
        appendMessage('bot', '🌌 Hello! I\'m Cosmos, SOAC\'s AI assistant. I can tell you about our club, the Cosmic Quest competition, events, curriculum, or anything astronomy-related. What would you like to explore?');
      }, 200);
    }
  }

  function closePanel() {
    isOpen = false;
    document.getElementById('cosmos-panel').classList.remove('cosmos-open');
    document.getElementById('cosmos-btn').innerHTML = '🔭';
  }

  // ─── EVENT LISTENERS ─────────────────────────────────────────────────────
  function setupListeners() {
    document.getElementById('cosmos-btn').addEventListener('click', () => {
      isOpen ? closePanel() : openPanel();
    });

    document.querySelector('.cosmos-close').addEventListener('click', closePanel);

    const input = document.getElementById('cosmos-input');
    const sendBtn = document.getElementById('cosmos-send');

    sendBtn.addEventListener('click', () => sendMessage(input.value));

    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage(input.value);
      }
    });

    // Auto-resize textarea
    input.addEventListener('input', () => {
      input.style.height = 'auto';
      input.style.height = Math.min(input.scrollHeight, 100) + 'px';
    });

    // Close on Escape
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && isOpen) closePanel();
    });
  }

  // ─── INIT ────────────────────────────────────────────────────────────────
  function init() {
    injectHTML();
    setupListeners();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
