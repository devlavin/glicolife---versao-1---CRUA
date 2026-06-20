<?php
if (session_status() === PHP_SESSION_NONE)
    session_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Recuperar senha</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/csscadastro.css">
</head>

<body>

    <div class="logo">
        <a href="index.php">
            <span class="glico">Glico</span><span class="life">Life</span>
        </a>
    </div>

    <div class="card">
        <div class="card-title">Recuperar senha</div>
        <div class="card-sub">Digite seu e-mail e enviaremos um link para redefinir sua senha</div>

        <?php if (isset($_GET['erro']) && $_GET['erro'] === 'email'): ?>
            <div class="erro-msg">E-mail não encontrado. Verifique e tente novamente.</div>
        <?php endif; ?>

        <?php if (isset($_GET['ok'])): ?>
            <div
                style="background:rgba(26,158,122,0.07);border:1px solid rgba(26,158,122,0.2);color:#1a9e7a;font-size:13px;padding:10px 14px;border-radius:10px;margin-bottom:18px;text-align:center;">
                Link enviado! Verifique seu e-mail.
            </div>
        <?php endif; ?>

        <form method="post" action="enviar_recuperacao.php">
            <div class="campo">
                <input type="email" name="email" id="email" required autocomplete="email">
                <label for="email">E-mail</label>
            </div>
            <button type="submit" class="btn-primary">Enviar link</button>
        </form>

        <div class="extra">
            <span>Lembrou a senha? <a href="login.php">Entrar</a></span>
        </div>
    </div>

</body>

</html>