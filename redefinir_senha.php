<?php
session_start();
require 'conexao.php';

$token = $_GET['token'] ?? '';

// Valida o token
$stmt = $conn->prepare("
    SELECT r.id, r.user_id, u.email FROM recuperacao_senha r
    JOIN usuarios u ON u.id = r.user_id
    WHERE r.token = ? AND r.usado = 0 AND r.expira_em > NOW()
    LIMIT 1
");
$stmt->bind_param('s', $token);
$stmt->execute();
$rec = $stmt->get_result()->fetch_assoc();

if (!$rec) {
    // Token inválido ou expirado
    header('Location: esqueci_senha.php?erro=token');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Nova senha</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/stylecadastro.css">
</head>

<body>

    <div class="logo">
        <a href="index.php">
            <span class="glico">Glico</span><span class="life">Life</span>
        </a>
    </div>

    <div class="card">
        <div class="card-title">Nova senha</div>
        <div class="card-sub">Digite sua nova senha abaixo</div>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'senha'): ?>
            <div class="erro-msg">As senhas não coincidem. Tente novamente.</div>
        <?php endif; ?>

        <form method="post" action="salvar_nova_senha.php">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

            <div class="campo campo-senha-wrap">
                <input type="password" name="senha" id="senha" required autocomplete="new-password">
                <label for="senha">Nova senha</label>
                <button type="button" class="toggle-senha" onclick="toggleSenha('senha', this)">
                    <svg class="olho-aberto" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg class="olho-fechado" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" style="display:none">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </button>
            </div>

            <div class="campo campo-senha-wrap">
                <input type="password" name="confirmar" id="confirmar" required autocomplete="new-password">
                <label for="confirmar">Confirmar senha</label>
                <button type="button" class="toggle-senha" onclick="toggleSenha('confirmar', this)">
                    <svg class="olho-aberto" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <svg class="olho-fechado" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" style="display:none">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" />
                        <path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" />
                        <line x1="1" y1="1" x2="23" y2="23" />
                    </svg>
                </button>
            </div>

            <button type="submit" class="btn-primary">Salvar nova senha</button>
        </form>

        <div class="extra">
            <span>Lembrou a senha? <a href="login.php">Entrar</a></span>
        </div>
    </div>

    <script src="js/jscadastro.js"></script>
</body>

</html>