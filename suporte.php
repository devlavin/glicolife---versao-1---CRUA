<?php
require 'sessao.php';
require 'conexao.php';

$enviado = isset($_GET['enviado']) && $_GET['enviado'] == 1;
$erro = isset($_GET['erro']) && $_GET['erro'] == 1;
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Ajuda & Suporte</title>
    <link rel="stylesheet" href="css/stylelogado.css">
    <link rel="stylesheet" href="css/stylesuporte.css">
    <link rel="manifest" href="/glicolife/manifest.json">
    <meta name="theme-color" content="#1a9e7a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="GlicoLife">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
</head>

<body>

    <nav class="navbar">
        <div class="navbar-logo">
            <span class="glico">Glico</span><span class="life">Life</span>
        </div>
        <button class="hamburger" id="hamburger" onclick="toggleHamburger()">
            <span></span><span></span><span></span>
        </button>
        <ul class="menu" id="menu-nav">
            <li><a href="home.php">Home</a></li>
            <li><a href="glicose.php">Glicose</a></li>
            <li><a href="medicamento.php">Medicamentos</a></li>
            <li><a href="alimentacao.php">Alimentação</a></li>
        </ul>
        <div class="menu-mobile-extra" id="menu-mobile-extra">
            <a href="dados.php">Conta</a>
            <a href="relatorio.php">Histórico & Relatórios</a>
            <a href="suporte.php">Ajuda & Suporte</a>
            <a href="logout.php" class="sair">Sair</a>
        </div>
        <div class="perfil" id="perfil">
            <span onclick="toggleMenu()"><?php echo explode(" ", $_SESSION['usuario_nome'])[0]; ?> ▾</span>
            <ul class="submenu" id="submenu">
                <li><a href="dados.php">Conta</a></li>
                <li><a href="relatorio.php">Histórico & Relatórios</a></li>
                <li><a href="suporte.php">Ajuda & Suporte</a></li>
                <li><a href="logout.php" class="sair">Sair</a></li>
            </ul>
        </div>
        <!-- PAINEL DE ACESSIBILIDADE -->
        <div class="acesso-wrap" id="acessoWrap">
            <button class="btn-acesso" id="btnAcesso" onclick="toggleAcesso()" title="Acessibilidade"
                aria-label="Opções de acessibilidade">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="5" r="2" />
                    <path d="M12 7v8m-4-5h8M9 19l-2 2m7-2l2 2" />
                </svg>
            </button>
            <div class="acesso-panel" id="acessoPanel">
                <div class="acesso-titulo">Acessibilidade</div>

                <div class="acesso-row">
                    <span class="acesso-label">Tamanho do texto</span>
                    <div class="fonte-btns">
                        <button id="btn-fonte-menos" onclick="diminuirFonte()" title="Diminuir texto"
                            aria-label="Diminuir texto">A−</button>
                        <button id="btn-fonte-mais" onclick="aumentarFonte()" title="Aumentar texto"
                            aria-label="Aumentar texto">A+</button>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <?php if ($enviado): ?>
        <div class="toast toast-ok"> Mensagem enviada! Retornaremos em breve.</div>
    <?php elseif ($erro): ?>
        <div class="toast toast-erro">❌ Erro ao enviar. Tente novamente.</div>
    <?php endif; ?>

    <div class="page-hero">
        <!-- <div class="hero-icon">🤝</div> -->
        <h2>Ajuda & Suporte</h2>
        <p>Estamos aqui para te ajudar a usar o GlicoLife</p>
    </div>

    <div class="suporte-layout">

        <div class="faq-section">
            <h3 class="section-title">Perguntas frequentes</h3>

            <div class="faq-list">
                <div class="faq-item">
                    <button class="faq-trigger" onclick="toggleFaq(this)">
                        <span class="faq-icon">❓</span>
                        <span>Como registrar uma medição de glicose?</span>
                        <span class="faq-chevron">▾</span>
                    </button>
                    <div class="faq-body">
                        Acesse <strong>Glicose</strong> no menu, preencha o valor em mg/dL, selecione o momento (jejum,
                        pré ou pós-refeição) e clique em <em>Salvar medição</em>.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-trigger" onclick="toggleFaq(this)">
                        <span class="faq-icon">💊</span>
                        <span>Como adicionar um medicamento?</span>
                        <span class="faq-chevron">▾</span>
                    </button>
                    <div class="faq-body">
                        Vá em <strong>Medicamentos</strong>, clique em <em>+ Registrar medicamento</em>, preencha o
                        nome, dosagem, via de administração, horário e frequência.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-trigger" onclick="toggleFaq(this)">
                        <span class="faq-icon">📊</span>
                        <span>Como ver meu histórico de glicose?</span>
                        <span class="faq-chevron">▾</span>
                    </button>
                    <div class="faq-body">
                        Acesse <strong>Histórico & Relatórios</strong> pelo menu de perfil (canto superior direito).
                        Você pode filtrar por 7, 14, 30 ou 90 dias e ver o gráfico de evolução.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-trigger" onclick="toggleFaq(this)">
                        <span class="faq-icon">🔑</span>
                        <span>Como alterar minha senha?</span>
                        <span class="faq-chevron">▾</span>
                    </button>
                    <div class="faq-body">
                        Vá em <strong>Conta</strong> (menu de perfil), clique em <em>Editar dados</em> e preencha os
                        campos de nova senha e confirmação.
                    </div>
                </div>

                <div class="faq-item">
                    <button class="faq-trigger" onclick="toggleFaq(this)">
                        <span class="faq-icon">⚠️</span>
                        <span>Os dados não aparecem, o que fazer?</span>
                        <span class="faq-chevron">▾</span>
                    </button>
                    <div class="faq-body">
                        Tente atualizar a página. Se o problema persistir, faça logout e entre novamente.
                        Certifique-se de que todos os campos obrigatórios foram preenchidos antes de salvar.
                    </div>
                </div>
            </div>
        </div>

        <div class="contato-card">
            <div class="contato-header">
                <div class="contato-icon">📩</div>
                <div>
                    <h3>Fale com o suporte</h3>
                    <p>Não encontrou o que precisava? Envie uma mensagem.</p>
                </div>
            </div>

            <form action="enviar_suporte.php" method="POST">
                <div class="campo">
                    <label for="nome">Seu nome</label>
                    <input type="text" id="nome" name="nome" placeholder="Ex: João Silva" required>
                </div>
                <div class="campo">
                    <label for="email">E-mail para resposta</label>
                    <input type="email" id="email" name="email" placeholder="seu@email.com" required>
                </div>
                <div class="campo">
                    <label for="assunto">Assunto</label>
                    <select id="assunto" name="assunto" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="duvida">Dúvida sobre uso</option>
                        <option value="erro">Erro no sistema</option>
                        <option value="conta">Problema com minha conta</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="campo">
                    <label for="mensagem">Mensagem</label>
                    <textarea id="mensagem" name="mensagem" rows="5"
                        placeholder="Descreva detalhadamente o que aconteceu..." required></textarea>
                </div>
                <button type="submit" class="btn-enviar">
                    <svg width="15" height="15" viewBox="0 0 15 15" fill="none">
                        <path d="M13 2L2 6.5l4 2 2 4L13 2z" stroke="currentColor" stroke-width="1.6"
                            stroke-linejoin="round" />
                    </svg>
                    Enviar mensagem
                </button>
            </form>
        </div>

    </div>

    <script src="js/jscommon.js"></script>
    <script src="js/jssuporte.js"></script>
</body>

</html>