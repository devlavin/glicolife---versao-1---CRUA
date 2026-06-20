<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Conheça a Gotinha</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/stylegotinha.css">
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
            <a href="index.php" style="text-decoration:none; display:flex;">
                <span class="glico">Glico</span><span class="life">Life</span>
            </a>
        </div>
        <ul class="menu">
            <li><a href="index.php">Início</a></li>
        </ul>
    </nav>

    <section class="gotinha-hero">
        <div class="gotinha-hero-texto">
            <span class="hero-badge">Mascote oficial</span>
            <h1>Olá! Eu sou a<br><span class="highlight">Gotinha</span> 🩸</h1>
            <p>
                Sou a mascote do GlicoLife e estou aqui pra te acompanhar em cada medição,
                cada refeição e cada passo da sua jornada com o diabetes.
                Quando você estiver bem, eu estarei comemorando junto com você!
            </p>
            <div class="buttons">
                <a href="cadastro.php"><button class="primary">Criar conta</button></a>
                <button class="btn-secondary"
                    onclick="document.getElementById('expressoes').scrollIntoView({behavior:'smooth'})">Ver minhas
                    expressões</button>
            </div>
        </div>

        <div class="gotinha-mascote-wrap">
            <div class="gotinha-blob"></div>
            <span class="sparkle" style="top:-10px;left:20px;animation-delay:0s">✨</span>
            <span class="sparkle" style="top:30px;right:-5px;animation-delay:0.6s">✦</span>
            <span class="sparkle" style="bottom:40px;left:5px;animation-delay:1.2s">✧</span>
            <img src="img/gota-mascote.png" alt="Gotinha, mascote do GlicoLife">
        </div>
    </section>

    <section class="historia-section">
        <div class="historia-wrap">
            <span class="hero-badge">Minha história</span>
            <h2>Por que eu existo?</h2>
            <p>
                Viver com diabetes pode ser desafiador — são medições, medicamentos, dieta e tantos
                cuidados diários. O GlicoLife nasceu pra tornar essa rotina mais leve, e eu, a Gotinha,
                nasci pra ser o rosto dessa leveza.
            </p>
            <p>
                Sou uma gotinha de sangue, sim — mas cheia de vida, expressão e carinho!
                Fui criada pra ser sua companheira no aplicativo: celebro quando sua glicose
                está no lugar certo, fico preocupada quando está alta e um pouco tontinha quando
                está baixa. Porque aqui, seus dados têm sentimento.
            </p>
            <p>
                Meu objetivo é simples: fazer você se sentir acompanhado, nunca sozinho
                nessa jornada. 💚
            </p>
        </div>
    </section>

    <section class="expressoes-section" id="expressoes">
        <h2>Minhas expressões</h2>
        <p class="expressoes-sub">Minha carinha muda de acordo com sua glicose — assim você sabe de um olhar como está
        </p>

        <div class="expressoes-grid">

            <div class="expressao-card">
                <div class="expressao-img-wrap">
                    <img src="img/gota-normal.png" alt="Gotinha comemorando — glicose normal">
                </div>
                <span class="expressao-badge badge-normal">✅ Normal</span>
                <h3>Comemorando!</h3>
                <p>
                    Quando sua glicose está entre 70 e 180 mg/dL, fico assim —
                    feliz, animada e cheia de energia. Continue assim!
                </p>
            </div>

            <div class="expressao-card">
                <div class="expressao-img-wrap">
                    <img src="img/gota-alta.png" alt="Gotinha preocupada — glicose alta">
                </div>
                <span class="expressao-badge badge-alta">🔴 Alta</span>
                <h3>Preocupada...</h3>
                <p>
                    Glicose acima de 180 mg/dL me deixa assim — de olho em você.
                    Evite doces, beba água e descanse um pouco, tá?
                </p>
            </div>

            <div class="expressao-card">
                <div class="expressao-img-wrap">
                    <img src="img/gota-baixa.png" alt="Gotinha tonta — glicose baixa">
                </div>
                <span class="expressao-badge badge-baixa">🟡 Baixa</span>
                <h3>Um pouco tontinha...</h3>
                <p>
                    Abaixo de 70 mg/dL fico assim — tontinha e cansada.
                    Coma algo leve logo! Sua segurança é o mais importante.
                </p>
            </div>

        </div>
    </section>

    <section class="gotinha-cta">
        <h2>Vamos juntos nessa jornada? 🩸</h2>
        <p>Crie sua conta e me encontre dentro do GlicoLife!</p>
        <div class="gotinha-cta-btns">
            <a href="cadastro.php"><button class="primary">Criar conta grátis</button></a>
        </div>
    </section>

    <footer class="footer">
        <div class="footer-wrap">
            <div class="footer-logo">
                <span class="glico">Glico</span><span class="life">Life</span>
                <p>Cuidando de você com tecnologia e empatia.</p>
            </div>
            <p class="footer-copy">© 2026 GlicoLife. Todos os direitos reservados.</p>
        </div>
    </footer>

</body>

</html>