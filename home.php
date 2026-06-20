<?php
require 'sessao.php';
require 'conexao.php';
require_once 'helpers_glicose_status.php'; // ← ADICIONADO: funções de classificação por tipo

$uid = $_SESSION['usuario_id'];
$nome = explode(" ", $_SESSION['usuario_nome'])[0];

// ── Busca o tipo de diabetes do usuário ──────────────────────────────────────
// ADICIONADO: antes o código ignorava o tipo do usuário; agora busca para usar
// nos limites corretos (Tipo 1, Tipo 2, Gestacional, Pré-diabetes)
$stmtTipo = $conn->prepare("SELECT tipo_diabetes FROM usuarios WHERE id = ?");
$stmtTipo->bind_param('i', $uid);
$stmtTipo->execute();
$tipoDiabetes = $stmtTipo->get_result()->fetch_assoc()['tipo_diabetes'] ?? 'tipo 1';

// ── Medições de glicose do dia ───────────────────────────────────────────────
$stmt = $conn->prepare("
    SELECT valor, momento, registrado_em FROM glicose
    WHERE usuario_id = ? AND DATE(registrado_em) = CURDATE()
    ORDER BY registrado_em ASC
");
$stmt->bind_param("i", $uid);
$stmt->execute();
$glicoses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Alimentação do dia ───────────────────────────────────────────────────────
$stmt2 = $conn->prepare("
    SELECT tipo, alimento, horario FROM alimentacao
    WHERE usuario_id = ? AND DATE(criado_em) = CURDATE()
    ORDER BY horario ASC
");
$stmt2->bind_param("i", $uid);
$stmt2->execute();
$refeicoes = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

// ── Medicamentos ─────────────────────────────────────────────────────────────
$stmt3 = $conn->prepare("SELECT nome_remedio, dosagem, horario, frequencia FROM medicamentos WHERE usuario_id = ?");
$stmt3->bind_param("i", $uid);
$stmt3->execute();
$medicamentos = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);

$labels = array_map(fn($r) => date('H:i', strtotime($r['registrado_em'])), $glicoses);
$valores = array_column($glicoses, 'valor');

$nomes_refeicao = [
    'cafe' => 'Café da manhã',
    'almoco' => 'Almoço',
    'tarde' => 'Café da tarde',
    'jantar' => 'Jantar',
];

$tipoBadge = [
    'cafe' => ['badge-amber', '☕'],
    'almoco' => ['badge-green', '🍽️'],
    'tarde' => ['badge-teal', '🍵'],
    'jantar' => ['badge-blue', '🌙'],
];

// ── Classificação da glicose ─────────────────────────────────────────────────
// CORRIGIDO: antes usava if/elseif com 70 e 180 fixo para todo mundo.
// Agora usa statusGlicose() do helper, que aplica os limites corretos
// de acordo com o tipo de diabetes E o momento da medição (jejum, pós-refeição…)
$statusInfo = null;
$limites = ['baixo' => 70, 'alto' => 180]; // fallback para o gráfico

if (!empty($glicoses)) {
    $ultimaGlicose = end($glicoses);
    $ultimo = (int) $ultimaGlicose['valor'];
    $momento = $ultimaGlicose['momento'] ?? '';

    // Retorna: [$texto, $badgeClass, $cor, $bgRgba]
    [$statusTexto, , $cor, $bgRgba] = statusGlicose($ultimo, $tipoDiabetes, $momento);

    // Mensagens personalizadas por resultado
    $msgs = [
        'Hipoglicemia' => 'Seu nível está abaixo do ideal para o seu perfil. Considere comer algo leve.',
        'Hiperglicemia' => 'Nível acima do esperado para o seu perfil. Evite doces e hidrate-se bem.',
        'Normal' => 'Seu nível está dentro do esperado para o seu perfil. Continue assim!',
    ];

    // Mesmo formato de array que o HTML já espera: [texto, cor, bgRgba, mensagem]
    $statusInfo = [$statusTexto, $cor, $bgRgba, $msgs[$statusTexto]];

    // Limites dinâmicos para o gráfico (linhas tracejadas e cores dos pontos)
    $limites = getLimitesPorMomento($tipoDiabetes, $momento);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Home</title>
    <link rel="stylesheet" href="css/stylelogado.css">
    <link rel="stylesheet" href="css/stylehome.css">
    <!-- PWA -->
    <link rel="manifest" href="/glicolife/manifest.json">
    <meta name="theme-color" content="#1a9e7a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="GlicoLife">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
    <style>
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>

<body<?= !empty($_SESSION['voz_msg']) ? ' data-voz="' . htmlspecialchars($_SESSION['voz_msg']) . '"' : '' ?>>
    <?php unset($_SESSION['voz_msg']); ?>

    <nav class="navbar">
        <div class="navbar-logo">
            <span class="glico">Glico</span><span class="life">Life</span>
        </div>
        <button class="hamburger" id="hamburger" onclick="toggleHamburger()">
            <span></span><span></span><span></span>
        </button>
        <ul class="menu" id="menu-nav">
            <li><a href="home.php" class="active">Home</a></li>
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
            <span onclick="toggleMenu()"><?= $nome ?> ▾</span>
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

    <div class="home-hero">
        <div class="hero-left">
            <p class="hero-label" id="turno-label">Bom dia</p>
            <h1 class="hero-name"><?= htmlspecialchars($nome) ?> <span class="hero-wave">👋</span></h1>
            <p class="hero-sub">Aqui está seu resumo de saúde de hoje</p>
        </div>
        <div class="hero-date" id="hero-date"></div>
    </div>

    <div class="dash-grid">

        <div class="dash-card dash-full">
            <div class="card-header">
                <div class="card-title-wrap"><span class="card-icon">📊</span>
                    <h2>Glicose de hoje</h2>
                </div>
                <a href="glicose.php" class="card-link">Ver tudo →</a>
            </div>
            <?php if (!empty($glicoses)): ?>
                <div class="glicose-status-bar"
                    style="background:<?= $statusInfo[2] ?>; border-color:<?= $statusInfo[1] ?>">
                    <div class="status-value" style="color:<?= $statusInfo[1] ?>"><?= $ultimo ?><span
                            class="status-unit">mg/dL</span></div>
                    <div class="status-info">
                        <span class="status-badge"
                            style="background:<?= $statusInfo[1] ?>22; color:<?= $statusInfo[1] ?>"><?= $statusInfo[0] ?></span>
                        <p><?= $statusInfo[3] ?></p>
                    </div>
                </div>
                <?php if ($statusInfo):
                    if ($statusInfo[0] === 'Normal') {
                        $gotinhaImg = 'img/gota-normal.png';
                        $gotinhaMsgTexto = 'Uhuul! Sua glicose está ótima hoje! Continue assim, tô super feliz! 💚';
                        $gotinhaBadge = '🎉 Tudo certo!';
                    } elseif ($statusInfo[0] === 'Hiperglicemia') {
                        $gotinhaImg = 'img/gota-alta.png';
                        $gotinhaMsgTexto = 'Hmm, tô preocupada com você... Evite doces, beba água e descanse um pouco, tá?';
                        $gotinhaBadge = '😟 Atenção!';
                    } else {
                        $gotinhaImg = 'img/gota-baixa.png';
                        $gotinhaMsgTexto = 'Ai, tô tontinha aqui... Cuide-se mais! Come alguma coisa rapidinho, por favor! 🥺';
                        $gotinhaBadge = '😵 Tontinha!';
                    }
                    ?>
                    <div class="gotinha-glicose-wrap">
                        <img src="<?= $gotinhaImg ?>" alt="Gotinha" class="gotinha-glicose-img">
                        <div class="gotinha-glicose-msg">
                            <span class="gotinha-glicose-badge"
                                style="color:<?= $statusInfo[1] ?>;background:<?= $statusInfo[1] ?>18;border-color:<?= $statusInfo[1] ?>">
                                <?= $gotinhaBadge ?>
                            </span>
                            <p><?= $gotinhaMsgTexto ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="chart-wrap"><canvas id="glucChart"></canvas></div>
                <!-- CORRIGIDO: legenda mostra os limites reais do perfil do usuário -->
                <div class="chart-legend">
                    <span><span class="legend-dot" style="background:#10b981"></span>Normal
                        (<?= $limites['baixo'] ?>–<?= $limites['alto'] ?>)</span>
                    <span><span class="legend-dot" style="background:#ef4444"></span>Alto
                        (&gt;<?= $limites['alto'] ?>)</span>
                    <span><span class="legend-dot" style="background:#f59e0b"></span>Baixo
                        (&lt;<?= $limites['baixo'] ?>)</span>
                </div>
            <?php else: ?>
                <div class="empty-card">
                    <span class="empty-icon">📉</span>
                    <p>Nenhuma medição registrada hoje.</p>
                    <a href="glicose.php" class="btn-empty">Registrar agora</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="dash-card">
            <div class="card-header">
                <div class="card-title-wrap"><span class="card-icon">🍎</span>
                    <h2>Alimentação de hoje</h2>
                </div>
                <a href="alimentacao.php" class="card-link">Ver tudo →</a>
            </div>
            <?php if (!empty($refeicoes)): ?>
                <div class="refeicao-list">
                    <?php foreach ($refeicoes as $r):
                        [$badgeClass, $emoji] = $tipoBadge[$r['tipo']] ?? ['badge-green', '🍴'];
                        $label = $nomes_refeicao[$r['tipo']] ?? $r['tipo'];
                        ?>
                        <div class="refeicao-item">
                            <span class="badge <?= $badgeClass ?>"><?= $emoji ?>         <?= $label ?></span>
                            <span class="refeicao-alimento"><?= htmlspecialchars($r['alimento']) ?></span>
                            <span class="time-chip">
                                <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                    <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.4" />
                                    <path d="M6 3v3l2 1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                                </svg>
                                <?= substr($r['horario'], 0, 5) ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-card">
                    <span class="empty-icon">🥗</span>
                    <p>Nenhuma refeição registrada hoje.</p>
                    <a href="alimentacao.php" class="btn-empty">Registrar</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="dash-card">
            <div class="card-header">
                <div class="card-title-wrap"><span class="card-icon">💊</span>
                    <h2>Medicamentos de hoje</h2>
                </div>
                <a href="medicamento.php" class="card-link">Ver tudo →</a>
            </div>
            <?php if (!empty($medicamentos)): ?>
                <div class="med-list">
                    <?php foreach ($medicamentos as $i => $med): ?>
                        <div class="med-item">
                            <div class="med-info-wrap">
                                <span class="med-nome"><?= htmlspecialchars($med['nome_remedio']) ?></span>
                                <span class="med-detalhe"><?= htmlspecialchars($med['dosagem']) ?> ·
                                    <?= substr($med['horario'], 0, 5) ?></span>
                            </div>
                            <button class="btn-tomar" id="med-<?= $i ?>" onclick="marcarTomado(<?= $i ?>)">Tomar</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-card">
                    <span class="empty-icon">💊</span>
                    <p>Nenhum medicamento cadastrado.</p>
                    <a href="medicamento.php" class="btn-empty">Cadastrar</a>
                </div>
            <?php endif; ?>
        </div>

        <div class="dash-card dash-full">
            <div class="card-header">
                <div class="card-title-wrap"><span class="card-icon">💧</span>
                    <h2>Hidratação diária</h2>
                </div>
                <span class="agua-meta-label" id="agua-pct">0%</span>
            </div>
            <div class="agua-wrap">
                <div class="agua-progress-wrap">
                    <div class="agua-progress-bg">
                        <div class="agua-progress-fill" id="agua-fill"></div>
                    </div>
                    <span class="agua-progress-text" id="agua-texto">0 de 8 copos</span>
                </div>
                <div class="agua-copos" id="copos"></div>
                <div class="agua-actions">
                    <button class="btn-agua" onclick="beberAgua()">
                        <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                            <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                        Bebi um copo
                    </button>
                </div>

                <!-- reaction gota -->
                <div class="agua-gotinha" id="agua-gotinha" style="display:none">
                    <img id="gotinha-img" src="" alt="Gotinha">
                    <div class="gotinha-msg-wrap">
                        <span class="gotinha-msg-badge" id="gotinha-badge"></span>
                        <p class="gotinha-msg" id="gotinha-msg"></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <script src="js/jscommon.js"></script>
    <script>
        // saudações
        const hora = new Date().getHours();
        document.getElementById('turno-label').textContent =
            hora < 12 ? 'Bom dia,' : hora < 18 ? 'Boa tarde,' : 'Boa noite,';
        document.getElementById('hero-date').textContent =
            new Date().toLocaleDateString('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' });

        // GRÁFICO
        <?php if (!empty($glicoses)): ?>
            const labels = <?= json_encode($labels) ?>;
            const valores = <?= json_encode($valores) ?>;

            // CORRIGIDO: limites vindos do PHP (específicos por tipo + momento)
            // antes era fixo: v > 180 / v < 70 para todo mundo
            const limBaixo = <?= $limites['baixo'] ?>;
            const limAlto = <?= $limites['alto'] ?>;

            const ptColors = valores.map(v => v > limAlto ? '#ef4444' : v < limBaixo ? '#f59e0b' : '#10b981');

            new Chart(document.getElementById('glucChart'), {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Glicose',
                            data: valores,
                            borderColor: '#1a9e7a',
                            backgroundColor: ctx => {
                                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 200);
                                g.addColorStop(0, 'rgba(26,158,122,0.18)');
                                g.addColorStop(1, 'rgba(26,158,122,0)');
                                return g;
                            },
                            pointBackgroundColor: ptColors,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            tension: 0.4,
                            fill: true,
                            borderWidth: 2.5
                        },
                        {
                            // CORRIGIDO: linha tracejada usa limAlto em vez de 180 fixo
                            label: `Limite alto (${limAlto})`,
                            data: labels.map(() => limAlto),
                            borderColor: 'rgba(239,68,68,0.5)',
                            borderDash: [5, 4],
                            borderWidth: 1.5,
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            // CORRIGIDO: linha tracejada usa limBaixo em vez de 70 fixo
                            label: `Limite baixo (${limBaixo})`,
                            data: labels.map(() => limBaixo),
                            borderColor: 'rgba(245,158,11,0.5)',
                            borderDash: [5, 4],
                            borderWidth: 1.5,
                            pointRadius: 0,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#0d2218',
                            titleColor: '#9dd8bc',
                            bodyColor: '#fff',
                            padding: 12,
                            cornerRadius: 10,
                            callbacks: {
                                label: ctx => {
                                    const v = ctx.parsed.y;
                                    // CORRIGIDO: tooltip também usa os limites do perfil
                                    const s = v > limAlto ? '— Alto' : v < limBaixo ? '— Baixo' : '— Normal';
                                    return ` ${v} mg/dL ${s}`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#92b3a5', font: { size: 11 } },
                            grid: { color: '#f0f8f4' }
                        },
                        y: {
                            min: 40, max: 320,
                            ticks: {
                                color: '#92b3a5',
                                font: { size: 11 },
                                callback: v => v + ' mg/dL'
                            },
                            grid: { color: '#f0f8f4' }
                        }
                    }
                }
            });
        <?php endif; ?>

            // MEDICAMENTOS
            // Limpa status de medicamentos de dias anteriores
            (function () {
                const hoje = new Date().toDateString();
                const tomados = JSON.parse(localStorage.getItem('med_tomados') || '{}');
                Object.keys(tomados).forEach(dia => {
                    if (dia !== hoje) delete tomados[dia];
                });
                localStorage.setItem('med_tomados', JSON.stringify(tomados));
            })();

        function marcarTomado(i) {
            const btn = document.getElementById('med-' + i);
            const tomados = JSON.parse(localStorage.getItem('med_tomados') || '{}');
            const hoje = new Date().toDateString();
            if (!tomados[hoje]) tomados[hoje] = {};
            if (tomados[hoje][i]) {
                delete tomados[hoje][i];
                btn.textContent = 'Tomar';
                btn.classList.remove('tomado');
            } else {
                tomados[hoje][i] = true;
                btn.textContent = '✓ Tomado';
                btn.classList.add('tomado');
            }
            localStorage.setItem('med_tomados', JSON.stringify(tomados));
        }
        (function () {
            const tomados = JSON.parse(localStorage.getItem('med_tomados') || '{}');
            const hoje = new Date().toDateString();
            if (tomados[hoje]) {
                Object.keys(tomados[hoje]).forEach(i => {
                    const btn = document.getElementById('med-' + i);
                    if (btn) { btn.textContent = '✓ Tomado'; btn.classList.add('tomado'); }
                });
            }
        })();

        // ÁGUA + GOTINHA
        // Limpa dados de dias anteriores automaticamente
        (function () {
            const hoje = new Date().toDateString();
            const d = JSON.parse(localStorage.getItem('agua') || '{}');
            Object.keys(d).forEach(dia => {
                if (dia !== hoje) delete d[dia];
            });
            localStorage.setItem('agua', JSON.stringify(d));
        })();
        const META = 8;

        const gotinhaEstados = [
            {
                min: 1, max: 3,
                img: 'img/gota-baixa.png',
                badge: '😟 Pouco',
                msg: 'Só um pouquinho... A gotinha tá pedindo mais água, tá bom?',
                cor: '#f59e0b'
            },
            {
                min: 4, max: 6,
                img: 'img/gota-mascote.png',
                badge: '🙂 Indo bem',
                msg: 'Tá indo bem! Continue assim, falta pouco pra meta do dia!',
                cor: '#3a7bd5'
            },
            {
                min: 7, max: 7,
                img: 'img/gota-normal.png',
                badge: '😄 Quase lá!',
                msg: 'Quase na meta! Só mais um copinho e a gotinha vai comemorar!',
                cor: '#1a9e7a'
            },
            {
                min: 8, max: 8,
                img: 'img/gota-normal.png',
                badge: '🎉 Meta atingida!',
                msg: 'Parabéns! Você completou os 8 copos de hoje! A gotinha está super feliz e orgulhosa! 💚',
                cor: '#1a9e7a'
            },
        ];

        function getEstado(qtd) {
            return gotinhaEstados.find(e => qtd >= e.min && qtd <= e.max) || null;
        }

        function renderAgua(qtd) {
            const wrap = document.getElementById('copos');
            wrap.innerHTML = '';
            for (let i = 0; i < META; i++) {
                const c = document.createElement('button');
                c.className = 'copo' + (i < qtd ? ' cheio' : '');
                c.title = i < qtd ? 'Clique para remover' : 'Clique para adicionar';
                c.textContent = '🥤';
                c.onclick = () => salvarAgua(i < qtd ? i : i + 1);
                wrap.appendChild(c);
            }

            const pct = Math.round((qtd / META) * 100);
            document.getElementById('agua-fill').style.width = pct + '%';
            document.getElementById('agua-texto').textContent = qtd + ' de ' + META + ' copos';
            document.getElementById('agua-pct').textContent = pct + '%';

            const estado = getEstado(qtd);
            const gotinhaWrap = document.getElementById('agua-gotinha');
            if (estado) {
                document.getElementById('gotinha-img').src = estado.img;
                document.getElementById('gotinha-badge').textContent = estado.badge;
                document.getElementById('gotinha-badge').style.color = estado.cor;
                document.getElementById('gotinha-badge').style.borderColor = estado.cor;
                document.getElementById('gotinha-badge').style.background = estado.cor + '18';
                document.getElementById('gotinha-msg').textContent = estado.msg;
                gotinhaWrap.style.display = 'flex';
            } else {
                gotinhaWrap.style.display = 'none';
            }
        }

        function getAgua() {
            const hoje = new Date().toDateString();
            return (JSON.parse(localStorage.getItem('agua') || '{}'))[hoje] || 0;
        }
        function salvarAgua(qtd) {
            const hoje = new Date().toDateString();
            const d = JSON.parse(localStorage.getItem('agua') || '{}');
            d[hoje] = Math.max(0, Math.min(qtd, META));
            localStorage.setItem('agua', JSON.stringify(d));
            renderAgua(d[hoje]);
        }
        function beberAgua() { salvarAgua(getAgua() + 1); }
        function resetarAgua() { salvarAgua(0); }

        renderAgua(getAgua());

        // alerta remedio
        if ('Notification' in window) Notification.requestPermission();

        const remedios = <?= json_encode(array_map(function ($m) {
            return [
                'nome' => $m['nome_remedio'],
                'horario' => $m['horario'],
            ];
        }, $medicamentos)) ?>;

        function agendarNotificacoes() {
            if (Notification.permission !== 'granted') return;
            remedios.forEach(rem => {
                const agora = new Date();
                const [h, m] = rem.horario.split(':');
                const horario = new Date();
                horario.setHours(parseInt(h), parseInt(m), 0, 0);
                const diff = horario - agora;
                if (diff > 0) {
                    setTimeout(() => {
                        new Notification('💊 Hora do remédio!', {
                            body: 'Tomar ' + rem.nome + ' agora. E não esquece de beber água! 💧',
                            icon: 'img/gota-mascote.png'
                        });
                    }, diff);
                }
            });
        }

        agendarNotificacoes();

        function toggleHamburger() {
            const ham = document.getElementById('hamburger');
            const nav = document.getElementById('menu-nav');
            const extra = document.getElementById('menu-mobile-extra');

            ham.classList.toggle('aberto');
            nav.classList.toggle('aberto');
            extra.classList.toggle('aberto');

            if (extra.classList.contains('aberto')) {
                const navBottom = nav.getBoundingClientRect().bottom;
                extra.style.top = navBottom + 'px';
            }
        }
    </script>

    </body>

</html>