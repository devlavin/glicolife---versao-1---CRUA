<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Login</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/stylelogin.css">
    <!-- PWA -->
    <link rel="manifest" href="/glicolife/manifest.json">
    <meta name="theme-color" content="#1a9e7a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="GlicoLife">
    <link rel="apple-touch-icon" href="/img/icon-192.png">
</head>

<body>

    <div class="logo">
        <a href="index.html">
            <span class="glico">Glico</span><span class="life">Life</span>
        </a>
    </div>

    <div class="card">
        <div class="card-title">Bem-vindo de volta</div>
        <div class="card-sub">Entre na sua conta para continuar</div>

        <?php if (isset($_GET['erro'])): ?>
            <div class="erro-msg">E-mail ou senha incorretos. Tente novamente.</div>
        <?php endif; ?>
        <?php if (isset($_GET['senha_redefinida'])): ?>
            <div class="erro-msg" style="background:rgba(26,158,122,0.07);border-color:rgba(26,158,122,0.2);color:#1a9e7a;">
                ✅ Senha redefinida com sucesso! Faça login.
            </div>
        <?php endif; ?>

        <form method="post" action="login_verifica.php">
            <div class="campo">
                <input type="email" name="email" id="campo-email" required autocomplete="email">
                <label for="campo-email">E-mail</label>
            </div>
            <div class="campo campo-senha-wrap">
                <input type="password" name="senha" id="campo-senha" required autocomplete="current-password">
                <label for="campo-senha">Senha</label>
                <button type="button" class="toggle-senha" onclick="toggleSenha('campo-senha', this)">
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
            <button type="submit" class="btn-primary">Entrar</button>
            <a href="esqueci_senha.php"
                style="display:block;text-align:center;margin-top:12px;font-size:13px;color:#8aab9e;text-decoration:none;">
                Esqueceu sua senha?
            </a>
        </form>

        <div class="extra">
            <div class="divider">ou</div>
            <span>Ainda não tem conta? <a href="cadastro.php">Cadastre-se grátis</a></span>
            <a href="index.php" style="color:#8aab9e; font-size:12px; font-weight:400;">← Voltar ao início</a>
        </div>
    </div>

    <script src="js/jslogin.js"></script>
</body>

</html>