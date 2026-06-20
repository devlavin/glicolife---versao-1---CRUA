// ── NAVBAR
function toggleMenu() {
  document.getElementById("submenu").classList.toggle("aberto");
}

function toggleHamburger() {
  const hamburger = document.getElementById("hamburger");
  const menu = document.getElementById("menu-nav");
  const extra = document.getElementById("menu-mobile-extra");

  hamburger.classList.toggle("aberto");
  menu.classList.toggle("aberto");
  extra.classList.toggle("aberto");

  // Posiciona o extra logo abaixo do menu principal
  if (extra.classList.contains("aberto")) {
    extra.style.top = 60 + menu.offsetHeight + "px";
  }
}

document.addEventListener("click", function (e) {
  const perfil = document.getElementById("perfil");
  if (perfil && !perfil.contains(e.target))
    document.getElementById("submenu").classList.remove("aberto");

  const wrap = document.getElementById("acessoWrap");
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById("acessoPanel").classList.remove("aberto");
    document.getElementById("btnAcesso").classList.remove("aberto");
  }
});

// ── PAINEL ACESSIBILIDADE
function toggleAcesso() {
  document.getElementById("acessoPanel").classList.toggle("aberto");
  document.getElementById("btnAcesso").classList.toggle("aberto");
}

// ── TOAST
const toast = document.getElementById("toast");
if (toast) {
  setTimeout(() => (toast.style.opacity = "0"), 3000);
  setTimeout(() => toast.remove(), 3500);
}

// ── TAMANHO DE FONTE
const FONT_KEY = "glicolife_fonte";
let fonteAtual = parseInt(localStorage.getItem(FONT_KEY)) || 16;

function aplicarFonte() {
  const escala = fonteAtual / 16;

  let tag = document.getElementById("_fonte_override");
  if (!tag) {
    tag = document.createElement("style");
    tag.id = "_fonte_override";
    document.head.appendChild(tag);
  }

  tag.textContent = `
        body, p, span, a, td, th, li, label,
        input, select, textarea, button, div {
            font-size: ${escala}rem !important;
        }
        h1 { font-size: ${(escala * 2).toFixed(3)}rem !important; }
        h2 { font-size: ${(escala * 1.5).toFixed(3)}rem !important; }
        h3 { font-size: ${(escala * 1.25).toFixed(3)}rem !important; }
        small, .text-sm { font-size: ${(escala * 0.875).toFixed(3)}rem !important; }
    `;

  // Base rem para qualquer coisa que use rem/em no CSS
  document.documentElement.style.fontSize = fonteAtual + "px";

  const btnMenos = document.getElementById("btn-fonte-menos");
  const btnMais = document.getElementById("btn-fonte-mais");
  if (btnMenos) btnMenos.disabled = fonteAtual <= 14;
  if (btnMais) btnMais.disabled = fonteAtual >= 26;
}

function aumentarFonte() {
  if (fonteAtual < 26) {
    fonteAtual += 1;
    localStorage.setItem(FONT_KEY, fonteAtual);
    aplicarFonte();
  }
}

function diminuirFonte() {
  if (fonteAtual > 14) {
    fonteAtual -= 1;
    localStorage.setItem(FONT_KEY, fonteAtual);
    aplicarFonte();
  }
}

aplicarFonte();

// ── VOZ
const VOZ_KEY = "glicolife_voz";
let vozAtiva = localStorage.getItem(VOZ_KEY) === "true";

/*
 Atualiza o visual do toggle de voz e visibilidade dos botões de microfone.
 Cada página pode chamar esta função após adicionar seus próprios mic-buttons.
 */
function aplicarEstadoVoz() {
  const chk = document.getElementById("toggleVozCheck");
  const label = document.getElementById("vozLabel");
  const temSuporte =
    "SpeechRecognition" in window || "webkitSpeechRecognition" in window;

  if (chk) chk.checked = vozAtiva;
  if (label) label.textContent = vozAtiva ? "Ligada" : "Desligada";

  // Mostra/oculta TODOS os botões de microfone da página de uma vez
  document.querySelectorAll('[id^="mic-"]').forEach((btn) => {
    btn.classList.toggle("hidden", !(vozAtiva && temSuporte));
  });
}

function onToggleVoz(ligada) {
  vozAtiva = ligada;
  localStorage.setItem(VOZ_KEY, vozAtiva);
  aplicarEstadoVoz();
  if (vozAtiva) falar("Voz ativada. Agora posso ler os resultados para você.");
}

function falar(texto) {
  if (!vozAtiva || !("speechSynthesis" in window)) return;
  speechSynthesis.cancel();
  const u = new SpeechSynthesisUtterance(texto);
  u.lang = "pt-BR";
  u.rate = 0.88;
  speechSynthesis.speak(u);
}

window.addEventListener("load", function () {
  aplicarEstadoVoz();
  const msg = document.body.getAttribute("data-voz");
  if (msg) falar(msg);
});

// ── MICROFONE
let micAtivo = false;

/**
 * Inicia o reconhecimento de voz para um campo.
 * @param {string} campoId  - sufixo do id do botão mic (ex: 'glicose' → id="mic-glicose")
 * @param {function} aoCapturar - callback(transcript: string)
 */
function iniciarMicrofone(campoId, aoCapturar) {
  if (!vozAtiva || micAtivo) return;
  const SR = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SR) return;

  const btn = document.getElementById("mic-" + campoId);
  const rec = new SR();
  rec.lang = "pt-BR";
  rec.interimResults = false;
  rec.maxAlternatives = 1;

  micAtivo = true;
  if (btn) {
    btn.classList.add("ouvindo");
    btn.disabled = true;
  }

  rec.start();

  rec.onresult = (e) => aoCapturar(e.results[0][0].transcript);

  rec.onerror = (e) => {
    const msgs = {
      "no-speech": "Não ouvi nada.",
      "not-allowed": "Permissão de microfone negada.",
      network: "Erro de rede.",
    };
    falar(msgs[e.error] || "Erro no microfone. Tente novamente.");
  };

  rec.onend = () => {
    micAtivo = false;
    if (btn) {
      btn.classList.remove("ouvindo");
      btn.disabled = false;
    }
  };
}
