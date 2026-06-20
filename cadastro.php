<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Cadastro</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/stylecadastro.css">
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
        <a href="index.php">
            <span class="glico">Glico</span><span class="life">Life</span>
        </a>
    </div>

    <div class="card">
        <div class="card-title">Crie sua conta</div>
        <div class="card-sub">Comece a monitorar sua saúde hoje mesmo</div>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'email'): ?>
            <div class="erro-msg">Este e-mail já está cadastrado. Tente fazer login.</div>
        <?php endif; ?>

        <form method="post" action="recebe.php" id="form-cadastro" novalidate>

            <p class="section-label">Dados pessoais</p>

            <div class="campo">
                <input type="text" name="nome" id="nome" required autocomplete="name">
                <label for="nome">Nome completo</label>
            </div>

            <div class="campo">
                <input type="email" name="email" id="email" required autocomplete="email">
                <label for="email">E-mail</label>
            </div>

            <div class="row">
                <div class="campo">
                    <input type="tel" name="celular" id="celular" required autocomplete="tel">
                    <label for="celular">Celular (DDD)</label>
                </div>
                <div class="campo">
                    <input type="date" name="nascimento" id="nascimento" required>
                    <label for="nascimento">Nascimento</label>
                </div>
            </div>

            <div class="row">
                <div class="campo">
                    <select name="sexo" id="sexo" required>
                        <option value="" disabled selected></option>
                        <option value="feminino">Feminino</option>
                        <option value="masculino">Masculino</option>
                    </select>
                    <label for="sexo">Sexo</label>
                </div>
                <div class="campo">
                    <select name="tipo_diabetes" id="tipo_diabetes" required>
                        <option value="" disabled selected></option>
                        <option value="tipo1">Tipo 1</option>
                        <option value="tipo2">Tipo 2</option>
                        <option value="gestacional">Gestacional</option>
                        <option value="prediabetes">Pré-diabetes</option>
                    </select>
                    <label for="tipo_diabetes">Tipo de diabetes</label>
                </div>
            </div>

            <p class="section-label">Segurança</p>

            <div class="campo campo-senha-wrap">
                <input type="password" name="senha" id="senha" required autocomplete="new-password">
                <label for="senha">Senha</label>
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

            <div class="strength-wrap">
                <div class="strength-bar">
                    <span id="s1"></span><span id="s2"></span>
                    <span id="s3"></span><span id="s4"></span>
                </div>
                <span class="strength-text" id="strength-text"></span>
            </div>

            <span class="erro-inline" id="erro-inline"></span>
            <div style="font-size:13px; color:#6a8e80; margin-top:8px;">
                <label style="display:flex; align-items:flex-start; gap:8px; cursor:pointer;">
                    <input type="checkbox" name="aceite_termos" required style="margin-top:3px; accent-color:#1a9e7a;">
                    Li e aceito os <a href="termos.php" target="_blank" style="color:#1a9e7a; font-weight:600;">Termos
                        de Uso</a>
                </label>
            </div>

            <button type="submit" class="btn-primary">Criar conta</button>
        </form>

        <div class="extra">
            <div class="divider">ou</div>
            <span>Já tem conta? <a href="login.php">Entrar</a></span>
            <a href="index.php" style="color:#8aab9e; font-size:12px; font-weight:400;">← Voltar ao início</a>
        </div>
    </div>

</body>
<script src="js/jscadastro.js"></script>

</html>