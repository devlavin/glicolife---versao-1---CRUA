// Carrossel 

const track  = document.getElementById('gTrack');
const cards  = Array.from(track.querySelectorAll('.gc-card'));
const dotsEl = document.getElementById('gDots');
const btnP   = document.getElementById('gPrev');
const btnN   = document.getElementById('gNext');

let cur = 0;

function vis()  { return window.innerWidth <= 480 ? 1 : window.innerWidth <= 900 ? 2 : 3; }
function maxI() { return Math.max(0, cards.length - vis()); }

function buildDots() {
  dotsEl.innerHTML = '';

  for (let i = 0; i <= maxI(); i++) {
    const d = document.createElement('button');
    d.className = 'gc-dot' + (i === cur ? ' on' : '');
    d.onclick = () => go(i);
    dotsEl.appendChild(d);
  }
}

function go(n) {
  cur = Math.max(0, Math.min(n, maxI()));

  const w = cards[0].offsetWidth + 18;
  track.style.transform = `translateX(-${cur * w}px)`;

  dotsEl.querySelectorAll('.gc-dot').forEach((d, i) => d.classList.toggle('on', i === cur));

  btnP.disabled = cur === 0;
  btnN.disabled = cur === maxI();
}

btnP.onclick = () => go(cur - 1);
btnN.onclick = () => go(cur + 1);

// Touch

let tx = 0;

track.addEventListener('touchstart', e => {
  tx = e.touches[0].clientX;
}, { passive: true });

track.addEventListener('touchend', e => {
  const dx = e.changedTouches[0].clientX - tx;
  if (Math.abs(dx) > 40) go(cur + (dx < 0 ? 1 : -1));
});

window.addEventListener('resize', () => {
  buildDots();
  go(Math.min(cur, maxI()));
});

buildDots();
go(0);


// ─── Menu — scroll

document.querySelectorAll('.menu a[href^="#"]').forEach(link => {
  link.addEventListener('click', function (e) {
    e.preventDefault();

    const destino = document.querySelector(this.getAttribute('href'));
    if (destino) destino.scrollIntoView({ behavior: 'smooth' });

    // Fecha menu mobile se estiver aberto
    const menu = document.querySelector('.menu');
    if (menu) menu.classList.remove('aberto');
  });
});


// Toast 

const toast = document.querySelector('.form-toast');

if (toast) {
  setTimeout(() => {
    toast.style.transition = 'opacity 0.5s';
    toast.style.opacity = '0';

    setTimeout(() => toast.remove(), 500);
  }, 3000);
}