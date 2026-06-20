<?php
require 'sessao.php';
require 'conexao.php';

$uid = $_SESSION['usuario_id'];

$sql = $conn->query("SELECT nome, data_nascimento, celular, email, sexo, tipo_diabetes, foto FROM usuarios WHERE id = $uid");
$dados = $sql->fetch_assoc();

$nome = $dados['nome'] ?? '—';
$data_nasc = $dados['data_nascimento'] ?? '—';
$celular = $dados['celular'] ?? '—';
$email = $dados['email'] ?? '—';
$sexo = $dados['sexo'] ?? '—';
$tipo_diab = $dados['tipo_diabetes'] ?? '—';
$foto = $dados['foto'] ?? null;

$partes = explode(" ", trim($nome));
$iniciais = strtoupper(substr($partes[0], 0, 1) . (isset($partes[1]) ? substr($partes[1], 0, 1) : ''));

$idade = '—';
if ($data_nasc && $data_nasc !== '—') {
    try {
        $idade = (new DateTime())->diff(new DateTime($data_nasc))->y . ' anos';
    } catch (Exception $e) {
    }
}

$dataNascFmt = '—';
if ($data_nasc && $data_nasc !== '—') {
    try {
        $dataNascFmt = (new DateTime($data_nasc))->format('d/m/Y');
    } catch (Exception $e) {
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta – GlicoLife</title>
    <link rel="stylesheet" href="css/stylelogado.css">
    <link rel="stylesheet" href="css/styledados.css">
    <!-- PWA -->
    <link rel="manifest" href="/glicolife/manifest.json">
    <meta name="theme-color" content="#1a9e7a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="GlicoLife">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
</head>

<body>
    <?php if (isset($_GET['salvo'])): ?>
        <div class="toast toast-sucesso" id="toast">✅ Dados atualizados com sucesso!</div>
    <?php endif; ?>

    <?php if (isset($_GET['erro'])): ?>
        <div class="toast toast-erro" id="toast">
            <?php
            $erros = [
                'email_invalido' => '❌ E-mail inválido.',
                'senhas_diferentes' => '❌ As senhas não coincidem.',
                'senha_curta' => '❌ A senha precisa ter pelo menos 8 caracteres.',
            ];
            echo $erros[$_GET['erro']] ?? '❌ Erro ao salvar.';
            ?>
        </div>
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
            <a href="relatorio.php">Histórico & Relatórios</a>
            <a href="suporte.php">Ajuda & Suporte</a>
            <a href="logout.php" class="sair">Sair</a>
        </div>
        <div class="perfil" id="perfil">
            <span onclick="toggleMenu()"><?php echo $partes[0]; ?> ▾</span>
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

    <div class="dados-layout">

        <div class="perfil-card">
            <div class="perfil-bg"></div>

            <div class="perfil-body">
                <div class="avatar-wrap" onclick="abrirEdicaoFoto()" title="Alterar foto">
                    <?php if ($foto): ?>
                        <img class="avatar" src="<?= htmlspecialchars($foto) ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <div class="avatar avatar-iniciais"><?= $iniciais ?></div>
                    <?php endif; ?>
                    <div class="avatar-edit-badge">
                        <svg width="10" height="10" viewBox="0 0 12 12" fill="none">
                            <path d="M8.5 1.5a1.5 1.5 0 0 1 2.12 2.12L4 10.25 1.5 10.5l.25-2.5L8.5 1.5z"
                                stroke="currentColor" stroke-width="1.3" stroke-linejoin="round" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="mini-stats">
                <div class="mini-stat">
                    <span class="mini-val"><?= $idade ?></span>
                    <span class="mini-lbl">Idade</span>
                </div>
                <div class="mini-stat">
                    <span class="mini-val"><?= htmlspecialchars($sexo) ?></span>
                    <span class="mini-lbl">Sexo</span>
                </div>
                <div class="mini-stat">
                    <span class="mini-val"><?= htmlspecialchars($tipo_diab) ?></span>
                    <span class="mini-lbl">Tipo</span>
                </div>
            </div>
        </div>

        <div class="info-section">
            <div class="section-head">
                <h2>Informações pessoais</h2>
                <button class="btn-edit" onclick="abrirEdicao()">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M9.5 1.5a1.7 1.7 0 0 1 2.4 2.4L4.5 11.5l-3 .5.5-3 7.5-7.5z" stroke="currentColor"
                            stroke-width="1.5" stroke-linejoin="round" />
                    </svg>
                    Editar dados
                </button>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-key">Nome completo</span>
                    <span class="info-val"><?= htmlspecialchars($nome) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Data de nascimento</span>
                    <span class="info-val"><?= $dataNascFmt ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Celular</span>
                    <span class="info-val"><?= htmlspecialchars($celular) ?: '—' ?></span>
                </div>
                <div class="info-item">
                    <span class="info-key">Sexo</span>
                    <span class="info-val"><?= htmlspecialchars($sexo) ?></span>
                </div>
                <div class="info-item span2">
                    <span class="info-key">E-mail</span>
                    <span class="info-val"><?= htmlspecialchars($email) ?: '—' ?></span>
                </div>
            </div>
        </div>

        <div class="danger-section">
            <h3>⚠️ Zona de perigo</h3>
            <p>Ao excluir sua conta, todos os seus dados serão permanentemente removidos. Essa ação não pode ser
                desfeita.</p>
            <button class="btn-danger" onclick="abrirExcluirConta()">🗑️ Excluir minha conta</button>
        </div>

    </div>

    <div class="modal-overlay" id="modalEdicao" onclick="fecharSeForaDoModal(event)">
        <div class="modal-box">
            <button class="modal-close" onclick="fecharEdicao()">✕</button>
            <div class="modal-header">
                <div class="modal-icon">✏️</div>
                <h3>Editar dados</h3>
            </div>
            <form action="editar_cadastro.php" method="POST">
                <div class="campo">
                    <label for="ed-celular">Celular</label>
                    <input type="text" id="ed-celular" name="celular" value="<?= htmlspecialchars($celular) ?>"
                        placeholder="(00) 00000-0000">
                </div>
                <div class="campo">
                    <label for="ed-email">E-mail</label>
                    <input type="email" id="ed-email" name="email" value="<?= htmlspecialchars($email) ?>"
                        placeholder="seu@email.com">
                </div>
                <div class="campo">
                    <label for="ed-senha">Nova senha</label>
                    <input type="password" id="ed-senha" name="senha" placeholder="Deixe em branco para não alterar">
                </div>
                <div class="campo">
                    <label for="ed-senha2">Confirmar nova senha</label>
                    <input type="password" id="ed-senha2" name="senha_confirm" placeholder="Repita a nova senha">
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">Salvar alterações</button>
                    <button type="button" class="btn-cancel" onclick="fecharEdicao()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modalFoto" onclick="if(event.target===this)fecharEdicaoFoto()">
        <div class="modal-box">
            <button class="modal-close" onclick="fecharEdicaoFoto()">✕</button>
            <div class="modal-header">
                <div class="modal-icon">📷</div>
                <h3>Alterar foto de perfil</h3>
            </div>

            <div style="text-align:center;margin:16px 0;">
                <?php if ($foto): ?>
                    <img id="previewFoto" src="<?= htmlspecialchars($foto) ?>"
                        style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #1a9e7a;">
                    <div id="previewIniciais" class="avatar avatar-iniciais" style="display:none;margin:0 auto;">
                        <?= $iniciais ?>
                    </div>
                <?php else: ?>
                    <img id="previewFoto" src=""
                        style="display:none;width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #1a9e7a;">
                    <div id="previewIniciais" class="avatar avatar-iniciais" style="margin:0 auto;">
                        <?= $iniciais ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($foto): ?>
                <div style="text-align:center;margin-bottom:12px;">
                    <form action="remover_foto.php" method="POST" onsubmit="return confirm('Remover foto de perfil?')">
                        <button type="submit" style="background:none;border:1px solid #ef4444;color:#ef4444;
                            padding:6px 14px;border-radius:8px;font-size:12px;cursor:pointer;">
                            🗑️ Remover foto atual
                        </button>
                    </form>
                </div>
            <?php endif; ?>

            <form action="upload_foto.php" method="POST" enctype="multipart/form-data">
                <div class="campo">
                    <label for="inputFoto">Selecionar imagem</label>
                    <input type="file" id="inputFoto" name="foto" accept="image/jpeg,image/png,image/webp" required>
                    <small style="color:#92b3a5;font-size:11px;">JPG, PNG ou WEBP · Máximo 2MB</small>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-save">Salvar foto</button>
                    <button type="button" class="btn-cancel" onclick="fecharEdicaoFoto()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    >
    <div class="modal-overlay" id="modalExcluir" onclick="if(event.target===this)fecharExcluirConta()">
        <div class="modal-box">
            <button class="modal-close" onclick="fecharExcluirConta()">✕</button>
            <div class="modal-header">
                <div class="modal-icon">⚠️</div>
                <h3>Excluir conta</h3>
            </div>
            <p style="color:#5a7a6d;font-size:13px;margin-bottom:16px;text-align:center;">
                Essa ação é <strong>irreversível</strong>. Todos os seus dados serão apagados permanentemente, incluindo
                medições de glicose, alimentação e medicamentos.
            </p>
            <form action="excluir_conta.php" method="POST">
                <div class="campo">
                    <label for="confirma-senha">Digite sua senha para confirmar</label>
                    <input type="password" id="confirma-senha" name="senha" placeholder="Sua senha atual" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn-danger">Excluir permanentemente</button>
                    <button type="button" class="btn-cancel" onclick="fecharExcluirConta()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/jscommon.js"></script>
    <script src="js/jsdados.js"></script>
</body>

</html>