<?php
require 'sessao.php';
require 'conexao.php';
require 'helpers_glicose_status.php';
date_default_timezone_set('America/Sao_Paulo');

$conn->query("SET time_zone = '-03:00'");

$uid = $_SESSION['usuario_id'];

$uStmt = $conn->prepare("SELECT tipo_diabetes, email FROM usuarios WHERE id = ?");
$uStmt->bind_param('i', $uid);
$uStmt->execute();
$usuario = $uStmt->get_result()->fetch_assoc();
$tipoDiab = $usuario['tipo_diabetes'] ?? 'tipo 1';
$limites = getLimites($tipoDiab);

$periodo = intval($_GET['dias'] ?? 7);
if (!in_array($periodo, [7, 14, 30, 90]))
    $periodo = 7;

$stmt = $conn->prepare(
    "SELECT valor, momento, registrado_em FROM glicose
     WHERE usuario_id = ? AND registrado_em >= DATE_SUB(NOW(), INTERVAL ? DAY)
     ORDER BY registrado_em ASC"
);
$stmt->bind_param('ii', $uid, $periodo);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total = count($rows);
$media = $total ? round(array_sum(array_column($rows, 'valor')) / $total) : 0;
$maxVal = $total ? max(array_column($rows, 'valor')) : 0;
$minVal = $total ? min(array_column($rows, 'valor')) : 0;

$normais = count(array_filter($rows, function ($r) use ($tipoDiab) {
    $l = getLimitesPorMomento($tipoDiab, $r['momento']);
    return $r['valor'] >= $l['baixo'] && $r['valor'] <= $l['alto'];
}));
$altos = count(array_filter($rows, function ($r) use ($tipoDiab) {
    $l = getLimitesPorMomento($tipoDiab, $r['momento']);
    return $r['valor'] > $l['alto'];
}));
$baixos = count(array_filter($rows, function ($r) use ($tipoDiab) {
    $l = getLimitesPorMomento($tipoDiab, $r['momento']);
    return $r['valor'] < $l['baixo'];
}));

$pctNorm = $total ? round($normais / $total * 100) : 0;

$labels = array_map(fn($r) => date('d/m H:i', strtotime($r['registrado_em'])), $rows);
$valores = array_column($rows, 'valor');

$periodoLabel = [7 => '7 dias', 14 => '14 dias', 30 => '1 mês', 90 => '3 meses'][$periodo];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Relatórios</title>
    <link rel="stylesheet" href="css/stylelogado.css">
    <link rel="stylesheet" href="css/stylerelatorio.css">
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

