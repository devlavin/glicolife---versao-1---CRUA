<?php
session_start();
require 'conexao.php';

$uid = $_SESSION['usuario_id'];

// Verifica senha
$q = $conn->prepare("SELECT senha, foto FROM usuarios WHERE id = ?");
$q->bind_param('i', $uid);
$q->execute();
$user = $q->get_result()->fetch_assoc();

if (!$user || !password_verify($_POST['senha'], $user['senha'])) {
    header('Location: dados.php?erro=senha_incorreta');
    exit;
}

// Remove foto se existir
if ($user['foto'] && file_exists(__DIR__ . '/' . $user['foto'])) {
    unlink(__DIR__ . '/' . $user['foto']);
}
// Apaga os códigos OTP do usuário antes de excluir a conta
$del_otp = $conn->prepare("DELETE FROM otp_verificacao WHERE user_id = ?");
$del_otp->bind_param('i', $uid);
$del_otp->execute();

// Apaga tokens de recuperação de senha
$del_rec = $conn->prepare("DELETE FROM recuperacao_senha WHERE user_id = ?");
$del_rec->bind_param('i', $uid);
$del_rec->execute();

// Apaga todos os dados do usuário
$d1 = $conn->prepare("DELETE FROM glicose WHERE usuario_id = ?");
$d1->bind_param('i', $uid);
$d1->execute();

$d2 = $conn->prepare("DELETE FROM alimentacao WHERE usuario_id = ?");
$d2->bind_param('i', $uid);
$d2->execute();

$d3 = $conn->prepare("DELETE FROM medicamentos WHERE usuario_id = ?");
$d3->bind_param('i', $uid);
$d3->execute();

$d4 = $conn->prepare("DELETE FROM onboarding_respostas WHERE usuario_id = ?");
$d4->bind_param('i', $uid);
$d4->execute();

$d5 = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
$d5->bind_param('i', $uid);
$d5->execute();

session_destroy();
header('Location: conta_excluida.php');
exit;
?>