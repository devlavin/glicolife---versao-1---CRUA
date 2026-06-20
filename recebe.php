<?php
session_start();
require 'conexao.php';
require_once __DIR__ . '/config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$nome          = $_POST['nome']          ?? '';
$email         = $_POST['email']         ?? '';
$celular       = $_POST['celular']       ?? '';
$nascimento    = $_POST['nascimento']    ?? '';
$sexo          = $_POST['sexo']          ?? '';
$tipo_diabetes = $_POST['tipo_diabetes'] ?? '';
$senha         = password_hash($_POST['senha'], PASSWORD_DEFAULT);

$check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$check->bind_param('s', $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header('Location: cadastro.php?erro=email');
    exit;
}
$check->close();

$sql  = "INSERT INTO usuarios (nome, email, celular, data_nascimento, sexo, tipo_diabetes, senha, verificado) VALUES (?, ?, ?, ?, ?, ?, ?, 0)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('sssssss', $nome, $email, $celular, $nascimento, $sexo, $tipo_diabetes, $senha);
$stmt->execute();

$user_id = $conn->insert_id;

$codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expira = date('Y-m-d H:i:s', strtotime('+10 minutes'));

$otp = $conn->prepare("INSERT INTO otp_verificacao (user_id, canal, codigo, expira_em) VALUES (?, 'email', ?, ?)");
$otp->bind_param('iss', $user_id, $codigo, $expira);
$otp->execute();

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
    $mail->Subject = 'Seu código de verificação - GlicoLife';
    $mail->isHTML(true);
    $mail->Body = "
        <div style='font-family:sans-serif;max-width:400px;margin:0 auto'>
            <h2 style='color:#1a9e7a'>Olá, $nome!</h2>
            <p>Seu código de verificação é:</p>
            <div style='font-size:36px;font-weight:bold;letter-spacing:8px;color:#1a2e25;margin:20px 0'>$codigo</div>
            <p style='color:#6a8e80;font-size:13px'>Válido por 10 minutos. Não compartilhe com ninguém.</p>
        </div>
    ";
    $mail->send();
} catch (Exception $e) {}

$_SESSION['otp_user_id'] = $user_id;
$_SESSION['otp_email']   = $email;
$_SESSION['otp_nome']    = $nome;

header('Location: verificar.php');
exit;