<body>
    <?php if (isset($_GET['enviado'])): ?>
        <div class="toast toast-sucesso" id="toast">✅ Relatório enviado com sucesso!</div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="toast toast-erro" id="toast">❌ Erro ao enviar. Tente novamente.</div>
    <?php endif; ?>

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
            <a href="relatorio.php">Histórico &amp; Relatórios</a>
            <a href="suporte.php">Ajuda &amp; Suporte</a>
            <a href="logout.php" class="sair">Sair</a>
        </div>
        <div class="perfil" id="perfil">
            <span onclick="toggleMenu()"><?php echo explode(" ", $_SESSION['usuario_nome'])[0]; ?> ▾</span>
            <ul class="submenu" id="submenu">
                <li><a href="dados.php">Conta</a></li>
                <li><a href="relatorio.php">Histórico &amp; Relatórios</a></li>
                <li><a href="suporte.php">Ajuda &amp; Suporte</a></li>
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

    <div class="page-hero">
        <div class="hero-icon">📈</div>
        <h2>Histórico &amp; Relatórios</h2>
        <p>Acompanhe a evolução da sua glicose ao longo do tempo</p>
    </div>

    <div class="rel-layout">

        <div class="periodo-bar">
            <?php foreach ([7 => '7 dias', 14 => '14 dias', 30 => '1 mês', 90 => '3 meses'] as $d => $lbl): ?>
                <a href="?dias=<?= $d ?>" class="btn-periodo <?= $periodo == $d ? 'ativo' : '' ?>"><?= $lbl ?></a>
            <?php endforeach; ?>
        </div>

        <div class="metrics-grid">
            <div class="metric-card">
                <span class="metric-val"><?= $media ?: '—' ?></span>
                <span class="metric-lbl">Média mg/dL</span>
            </div>
            <div class="metric-card">
                <span class="metric-val" style="color:#ef4444"><?= $maxVal ?: '—' ?></span>
                <span class="metric-lbl">Máxima</span>
            </div>
            <div class="metric-card">
                <span class="metric-val" style="color:#f59e0b"><?= $minVal ?: '—' ?></span>
                <span class="metric-lbl">Mínima</span>
            </div>
            <div class="metric-card highlight">
                <span class="metric-val" style="color:#10b981"><?= $pctNorm ?>%</span>
                <span class="metric-lbl">No alvo</span>
            </div>
            <div class="metric-card">
                <span class="metric-val"><?= $total ?></span>
                <span class="metric-lbl">Medições</span>
            </div>
            <div class="metric-card">
                <span class="metric-val" style="color:#ef4444"><?= $altos ?></span>
                <span class="metric-lbl">Altas (&gt;<?= $limites['alto'] ?>)</span>
            </div>
            <div class="metric-card">
                <span class="metric-val" style="color:#f59e0b"><?= $baixos ?></span>
                <span class="metric-lbl">Baixas (&lt;<?= $limites['baixo'] ?>)</span>
            </div>
        </div>

        <?php if ($total > 0): ?>
            <div class="alvo-card">
                <div class="alvo-header">
                    <span class="alvo-title">Tempo no alvo (<?= $limites['baixo'] ?>–<?= $limites['alto'] ?> mg/dL)</span>
                    <span class="alvo-pct"><?= $pctNorm ?>%</span>
                </div>
                <div class="alvo-bar-bg">
                    <div class="alvo-seg low" style="width:<?= $total ? round($baixos / $total * 100) : 0 ?>%"></div>
                    <div class="alvo-seg ok" style="width:<?= $pctNorm ?>%"></div>
                    <div class="alvo-seg high" style="width:<?= $total ? round($altos / $total * 100) : 0 ?>%"></div>
                </div>
                <div class="alvo-legend">
                    <span><span class="dot" style="background:#f59e0b"></span>Baixo (<?= $baixos ?>)</span>
                    <span><span class="dot" style="background:#10b981"></span>Normal (<?= $normais ?>)</span>
                    <span><span class="dot" style="background:#ef4444"></span>Alto (<?= $altos ?>)</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="export-card">
            <div class="export-header">
                <div class="export-header-left">
                    <div class="export-icon">📤</div>
                    <div>
                        <h3>Exportar relatório</h3>
                        <p>Período: <strong><?= $periodoLabel ?></strong> · <?= $total ?>
                            medição<?= $total != 1 ? 'ões' : '' ?></p>
                    </div>
                </div>
            </div>
            <div class="export-options">
                <a href="gerar_relatorio.php?dias=<?= $periodo ?>&acao=download" class="export-btn export-pdf"
                    target="_blank">
                    <span class="export-btn-icon">📄</span>
                    <span class="export-btn-text">
                        <strong>Baixar PDF</strong>
                        <small>Salvar no dispositivo</small>
                    </span>
                </a>
                <button class="export-btn export-email" onclick="abrirModal('modalEmail')">
                    <span class="export-btn-icon">📧</span>
                    <span class="export-btn-text">
                        <strong>Enviar para mim</strong>
                        <small>Receber por e-mail</small>
                    </span>
                </button>
                <button class="export-btn export-medico" onclick="abrirModal('modalMedico')">
                    <span class="export-btn-icon">🩺</span>
                    <span class="export-btn-text">
                        <strong>Enviar ao médico</strong>
                        <small>Informar e-mail do médico</small>
                    </span>
                </button>
            </div>
        </div>

        <div class="rel-card">
            <div class="card-header">
                <div class="card-title-wrap">
                    <span class="card-icon">📊</span>
                    <h3>Evolução da glicose</h3>
                </div>
                <div class="chart-legend-inline">
                    <span><span class="ldot" style="background:#10b981"></span>Normal</span>
                    <span><span class="ldot" style="background:#ef4444"></span>Alto</span>
                    <span><span class="ldot" style="background:#f59e0b"></span>Baixo</span>
                </div>
            </div>
            <?php if ($total > 0): ?>
                <div class="chart-wrap"><canvas id="glucChart"></canvas></div>
            <?php else: ?>
                <div class="empty-state"><span>📉</span>
                    <p>Nenhuma medição neste período.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="rel-card">
            <div class="card-header">
                <div class="card-title-wrap">
                    <span class="card-icon">📋</span>
                    <h3>Registros detalhados</h3>
                </div>
                <?php if ($total > 0): ?>
                    <span class="count-chip"><?= $total ?> registros</span>
                <?php endif; ?>
            </div>
            <?php if ($total > 0): ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th scope="col">Data / Hora</th>
                                <th scope="col">Momento</th>
                                <th scope="col">Glicose</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_reverse($rows) as $row):
                                [$stTxt, $stClass] = statusGlicose((int) $row['valor'], $tipoDiab, $row['momento']);
                                ?>
                                <tr>
                                    <td class="date-cell"><?= date('d/m/Y H:i', strtotime($row['registrado_em'])) ?></td>
                                    <td class="momento-cell"><?= htmlspecialchars($row['momento']) ?></td>
                                    <td class="val-cell"><?= $row['valor'] ?> <span class="unit">mg/dL</span></td>
                                    <td><span class="badge <?= $stClass ?>"><?= $stTxt ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state"><span>🔍</span>
                    <p>Nenhum registro encontrado neste período.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <!-- ENVIAR PARA MIM -->
    <div class="modal-overlay" id="modalEmail" onclick="fecharSeForaDoModal(event,'modalEmail')">
        <div class="modal-box">
            <button class="modal-close" onclick="fecharModal('modalEmail')">✕</button>
            <div class="modal-header-wrap">
                <div class="modal-icon-wrap">📧</div>
                <div>
                    <h3>Enviar relatório por e-mail</h3>
                    <p>O PDF dos últimos <strong><?= $periodoLabel ?></strong> será enviado para você.</p>
                </div>
            </div>
            <form action="enviar_relatorio.php" method="POST">
                <input type="hidden" name="dias" value="<?= $periodo ?>">
                <input type="hidden" name="tipo" value="proprio">
                <div class="campo">
                    <label for="email-proprio">Seu e-mail</label>
                    <input type="email" id="email-proprio" name="email_destino"
                        value="<?= htmlspecialchars($usuario['email'] ?? '') ?>" placeholder="seu@email.com" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-enviar-modal">
                        <svg width="14" height="14" viewBox="0 0 15 15" fill="none">
                            <path d="M13 2L2 6.5l4 2 2 4L13 2z" stroke="currentColor" stroke-width="1.6"
                                stroke-linejoin="round" />
                        </svg>
                        Enviar agora
                    </button>
                    <button type="button" class="btn-cancel-modal" onclick="fecharModal('modalEmail')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ENVIAR AO MÉDICO -->
    <div class="modal-overlay" id="modalMedico" onclick="fecharSeForaDoModal(event,'modalMedico')">
        <div class="modal-box">
            <button class="modal-close" onclick="fecharModal('modalMedico')">✕</button>
            <div class="modal-header-wrap">
                <div class="modal-icon-wrap">🩺</div>
                <div>
                    <h3>Enviar ao médico</h3>
                    <p>Relatório dos últimos <strong><?= $periodoLabel ?></strong> será enviado ao e-mail indicado.</p>
                </div>
            </div>
            <form action="enviar_relatorio.php" method="POST">
                <input type="hidden" name="dias" value="<?= $periodo ?>">
                <input type="hidden" name="tipo" value="medico">
                <div class="campo">
                    <label for="nome-medico">Nome do médico <span class="campo-opt">(opcional)</span></label>
                    <input type="text" id="nome-medico" name="nome_medico" placeholder="Dr. João Silva">
                </div>
                <div class="campo">
                    <label for="email-medico">E-mail do médico</label>
                    <input type="email" id="email-medico" name="email_destino" placeholder="medico@clinica.com.br"
                        required>
                </div>
                <div class="campo">
                    <label for="obs-medico">Observação <span class="campo-opt">(opcional)</span></label>
                    <textarea id="obs-medico" name="observacao" rows="3"
                        placeholder="Ex: Solicito análise dos valores das últimas semanas..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-enviar-modal">
                        <svg width="14" height="14" viewBox="0 0 15 15" fill="none">
                            <path d="M13 2L2 6.5l4 2 2 4L13 2z" stroke="currentColor" stroke-width="1.6"
                                stroke-linejoin="round" />
                        </svg>
                        Enviar ao médico
                    </button>
                    <button type="button" class="btn-cancel-modal"
                        onclick="fecharModal('modalMedico')">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modais da page
        function abrirModal(id) {
            document.getElementById(id).classList.add('open');
            document.body.style.overflow = 'hidden';
        }
        function fecharModal(id) {
            document.getElementById(id).classList.remove('open');
            document.body.style.overflow = '';
        }
        function fecharSeForaDoModal(e, id) {
            if (e.target === document.getElementById(id)) fecharModal(id);
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') ['modalEmail', 'modalMedico'].forEach(fecharModal);
        });

        // Gráfico
        <?php if ($total > 0): ?>
            const labels = <?= json_encode($labels) ?>;
            const valores = <?= json_encode($valores) ?>;
            const limiteAlto = <?= $limites['alto'] ?>;
            const limiteBaixo = <?= $limites['baixo'] ?>;

            const ptColors = valores.map(v =>
                v > limiteAlto ? '#ef4444' : v < limiteBaixo ? '#f59e0b' : '#10b981'
            );

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
                                const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 220);
                                g.addColorStop(0, 'rgba(26,158,122,0.16)');
                                g.addColorStop(1, 'rgba(26,158,122,0)');
                                return g;
                            },
                            pointBackgroundColor: ptColors,
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            tension: 0.35,
                            fill: true,
                            borderWidth: 2.5
                        },
                        {
                            data: labels.map(() => limiteAlto),
                            borderColor: 'rgba(239,68,68,0.4)',
                            borderDash: [5, 4],
                            borderWidth: 1.5,
                            pointRadius: 0,
                            fill: false
                        },
                        {
                            data: labels.map(() => limiteBaixo),
                            borderColor: 'rgba(245,158,11,0.4)',
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
                            callbacks: { label: ctx => ` ${ctx.parsed.y} mg/dL` }
                        }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#92b3a5', font: { size: 11 }, maxRotation: 40, maxTicksLimit: 12 },
                            grid: { color: '#f0f8f4' }
                        },
                        y: {
                            min: 40, max: 320,
                            ticks: { color: '#92b3a5', font: { size: 11 }, callback: v => v + '' },
                            grid: { color: '#f0f8f4' }
                        }
                    }
                }
            });
        <?php endif; ?>
    </script>

    <script src="js/jscommon.js"></script>

</body>

</html>