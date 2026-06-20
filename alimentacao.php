<?php
require 'sessao.php';
require 'conexao.php';

$stmt = $conn->prepare("SELECT * FROM alimentacao WHERE usuario_id = ? AND DATE(criado_em) = CURDATE() ORDER BY horario ASC");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$alimentacao = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$tipoLabel = ['cafe' => 'Café da manhã', 'almoco' => 'Almoço', 'tarde' => 'Café da tarde', 'jantar' => 'Jantar'];
$tipoBadge = ['cafe' => 'badge-amber', 'almoco' => 'badge-green', 'tarde' => 'badge-teal', 'jantar' => 'badge-blue'];

$vozMsg = $_SESSION['voz_msg'] ?? '';
unset($_SESSION['voz_msg']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife — Alimentação</title>
    <link rel="stylesheet" href="css/stylelogado.css">
    <link rel="stylesheet" href="css/stylemedic-alimen.css">
    <link rel="manifest" href="/glicolife/manifest.json">
    <meta name="theme-color" content="#1a9e7a">
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
            <li><a href="medicamento.php">Medicamentos</a></li>
            <li><a href="alimentacao.php" class="active">Alimentação</a></li>
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
                        <button id="btn-fonte-menos" onclick="diminuirFonte()" title="Diminuir texto">A−</button>
                        <button id="btn-fonte-mais" onclick="aumentarFonte()" title="Aumentar texto">A+</button>
                    </div>
                </div>
                <div class="acesso-row">
                    <span class="acesso-label">Leitura em voz</span>
                    <div class="toggle-wrap">
                        <label class="toggle" title="Ligar ou desligar a leitura em voz alta">
                            <input type="checkbox" id="toggleVozCheck" onchange="onToggleVoz(this.checked)">
                            <span class="toggle-slider"></span>
                        </label>
                        <span class="toggle-label" id="vozLabel">Desligada</span>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-hero">
        <div class="hero-icon">🍎</div>
        <h2>Alimentação</h2>
        <p>Acompanhe suas refeições e mantenha uma dieta equilibrada</p>
    </div>

    <div class="section">

        <?php if (!empty($alimentacao)): ?>
            <div class="stats-bar">
                <div class="stat-card">
                    <span class="stat-num"><?= count($alimentacao) ?></span>
                    <span class="stat-label">Refeições registradas</span>
                </div>
                <?php
                $tipos = array_count_values(array_column($alimentacao, 'tipo'));
                arsort($tipos);
                $topTipo = key($tipos);
                ?>
                <div class="stat-card">
                    <span class="stat-num"><?= $tipoLabel[$topTipo] ?? ucfirst($topTipo) ?></span>
                    <span class="stat-label">Mais registrada</span>
                </div>
                <div class="stat-card">
                    <span class="stat-num"><?= count($tipos) ?></span>
                    <span class="stat-label">Tipos distintos</span>
                </div>
            </div>
        <?php endif; ?>

        <div class="toolbar">
            <span class="toolbar-title">🍽️ Suas refeições</span>
            <button class="btn-add" onclick="abrirForm()">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                </svg>
                Registrar refeição
            </button>
        </div>

        <?php if (empty($alimentacao)): ?>
            <div class="empty-state">
                <span class="empty-icon">🥗</span>
                <p>Nenhuma refeição registrada ainda.</p>
                <button class="btn-add" onclick="abrirForm()">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M7 1v12M1 7h12" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    Registrar primeira refeição
                </button>
            </div>
        <?php else: ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Alimentos</th>
                            <th>Horário</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alimentacao as $item):
                            $tipo = $item['tipo'];
                            $badgeClass = $tipoBadge[$tipo] ?? 'badge-green';
                            $label = $tipoLabel[$tipo] ?? htmlspecialchars($tipo);
                            ?>
                            <tr>
                                <td><span class="badge <?= $badgeClass ?>"><?= $label ?></span></td>
                                <td><?= htmlspecialchars($item['alimento']) ?></td>
                                <td>
                                    <span class="time-chip">
                                        <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                                            <circle cx="6" cy="6" r="5" stroke="currentColor" stroke-width="1.4" />
                                            <path d="M6 3v3l2 1.5" stroke="currentColor" stroke-width="1.4"
                                                stroke-linecap="round" />
                                        </svg>
                                        <?= htmlspecialchars($item['horario']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <!-- MODAL -->
    <div class="modal-overlay" id="modalOverlay" onclick="fecharFormOutside(event)">
        <div class="caixa inside-overlay" id="formAlimentacao">
            <button class="close-btn" type="button" onclick="fecharForm()" title="Fechar">✕</button>
            <div class="caixa-header">
                <div class="caixa-icon">🍎</div>
                <h1>Registrar refeição</h1>
            </div>
            <form method="post" action="salvar_alimentacao.php">

                <div class="campo">
                    <label for="tipo">Tipo de refeição</label>
                    <select name="tipo" id="tipo" required>
                        <option value="" disabled selected>Selecione...</option>
                        <option value="cafe">☕ Café da manhã</option>
                        <option value="almoco">🍽️ Almoço</option>
                        <option value="tarde">🍵 Café da tarde</option>
                        <option value="jantar">🌙 Jantar</option>
                    </select>
                </div>

                <div class="campo">
                    <label for="alimento">Alimentos consumidos</label>
                    <textarea name="alimento" id="alimento" required
                        placeholder="Ex: arroz, feijão, frango grelhado..."></textarea>
                    <button type="button" class="btn-mic-textarea hidden" id="mic-alimento" onclick="micAlimento()"
                        title="Falar alimentos" aria-label="Usar microfone">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="9" y="2" width="6" height="12" rx="3" />
                            <path d="M5 10a7 7 0 0014 0M12 19v3M8 22h8" />
                        </svg>
                        Falar alimentos
                    </button>
                </div>

                <div class="campo">
                    <label for="horario">Horário</label>
                    <input type="time" name="horario" id="horario" required>
                </div>

                <div class="form-actions">
                    <button type="submit">Salvar refeição</button>
                    <button type="button" onclick="fecharForm()">Cancelar</button>
                </div>

            </form>
        </div>
    </div>
    <script src="js/jscommon.js"></script>
    <script src="js/jsalimentacao.js"></script>
    </body>

</html>