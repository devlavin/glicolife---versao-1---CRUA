<?php
session_start();
require 'conexao.php';

if (empty($_SESSION['otp_user_id'])) {
    header('Location: cadastro.php');
    exit;
}

$user_id = $_SESSION['otp_user_id'];
$codigo = ($_POST['d1'] ?? '') . ($_POST['d2'] ?? '') . ($_POST['d3'] ?? '')
    . ($_POST['d4'] ?? '') . ($_POST['d5'] ?? '') . ($_POST['d6'] ?? '');

// Busca código válido 
$stmt = $conn->prepare("
    SELECT id FROM otp_verificacao
    WHERE user_id = ? AND codigo = ? AND usado = 0 AND expira_em > NOW()
    LIMIT 1
");
$stmt->bind_param('is', $user_id, $codigo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    $exp = $conn->prepare("
        SELECT id FROM otp_verificacao
        WHERE user_id = ? AND usado = 0 AND expira_em <= NOW()
        LIMIT 1
    ");
    $exp->bind_param('i', $user_id);
    $exp->execute();
    $exp->store_result();

    if ($exp->num_rows > 0) {
        header('Location: verificar.php?erro=expirado');
    } else {
        header('Location: verificar.php?erro=invalido');
    }
    exit;
}

$row = $resultado->fetch_assoc();

// Marca código como usado
$stmt_usado = $conn->prepare("UPDATE otp_verificacao SET usado = 1 WHERE id = ?");
$stmt_usado->bind_param('i', $row['id']);
$stmt_usado->execute();

// Marca usuário como verificado
$stmt_verif = $conn->prepare("UPDATE usuarios SET verificado = 1 WHERE id = ?");
$stmt_verif->bind_param('i', $user_id);
$stmt_verif->execute();

// Busca nome do usuário
$u = $conn->prepare("SELECT nome FROM usuarios WHERE id = ?");
$u->bind_param('i', $user_id);
$u->execute();
$nome = $u->get_result()->fetch_assoc()['nome'];

// inicia sessão de login
unset($_SESSION['otp_user_id'], $_SESSION['otp_email'], $_SESSION['otp_nome']);
$_SESSION['usuario_id'] = $user_id;
$_SESSION['usuario_nome'] = $nome;

header('Location: onboarding.php');
exit;
?>