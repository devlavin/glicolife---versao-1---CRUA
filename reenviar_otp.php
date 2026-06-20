<?php
session_start();
require 'conexao.php';
require_once __DIR__ . '/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (empty($_SESSION['otp_user_id'])) {
    header('Location: cadastro.php');
    exit;
}

$user_id = $_SESSION['otp_user_id'];
$email   = $_SESSION['otp_email'];
$nome    = $_SESSION['otp_nome'];

$stmt_inv = $conn->prepare("UPDATE otp_verificacao SET usado = 1 WHERE user_id = ? AND usado = 0");
$stmt_inv->bind_param('i', $user_id);
$stmt_inv->execute();

$codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$stmt_ins = $conn->prepare("INSERT INTO otp_verificacao (user_id, canal, codigo, expira_em) VALUES (?, 'email', ?, ?)");
$stmt_ins->bind_param('iss', $user_id, $codigo, $expira);
$stmt_ins->execute();

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
    $mail->addAddress($email, $nome);
    $mail->Subject = 'Novo código de verificação - GlicoLife';
    $mail->isHTML(true);
    $mail->Body = "
        <div style='font-family:sans-serif;max-width:400px;margin:0 auto'>
            <h2 style='color:#1a9e7a'>Olá, $nome!</h2>
            <p>Seu novo código de verificação é:</p>
            <div style='font-size:36px;font-weight:bold;letter-spacing:8px;color:#1a2e25;margin:20px 0'>$codigo</div>
            <p style='color:#6a8e80;font-size:13px'>Válido por 10 minutos. Não compartilhe com ninguém.</p>
        </div>
    ";
    $mail->send();
} catch (Exception $e) {}

header('Location: verificar.php');
exit;