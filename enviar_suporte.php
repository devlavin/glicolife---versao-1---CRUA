<?php
session_start();
require_once __DIR__ . '/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$nome     = $_POST['nome']     ?? '';
$email    = $_POST['email']    ?? '';
$assunto  = $_POST['assunto']  ?? '';
$mensagem = $_POST['mensagem'] ?? '';

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

    $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME . ' Suporte');
    $mail->addAddress(SMTP_FROM, 'Suporte GlicoLife');
    $mail->addReplyTo($email, $nome);

    $mail->isHTML(true);
    $mail->Subject = '[GlicoLife Suporte] ' . ucfirst($assunto) . ' — ' . $nome;
    $mail->Body = "
        <div style='font-family:sans-serif;max-width:520px;margin:0 auto;padding:32px;background:#f0f7f4;border-radius:16px'>
            <h2 style='color:#1a9e7a;margin-bottom:4px'>GlicoLife</h2>
            <p style='color:#8aab9e;font-size:13px;margin-bottom:24px'>Nova mensagem de suporte</p>
            <table style='width:100%;border-collapse:collapse;font-size:14px'>
                <tr><td style='padding:10px 0;color:#8aab9e;width:90px'>Nome</td><td style='padding:10px 0;color:#1a2e25;font-weight:600'>{$nome}</td></tr>
                <tr><td style='padding:10px 0;color:#8aab9e'>E-mail</td><td style='padding:10px 0;color:#1a2e25'>{$email}</td></tr>
                <tr><td style='padding:10px 0;color:#8aab9e'>Assunto</td><td style='padding:10px 0;color:#1a2e25'>{$assunto}</td></tr>
            </table>
            <hr style='border:none;border-top:1px solid #d4e8df;margin:20px 0'>
            <p style='color:#8aab9e;font-size:12px;margin-bottom:8px;text-transform:uppercase;letter-spacing:1px'>Mensagem</p>
            <p style='color:#1a2e25;font-size:15px;line-height:1.7'>{$mensagem}</p>
        </div>
    ";

    $mail->send();
    header('Location: suporte.php?enviado=1');
    exit;

} catch (Exception $e) {
    header('Location: suporte.php?erro=1');
    exit;
}