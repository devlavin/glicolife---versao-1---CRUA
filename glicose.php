<?php
require 'sessao.php';
require 'conexao.php';
require 'helpers_glicose_status.php';
date_default_timezone_set('America/Sao_Paulo');
$conn->query("SET time_zone = '-03:00'");
$uid = $_SESSION['usuario_id'];

$uStmt = $conn->prepare("SELECT tipo_diabetes FROM usuarios WHERE id = ?");
$uStmt->bind_param('i', $uid);
$uStmt->execute();
$tipoDiab = $uStmt->get_result()->fetch_assoc()['tipo_diabetes'] ?? 'tipo1';
$limites = getLimites($tipoDiab);

$ult = $conn->query("SELECT valor, momento, registrado_em FROM glicose WHERE usuario_id=$uid AND DATE(registrado_em)=CURDATE() ORDER BY registrado_em DESC LIMIT 1");
$ultima = $ult->fetch_assoc();

$res = $conn->query("SELECT valor, momento, registrado_em FROM glicose WHERE usuario_id=$uid AND DATE(registrado_em)=CURDATE() ORDER BY registrado_em DESC LIMIT 20");
$historico = $res->fetch_all(MYSQLI_ASSOC);

$statsQ = $conn->query("SELECT COUNT(*) AS total, ROUND(AVG(valor),0) AS media, MAX(valor) AS maximo, MIN(valor) AS minimo FROM glicose WHERE usuario_id=$uid AND DATE(registrado_em)=CURDATE()");
$stats = $statsQ->fetch_assoc();

$infoDiabetes = [
    'tipo1' => ['titulo' => 'Diabetes Tipo 1', 'cor' => '#3b82f6', 'bg' => '#eff6ff', 'borda' => '#bfdbfe', 'icone' => '💉', 'alvo' => 'Jejum: 80–130 mg/dL · Pós-refeição: &lt;180 mg/dL', 'dicas' => ['Monitore antes e após as refeições', 'Ajuste a insulina conforme orientação médica', 'Tenha sempre açúcar rápido por perto', 'Registre padrões para seu médico']],
    'tipo2' => ['titulo' => 'Diabetes Tipo 2', 'cor' => '#1a9e7a', 'bg' => '#f0faf5', 'borda' => '#d6f5ea', 'icone' => '🩺', 'alvo' => 'Jejum: 80–130 mg/dL · Pós-refeição: &lt;180 mg/dL', 'dicas' => ['Prefira alimentos de baixo índice glicêmico', 'Pratique atividade física regularmente', 'Tome os medicamentos nos horários certos', 'Monitore mais em dias de estresse']],
    'gestacional' => ['titulo' => 'Diabetes Gestacional', 'cor' => '#ec4899', 'bg' => '#fdf2f8', 'borda' => '#fbcfe8', 'icone' => '🤰', 'alvo' => 'Jejum: &lt;95 mg/dL · 1h pós: &lt;140 mg/dL · 2h: &lt;120 mg/dL', 'dicas' => ['Meça em jejum e após cada refeição', 'Evite carboidratos simples', 'Mantenha acompanhamento médico frequente', 'Alimentação fracionada ajuda no controle']],
    'prediabetes' => ['titulo' => 'Pré-Diabetes', 'cor' => '#f59e0b', 'bg' => '#fffbeb', 'borda' => '#fde68a', 'icone' => '⚠️', 'alvo' => 'Jejum: 100–125 mg/dL · Atenção redobrada', 'dicas' => ['Mudança de hábitos pode reverter o quadro', 'Reduza açúcar e ultraprocessados', 'Atividade física é essencial', 'Consulte seu médico regularmente']],
];

$info = $infoDiabetes[strtolower(trim($tipoDiab))] ?? null;
$heroInfo = $ultima ? statusGlicose((int) $ultima['valor'], $tipoDiab, $ultima['momento']) : null;
$gaugeMin = $limites['baixo'] - 30;
$gaugeMax = $limites['alto'] + 120;
$gaugeRange = $gaugeMax - $gaugeMin;

