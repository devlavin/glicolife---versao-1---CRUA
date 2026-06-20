<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GlicoLife</title>
  <link rel="stylesheet" href="css/index.css">
  <!-- PWA -->
  <link rel="manifest" href="/glicolife/manifest.json">
  <meta name="theme-color" content="#1a9e7a">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-title" content="GlicoLife">
  <link rel="apple-touch-icon" href="/img/icon-192.png">

</head>

<body>

  <nav class="navbar">
    <div class="logo-wrap">
      <span class="glico">Glico</span><span class="life">Life</span>
    </div>
    <ul class="menu">
      <li><a href="index.php">Início</a></li>
      <li><a href="#sobre">Sobre</a></li>
      <li><a href="#contato">Contato</a></li>
      <li><a href="login.php" class="login-btn">Entrar</a></li>
    </ul>
  </nav>

  <div class="hero">
    <div class="hero-glow"></div>

    <div class="info">
      <span class="hero-badge">Saúde inteligente</span>
      <h1>Controle seu<br><span class="highlight">diabetes</span><br>com leveza</h1>
      <p>
        Um sistema criado para ajudar pessoas com diabetes a controlar a glicose,
        lembrar de medicamentos e melhorar a alimentação de forma simples e inteligente.
      </p>
      <div class="buttons">
        <a href="cadastro.php">
          <button class="primary">Criar conta</button>
        </a>
        <a href="gotinhag.php">
          <button class="btn-secondary">Conheça a Gotinha</button>
        </a>
      </div>
    </div>

    <div class="hero-mascote">
      <span class="sparkle" style="top:-10px;left:20px;animation-delay:0s">✨</span>
      <span class="sparkle" style="top:30px;right:-5px;animation-delay:0.6s">✦</span>
      <span class="sparkle" style="bottom:40px;left:5px;animation-delay:1.2s">✧</span>
      <img src="img/gota-mascote.png" alt="Gotinha, mascote do GlicoLife" class="mascote-img" />
    </div>
  </div>

  <section class="gc-section">
    <p class="gc-label">O que o GlicoLife oferece</p>

    <div class="gc-wrap">
      <button class="gc-nav gc-prev" id="gPrev" aria-label="Anterior">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round">
          <polyline points="15 18 9 12 15 6" />
        </svg>
      </button>

      <div class="gc-window">
        <div class="gc-track" id="gTrack">

          <div class="gc-card">
            <div class="gc-icon" style="background:linear-gradient(135deg,#1a9e7a,#00b4d8)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
              </svg>
            </div>
            <h3>Glicose</h3>
            <p>Registre e acompanhe seus níveis de glicemia diariamente com clareza.</p>
            <span class="gc-tag">Monitoramento</span>
          </div>

          <div class="gc-card">
            <div class="gc-icon" style="background:linear-gradient(135deg,#3a7bd5,#6a5acd)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                stroke-linecap="round">
                <rect x="3" y="3" width="18" height="18" rx="2" />
                <line x1="12" y1="8" x2="12" y2="16" />
                <line x1="8" y1="12" x2="16" y2="12" />
              </svg>
            </div>
            <h3>Remédios</h3>
            <p>Lembretes precisos para não esquecer nenhuma dose das suas medicações.</p>
            <span class="gc-tag">Alertas</span>
          </div>

          <div class="gc-card">
            <div class="gc-icon" style="background:linear-gradient(135deg,#43e97b,#1a9e7a)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                stroke-linecap="round">
                <path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z" />
              </svg>
            </div>
            <h3>Alimentação</h3>
            <p>Sugestões de alimentos e cuidados com a dieta para diabéticos.</p>
            <p style="font-size:11px;color:#8aab9e;margin-top:6px">🔬 Em desenvolvimento — aguardando parceria com
              nutricionista</p>
            <span class="gc-tag">Nutrição</span>
          </div>

          <div class="gc-card">
            <div class="gc-icon" style="background:linear-gradient(135deg,#f7971e,#ffd200)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                stroke-linecap="round">
                <line x1="18" y1="20" x2="18" y2="10" />
                <line x1="12" y1="20" x2="12" y2="4" />
                <line x1="6" y1="20" x2="6" y2="14" />
              </svg>
            </div>
            <h3>Histórico</h3>
            <p>Veja sua evolução ao longo do tempo em relatórios detalhados.</p>
            <span class="gc-tag">Relatórios</span>
          </div>

          <div class="gc-card">
            <div class="gc-icon" style="background:linear-gradient(135deg,#f953c6,#b91d73)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"
                stroke-linecap="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="12" y1="8" x2="12" y2="12" />
                <line x1="12" y1="16" x2="12.01" y2="16" />
              </svg>
            </div>
            <h3>Dicas</h3>
            <p>Conteúdo educativo e informações confiáveis sobre o diabetes.</p>
            <span class="gc-tag">Educação</span>
          </div>

        </div>
      </div>

      <button class="gc-nav gc-next" id="gNext" aria-label="Próximo">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round">
          <polyline points="9 18 15 12 9 6" />
        </svg>
      </button>
    </div>

    <div class="gc-dots" id="gDots"></div>
  </section>

  <!-- SOBRE -->
  <section class="sobre-section" id="sobre">
    <div class="sobre-wrap">

      <div class="sobre-texto">
        <span class="hero-badge">Sobre o GlicoLife</span>
        <h2>Um projeto feito com<br><span class="highlight">propósito</span></h2>
        <p>
          O GlicoLife nasceu da vontade de tornar o controle do diabetes mais simples,
          humano e acessível. Sabemos que conviver com o diabetes exige atenção constante —
          e por isso criamos uma plataforma que centraliza tudo em um só lugar.
        </p>
        <p>
          Aqui você registra sua glicose, organiza seus medicamentos, acompanha sua alimentação
          e visualiza sua evolução com clareza. Tudo pensado para que você foque no que
          realmente importa: sua saúde e qualidade de vida.
        </p>
        <a href="cadastro.php">
          <button class="primary" style="margin-top: 24px;">Comece agora</button>
        </a>
      </div>

      <div class="sobre-cards">
        <div class="sobre-card">
          <div class="sobre-card-icon" style="background:linear-gradient(135deg,#1a9e7a,#00b4d8)">🎯</div>
          <h4>Missão</h4>
          <p>Empoderar pessoas com diabetes a terem mais controle, autonomia e tranquilidade no dia a dia.</p>
        </div>
        <div class="sobre-card">
          <div class="sobre-card-icon" style="background:linear-gradient(135deg,#3a7bd5,#6a5acd)">👁️</div>
          <h4>Visão</h4>
          <p>Ser a plataforma de saúde mais querida por quem vive com diabetes no Brasil.</p>
        </div>
        <div class="sobre-card">
          <div class="sobre-card-icon" style="background:linear-gradient(135deg,#43e97b,#1a9e7a)">💚</div>
          <h4>Valores</h4>
          <p>Empatia, simplicidade e compromisso com o bem-estar de cada usuário.</p>
        </div>
      </div>

    </div>
  </section>

  <section class="contato-section" id="contato">
    <div class="contato-wrap">

      <div class="contato-texto">
        <span class="hero-badge">Fale conosco</span>
        <h2>Tem alguma <span class="highlight">dúvida</span>?</h2>
        <p>
          Manda uma mensagem pra gente. Respondemos todos os contatos
          com carinho e atenção.
        </p>
        <div class="contato-info">
          <div class="contato-info-item">
            <span class="contato-info-icon">📧</span>
            <span>glicolife.site@gmail.com</span>
          </div>
          <div class="contato-info-item">
            <span class="contato-info-icon">⏱️</span>
            <span>Respondemos em até 24h</span>
          </div>
        </div>
      </div>

      <form class="contato-form" action="enviar_contato.php" method="POST">
        <?php if (isset($_GET['enviado'])): ?>
          <div class="form-toast form-toast-ok">✅ Mensagem enviada com sucesso!</div>
        <?php elseif (isset($_GET['erro'])): ?>
          <div class="form-toast form-toast-erro">❌ Erro ao enviar. Tente novamente.</div>
        <?php endif; ?>

        <div class="form-campo">
          <input type="text" name="nome" placeholder="Seu nome" required>
        </div>
        <div class="form-campo">
          <input type="email" name="email" placeholder="Seu e-mail" required>
        </div>
        <div class="form-campo">
          <textarea name="mensagem" rows="4" placeholder="Sua mensagem..." required></textarea>
        </div>
        <button type="submit" class="primary" style="width:100%; justify-content:center;">
          Enviar mensagem
        </button>
      </form>

    </div>
  </section>

  <footer class="footer">
    <div class="footer-wrap">
      <div class="footer-logo">
        <span class="glico">Glico</span><span class="life">Life</span>
        <p>Cuidando de você com tecnologia e empatia.</p>
      </div>
      <a href="termos.php" style="color:#8aab9e; font-size:12px; text-decoration:none;">Termos de Uso</a>
      <p class="footer-copy">© 2026 GlicoLife. Todos os direitos reservados.</p>
    </div>
  </footer>

  <script src="js/jsindex.js"></script>
  <script>
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.register('/glicolife/service-worker.js')
    }
  </script>
</body>

</html>