<?php
require_once __DIR__ . '/config.php';

$nome     = trim($_POST['nome']     ?? '');
$email    = trim($_POST['email']    ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');

if (empty($nome) || empty($email) || empty($mensagem) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php?erro=1#contato');
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = SMTP_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = SMTP_USER;
    $mail->Password   = SMTP_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = SMTP_PORT;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addAddress(SMTP_FROM, SMTP_FROM_NAME);
    $mail->addReplyTo($email, $nome);

    $mail->isHTML(true);
    $mail->Subject = '[GlicoLife Contato] Mensagem de ' . $nome;
    $mail->Body = "
        <div style='font-family:sans-serif;max-width:520px;margin:0 auto;padding:32px;background:#f0f7f4;border-radius:16px'>
            <h2 style='color:#1a9e7a;margin-bottom:4px'>GlicoLife</h2>
            <p style='color:#8aab9e;font-size:13px;margin-bottom:24px'>Nova mensagem pelo formulário de contato</p>
            <table style='width:100%;border-collapse:collapse;font-size:14px'>
                <tr><td style='padding:10px 0;color:#8aab9e;width:80px'>Nome</td><td style='padding:10px 0;color:#1a2e25;font-weight:600'>{$nome}</td></tr>
                <tr><td style='padding:10px 0;color:#8aab9e'>E-mail</td><td style='padding:10px 0;color:#1a2e25'>{$email}</td></tr>
            </table>
            <hr style='border:none;border-top:1px solid #d4e8df;margin:20px 0'>
            <p style='color:#8aab9e;font-size:12px;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px'>Mensagem</p>
            <p style='color:#1a2e25;font-size:15px;line-height:1.7'>{$mensagem}</p>
        </div>
    ";

    $mail->send();
    header('Location: index.php?enviado=1#contato');
    exit;

} catch (Exception $e) {
    header('Location: index.php?erro=1#contato');
    exit;
}