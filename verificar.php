<?php
session_start();
require 'conexao.php';

// Se não veio pelo recebe.php, manda pro cadastro
if (empty($_SESSION['otp_user_id'])) {
    header('Location: cadastro.php');
    exit;
}

$erro = $_GET['erro'] ?? '';
$email = $_SESSION['otp_email'];
$nome = $_SESSION['otp_nome'];

// Mascara o email
$partes = explode('@', $email);
$mascarado = substr($partes[0], 0, 2) . '***@' . $partes[1];
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlicoLife - Verificação</title>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/styleverificar.css">
</head>

<body>

    <div class="logo">
        <a href="index.php">
            <span class="glico">Glico</span><span class="life">Life</span>
        </a>
    </div>

    <div class="card">
        <div class="card-title">Verifique seu e-mail</div>
        <p class="card-sub">Enviamos um código de 6 dígitos para:</p>
        <span class="email-destino">
            <?= $mascarado ?>
        </span>

        <?php if ($erro === 'invalido'): ?>
            <div class="erro-msg">Código incorreto ou expirado. Tente novamente.</div>
        <?php elseif ($erro === 'expirado'): ?>
            <div class="erro-msg">Código expirado. Clique em reenviar para receber um novo.</div>
        <?php endif; ?>

        <form method="post" action="confirmar_otp.php" id="form-otp">
            <div class="otp-row">
                <input type="tel" maxlength="1" id="o1" name="d1" autofocus>
                <input type="tel" maxlength="1" id="o2" name="d2">
                <input type="tel" maxlength="1" id="o3" name="d3">
                <input type="tel" maxlength="1" id="o4" name="d4">
                <input type="tel" maxlength="1" id="o5" name="d5">
                <input type="tel" maxlength="1" id="o6" name="d6">
            </div>

            <button type="submit" class="btn-primary" id="btn-verificar" disabled>
                Verificar código
            </button>
        </form>

        <div class="timer" id="timer"></div>

        <div class="extra">
            <form method="post" action="reenviar_otp.php" style="display:inline">
                Não recebeu? <button type="submit"
                    style="background:none;border:none;cursor:pointer;color:#1a9e7a;font-weight:600;font-size:13px;padding:0"
                    id="btn-reenviar" disabled>
                    Reenviar código
                </button>
            </form>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-row input');
        const btnVerificar = document.getElementById('btn-verificar');
        const btnReenviar = document.getElementById('btn-reenviar');

        // Navega entre os inputs auto
        inputs.forEach((inp, i) => {
            inp.addEventListener('input', () => {
                inp.value = inp.value.replace(/\D/, '');
                if (inp.value && i < inputs.length - 1) inputs[i + 1].focus();
                checarCompleto();
            });
            inp.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !inp.value && i > 0) inputs[i - 1].focus();
            });
        });

        function checarCompleto() {
            const completo = [...inputs].every(i => i.value);
            btnVerificar.disabled = !completo;
        }

        // Timer de 5 minutos
        let segundos = 300;
        const timerEl = document.getElementById('timer');

        const intervalo = setInterval(() => {
            segundos--;

            if (segundos <= 0) {
                clearInterval(intervalo);
                timerEl.textContent = 'Código expirado. Reenvie para continuar.';
                btnVerificar.disabled = true;
                btnReenviar.disabled = false;
                return;
            }

            const m = Math.floor(segundos / 60);
            const s = String(segundos % 60).padStart(2, '0');
            timerEl.textContent = `Expira em ${m}:${s}`;

            // Libera reenviar nos últimos 30 segundos ou após 1 minuto
            if (segundos <= 240) btnReenviar.disabled = false;
        }, 1000);
    </script>

</body>

</html>