$vozMsg = $_SESSION['voz_msg'] ?? '';
unset($_SESSION['voz_msg']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife — Glicose</title>
    <link rel="stylesheet" href="css/stylelogado.css">
    <link rel="stylesheet" href="css/styleglicose.css">
    <link rel="manifest" href="/glicolife/manifest.json">
    <meta name="theme-color" content="#1a9e7a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
</head>
<body<?= $vozMsg ? ' data-voz="' . htmlspecialchars($vozMsg) . '"' : '' ?>>

    <?php if (isset($_GET['ok'])): ?>
        <div class="toast toast-sucesso" id="toast">✅ Medição salva com sucesso!</div>
    <?php elseif (isset($_GET['erro'])): ?>
        <div class="toast toast-erro" id="toast">❌ Erro ao salvar. Verifique os dados.</div>
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
            <li><a href="glicose.php" class="active">Glicose</a></li>
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
            <span onclick="toggleMenu()"><?= explode(' ', $_SESSION['usuario_nome'])[0] ?> ▾</span>
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

                <div class="acesso-row">
                    <span class="acesso-label">Leitura em voz</span>
                    <div class="toggle-wrap">
                        <label class="toggle" for="toggleVozCheck" title="Ligar ou desligar a leitura em voz alta">
                            <input type="checkbox" id="toggleVozCheck" onchange="onToggleVoz(this.checked)">
                            <span class="toggle-slider"></span>
                            <span class="sr-only"></span>
                        </label>
                        <span class="toggle-label" id="vozLabel">Desligada</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-hero">
        <div class="hero-icon">📊</div>
        <h2>Glicose</h2>
        <p>Monitore seus níveis e mantenha o controle do diabetes</p>
    </div>

    <div class="glicose-layout">

        <!-- COLUNA ESQUERDA -->
        <div class="col-left">

            <div class="reading-card <?= $heroInfo ? $heroInfo[1] : 'status-empty' ?>">
                <div class="reading-label">Última medição</div>
                <?php if ($ultima): ?>
                    <div class="reading-value">
                        <?= (int) $ultima['valor'] ?><span class="reading-unit"> mg/dL</span>
                    </div>
                    <div class="reading-meta">
                        <span class="reading-badge"
                            style="background:<?= $heroInfo[2] ?>22;color:<?= $heroInfo[2] ?>"><?= $heroInfo[0] ?></span>
                        <span class="reading-time">
                            <svg width="11" height="11" viewBox="0 0 12 12" fill="none">
                                <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.4" />
                                <path d="M6 3v3l2 1.5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                            </svg>
                            <?= date('d/m/Y H:i', strtotime($ultima['registrado_em'])) ?>
                        </span>
                    </div>
                    <div class="reading-momento"><?= htmlspecialchars($ultima['momento']) ?></div>
                <?php else: ?>
                    <div class="reading-value reading-empty">—</div>
                    <div class="reading-meta">
                        <span class="reading-badge" style="background:#f0faf5;color:#92b3a5">Sem dados hoje</span>
                    </div>
                <?php endif; ?>

                <div class="gauge-wrap">
                    <div class="gauge-bar">
                        <div class="gauge-zone zone-low"></div>
                        <div class="gauge-zone zone-ok"></div>
                        <div class="gauge-zone zone-high"></div>
                        <?php if ($ultima): ?>
                            <div class="gauge-needle"
                                style="left:<?= min(max((((int) $ultima['valor'] - $gaugeMin) / $gaugeRange) * 100, 1), 99) ?>%">
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="gauge-labels">
                        <span><?= $gaugeMin ?></span>
                        <span><?= $limites['baixo'] ?></span>
                        <span><?= $limites['alto'] ?></span>
                        <span><?= $gaugeMax ?>+</span>
                    </div>
                </div>
            </div>

            <?php if ($stats && $stats['total'] > 0): ?>
                <div class="stats-grid">
                    <div class="stat-card"><span class="stat-num"><?= $stats['total'] ?></span><span
                            class="stat-label">Medições</span></div>
                    <div class="stat-card"><span class="stat-num"><?= $stats['media'] ?></span><span
                            class="stat-label">Média mg/dL</span></div>
                    <div class="stat-card"><span class="stat-num" style="color:#ef4444"><?= $stats['maximo'] ?></span><span
                            class="stat-label">Máximo</span></div>
                    <div class="stat-card"><span class="stat-num" style="color:#f59e0b"><?= $stats['minimo'] ?></span><span
                            class="stat-label">Mínimo</span></div>
                </div>
            <?php endif; ?>

            <div class="legenda-card">
                <?php if ($info): ?>
                    <div class="tipo-diab-card" style="border-color:<?= $info['borda'] ?>;background:<?= $info['bg'] ?>">
                        <div class="tipo-diab-header" style="color:<?= $info['cor'] ?>">
                            <span class="tipo-diab-icone"><?= $info['icone'] ?></span>
                            <div>
                                <div class="tipo-diab-titulo"><?= $info['titulo'] ?></div>
                                <div class="tipo-diab-alvo">🎯 <?= $info['alvo'] ?></div>
                            </div>
                        </div>
                        <div class="tipo-diab-dicas">
                            <div class="tipo-diab-dicas-titulo" style="color:<?= $info['cor'] ?>">Recomendações</div>
                            <?php foreach ($info['dicas'] as $d): ?>
                                <div class="tipo-diab-dica-item"><span style="color:<?= $info['cor'] ?>">✓</span><?= $d ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="legenda-title" style="margin-top:18px">Referência de valores</div>
                <div class="legenda-item"><span class="legenda-dot" style="background:#f59e0b"></span>
                    <div><strong>Hipoglicemia</strong> — abaixo de <?= $limites['baixo'] ?> mg/dL<p>Nível baixo, requer
                            atenção imediata</p>
                    </div>
                </div>
                <div class="legenda-item"><span class="legenda-dot" style="background:#10b981"></span>
                    <div><strong>Normal</strong> — entre <?= $limites['baixo'] ?> e <?= $limites['alto'] ?> mg/dL<p>
                            Glicose dentro do intervalo ideal</p>
                    </div>
                </div>
                <div class="legenda-item"><span class="legenda-dot" style="background:#ef4444"></span>
                    <div><strong>Hiperglicemia</strong> — acima de <?= $limites['alto'] ?> mg/dL<p>Nível alto, consulte
                            seu médico</p>
                    </div>
                </div>
            </div>

        </div><!-- /col-left -->

        <!-- COLUNA DIREITA -->
        <div class="col-right">

            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-icon">➕</div>
                    <h3>Registrar medição</h3>
                </div>
                <form method="post" action="registrar_glicose.php">
                    <div class="campo">
                        <label for="valor">Valor (mg/dL)</label>
                        <div class="input-mic-wrap">
                            <input type="number" name="valor" id="valor" min="20" max="600" placeholder="Ex: 110"
                                required>
                            <button type="button" class="btn-mic hidden" id="mic-glicose" onclick="micGlicose()"
                                title="Falar o valor" aria-label="Usar microfone">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="2" width="6" height="12" rx="3" />
                                    <path d="M5 10a7 7 0 0014 0M12 19v3M8 22h8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="campo">
                        <label for="momento">Momento</label>
                        <select name="momento" id="momento" required>
                            <option value="" disabled selected>Selecione...</option>
                            <option value="Jejum">🌅 Jejum</option>
                            <option value="Pré-refeição">🍽️ Pré-refeição</option>
                            <option value="Pós-refeição">✅ Pós-refeição</option>
                            <option value="Ao deitar">🌙 Ao deitar</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-salvar">
                        <svg width="14" height="14" viewBox="0 0 15 15" fill="none">
                            <path d="M2 7.5L6 11.5L13 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Salvar medição
                    </button>
                </form>
            </div>

            <div class="historico-card">
                <div class="historico-header">
                    <h3>📈 Histórico de hoje</h3>
                    <span class="historico-count"><?= count($historico) ?> registros</span>
                </div>
                <?php if (empty($historico)): ?>
                    <div class="empty-hist"><span>📉</span>
                        <p>Nenhuma medição registrada hoje</p>
                    </div>
                <?php else: ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th scope="col">Valor</th>
                                    <th scope="col">Momento</th>
                                    <th scope="col">Horário</th>
                                    <th scope="col">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($historico as $row):
                                    [$statusTxt, $badgeClass] = statusGlicose((int) $row['valor'], $tipoDiab, $row['momento']); ?>
                                    <tr>
                                        <td class="val-cell"><?= $row['valor'] ?> <span class="unit">mg/dL</span></td>
                                        <td><?= htmlspecialchars($row['momento']) ?></td>
                                        <td class="date-cell"><?= date('d/m H:i', strtotime($row['registrado_em'])) ?></td>
                                        <td><span class="badge <?= $badgeClass ?>"><?= $statusTxt ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="js/jsglicose.js"></script>
    <script src="js/jscommon.js"></script>
    </body>

</html>