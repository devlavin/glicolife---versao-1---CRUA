<?php
session_start();
require 'conexao.php';
require_once __DIR__ . '/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = trim($_POST['email'] ?? '');

$stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    header('Location: esqueci_senha.php?erro=email');
    exit;
}

$user_id = $usuario['id'];
$nome    = $usuario['nome'];

$stmt_inv = $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE user_id = ? AND usado = 0");
$stmt_inv->bind_param('i', $user_id);
$stmt_inv->execute();

$token  = bin2hex(random_bytes(32));
$expira = date('Y-m-d H:i:s', strtotime('+1 hour'));

$ins = $conn->prepare("INSERT INTO recuperacao_senha (user_id, token, expira_em) VALUES (?, ?, ?)");
$ins->bind_param('iss', $user_id, $token, $expira);
$ins->execute();

$link = "http://localhost/glicolife/redefinir_senha.php?token=$token";

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
    $mail->Subject = 'Redefinição de senha - GlicoLife';
    $mail->isHTML(true);
    $mail->Body = "
        <div style='font-family:sans-serif;max-width:400px;margin:0 auto'>
            <h2 style='color:#1a9e7a'>Olá, $nome!</h2>
            <p>Recebemos uma solicitação para redefinir sua senha.</p>
            <p>Clique no botão abaixo para criar uma nova senha. O link é válido por <strong>1 hora</strong>.</p>
            <a href='$link' style='display:inline-block;margin:20px 0;padding:12px 28px;background:#1a9e7a;color:#fff;border-radius:10px;text-decoration:none;font-weight:bold'>
                Redefinir senha
            </a>
            <p style='color:#8aab9e;font-size:12px'>Se você não solicitou isso, ignore este email.</p>
        </div>
    ";
    $mail->send();
} catch (Exception $e) {}

header('Location: esqueci_senha.php?ok=1');
exit;