<?php
require 'sessao.php';
require 'conexao.php';

$stmt = $conn->prepare("SELECT * FROM medicamentos WHERE usuario_id = ? ORDER BY horario ASC");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$medicamentos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$viaBadge = [
    'oral' => ['badge-green', 'Oral'],
    'subcutanea' => ['badge-teal', 'Subcutânea'],
    'intramuscular' => ['badge-blue', 'Intramuscular'],
    'intravenosa' => ['badge-purple', 'Intravenosa'],
    'sublingual' => ['badge-amber', 'Sublingual'],
    'topica' => ['badge-rose', 'Tópica'],
    'inalatoria' => ['badge-teal', 'Inalatória'],
    'retal' => ['badge-blue', 'Retal'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Medicamentos</title>
    <link rel="stylesheet" href="css/stylelogado.css">
    <link rel="stylesheet" href="css/stylemedic-alimen.css">
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
        .btn-acao {
            background: none;
            border: 1px solid #e0f2eb;
            border-radius: 8px;
            padding: 4px 8px;
            cursor: pointer;
            font-size: 13px;
            transition: background 0.2s;
        }

        .btn-editar:hover {
            background: #f0faf5;
        }

        .btn-excluir:hover {
            background: #fff0f0;
            border-color: #fecaca;
        }

        .btn-excluir-confirm {
            padding: 10px 20px;
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
        }

        .btn-excluir-confirm:hover {
            background: #dc2626;
        }

        .btn-cancelar-confirm {
            padding: 10px 20px;
            border: 1px solid #d6f5ea;
            background: #fff;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            color: #5a7a6d;
        }
    </style>
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
            <li><a href="home.php">Home</a></li>
            <li><a href="glicose.php">Glicose</a></li>
            <li><a href="medicamento.php" class="active">Medicamentos</a></li>
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
                            <span class="sr-only">Leitura em voz alta</span>
                        </label>
                        <span class="toggle-label" id="vozLabel">Desligada</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-hero">
        <div class="hero-icon">💊</div>
        <h2>Medicamentos</h2>
        <p>Gerencie seus remédios e não perca nenhuma dose</p>
    </div>

    <div class="section">

        <?php if (!empty($medicamentos)): ?>
            <div class="stats-bar">
                <div class="stat-card">
                    <span class="stat-num"><?= count($medicamentos) ?></span>
                    <span class="stat-label">Medicamentos cadastrados</span>
                </div>
                <?php
                $vias = array_count_values(array_column($medicamentos, 'via_administracao'));
                $topVia = key($vias);
                $topViaLabel = $viaBadge[$topVia][1] ?? ucfirst($topVia);
                ?>
                <div class="stat-card">
                    <span class="stat-num" style="font-size:18px"><?= $topViaLabel ?></span>
                    <span class="stat-label">Via mais comum</span>
                </div>
                <?php
                $freqs = array_count_values(array_column($medicamentos, 'frequencia'));
                $totalDoses = 0;
                foreach ($freqs as $f => $n) {
                    $num = intval($f);
                    $totalDoses += $num * $n;
                }
                ?>
                <div class="stat-card">
                    <span class="stat-num"><?= $totalDoses ?></span>
                    <span class="stat-label">Doses por dia</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="toolbar">
            <span class="toolbar-title">💊 Seus medicamentos</span>
            <button class="btn-add" onclick="abrirForm()">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                Registrar medicamento
            </button>
        </div>

        <?php if (empty($medicamentos)): ?>
            <div class="empty-state">
                <span class="empty-icon">💊</span>
                <p>Nenhum medicamento cadastrado ainda.</p>
                <button class="btn-add" onclick="abrirForm()">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    Cadastrar primeiro medicamento
                </button>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Dosagem</th>
                            <th>Via</th>
                            <th>Horário</th>
                            <th>Frequência</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($medicamentos as $med):
                            $via = $med["via_administracao"];
                            [$badgeClass, $viaLabel] = $viaBadge[$via] ?? ['badge-green', htmlspecialchars($via)];
                            ?>
                            <tr>
                                <td style="font-weight:600"><?= htmlspecialchars($med["nome_remedio"]) ?></td>
                                <td style="font-family:'DM Mono',monospace;font-size:13px;color:var(--text-muted)">
                                    <?= htmlspecialchars($med["dosagem"]) ?>
                                </td>
                                <td><span class="badge <?= $badgeClass ?>"><?= $viaLabel ?></span></td>
                                <td>
                                    <span class="time-chip">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                            <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.4" />
                                            <path d="M6 3v3l2 1.5" stroke="currentColor" stroke-width="1.4"
                                                stroke-linecap="round" />
                                        </svg>
                                        <?= htmlspecialchars($med["horario"]) ?>
                                    </span>
                                </td>
                                <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($med["frequencia"]) ?>
                                    ao dia</td>
                                <td>
                                    <div style="display:flex;gap:6px;">
                                        <button class="btn-acao btn-editar"
                                            onclick="abrirEdicao(<?= htmlspecialchars(json_encode($med)) ?>)">✏️</button>
                                        <button class="btn-acao btn-excluir"
                                            onclick="confirmarExclusao(<?= $med['id'] ?>)">🗑️</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <div class="modal-overlay" id="modalOverlay" onclick="fecharFormOutside(event)">
        <div class="caixa inside-overlay" id="formMedicamento">
            <button class="close-btn" type="button" onclick="fecharForm()" title="Fechar">✕</button>
            <div class="caixa-header">
                <div class="caixa-icon">💊</div>
                <h1>Cadastrar medicamento</h1>
            </div>
            <form method="post" action="salvar_medicamento.php">
                <div class="campo">
                    <label for="nome_remedio">Nome do remédio</label>
                    <div class="input-mic-wrap">
                        <input type="text" name="nome_remedio" id="nome_remedio" required placeholder="Ex: Metformina">
                        <button type="button" class="btn-mic hidden" id="mic-nome" onclick="micNome()"
                            title="Falar nome" aria-label="Usar microfone para o nome">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="2" width="6" height="12" rx="3" />
                                <path d="M5 10a7 7 0 0014 0M12 19v3M8 22h8" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="campo">
                    <label for="dosagem">Dosagem</label>
                    <div class="input-mic-wrap">
                        <input type="text" name="dosagem" id="dosagem" required placeholder="Ex: 500mg">
                        <button type="button" class="btn-mic hidden" id="mic-dosagem" onclick="micDosagem()"
                            title="Falar dosagem" aria-label="Usar microfone para a dosagem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect x="9" y="2" width="6" height="12" rx="3" />
                                <path d="M5 10a7 7 0 0014 0M12 19v3M8 22h8" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="campo">
                    <label for="via">Via de administração</label>
                    <select name="via_administracao" id="via" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="oral">Oral</option>
                        <option value="subcutanea">Subcutânea</option>
                        <option value="intramuscular">Intramuscular</option>
                        <option value="intravenosa">Intravenosa</option>
                        <option value="sublingual">Sublingual</option>
                        <option value="topica">Tópica</option>
                        <option value="inalatoria">Inalatória</option>
                        <option value="retal">Retal</option>
                    </select>
                </div>
                <div style="display:flex;gap:14px;">
                    <div class="campo" style="flex:1">
                        <label for="horario">Horário</label>
                        <input type="time" name="horario" id="horario" required>
                    </div>
                    <div class="campo" style="flex:1">
                        <label for="frequencia">Frequência</label>
                        <select name="frequencia" id="frequencia" required>
                            <option value="" disabled selected>Selecione...</option>
                            <option value="1x">1× ao dia</option>
                            <option value="2x">2× ao dia</option>
                            <option value="3x">3× ao dia</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit">Salvar medicamento</button>
                    <button type="button" onclick="fecharForm()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalEditar" onclick="if(event.target===this)fecharEdicao()">
        <div class="caixa inside-overlay">
            <button class="close-btn" type="button" onclick="fecharEdicao()">✕</button>
            <div class="caixa-header">
                <div class="caixa-icon">✏️</div>
                <h1>Editar medicamento</h1>
            </div>
            <form method="post" action="editar_medicamento.php">
                <input type="hidden" name="id" id="edit-id">
                <div class="campo">
                    <label for="edit-nome">Nome do remédio</label>
                    <input type="text" name="nome_remedio" id="edit-nome" required>
                </div>
                <div class="campo">
                    <label for="edit-dosagem">Dosagem</label>
                    <input type="text" name="dosagem" id="edit-dosagem" required>
                </div>
                <div class="campo">
                    <label for="edit-via">Via de administração</label>
                    <select name="via_administracao" id="edit-via" required>
                        <option value="oral">Oral</option>
                        <option value="subcutanea">Subcutânea</option>
                        <option value="intramuscular">Intramuscular</option>
                        <option value="intravenosa">Intravenosa</option>
                        <option value="sublingual">Sublingual</option>
                        <option value="topica">Tópica</option>
                        <option value="inalatoria">Inalatória</option>
                        <option value="retal">Retal</option>
                    </select>
                </div>
                <div style="display:flex;gap:14px;">
                    <div class="campo" style="flex:1">
                        <label for="edit-horario">Horário</label>
                        <input type="time" name="horario" id="edit-horario" required>
                    </div>
                    <div class="campo" style="flex:1">
                        <label for="edit-frequencia">Frequência</label>
                        <select name="frequencia" id="edit-frequencia" required>
                            <option value="1x">1× ao dia</option>
                            <option value="2x">2× ao dia</option>
                            <option value="3x">3× ao dia</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit">Salvar alterações</button>
                    <button type="button" onclick="fecharEdicao()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalConfirmarExclusao" onclick="if(event.target===this)fecharConfirmarExclusao()">
        <div class="caixa inside-overlay" style="max-width:380px;text-align:center;">
            <button class="close-btn" type="button" onclick="fecharConfirmarExclusao()">✕</button>
            <div style="font-size:40px;margin-bottom:12px;">🗑️</div>
            <h3 style="color:#0d2218;margin-bottom:8px;">Excluir medicamento?</h3>
            <p style="color:#5a7a6d;font-size:13px;margin-bottom:24px;">
                Essa ação não pode ser desfeita. O medicamento será removido permanentemente.
            </p>
            <div style="display:flex;gap:10px;justify-content:center;">
                <a id="btnConfirmarExclusao" href="#" class="btn-excluir-confirm">
                    Sim, excluir
                </a>
                <button onclick="fecharConfirmarExclusao()" class="btn-cancelar-confirm">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <script src="js/jscommon.js"></script>
    <script src="js/jsmedicamento.js"></script>
    </body>

</html>