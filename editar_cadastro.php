<?php
session_start();
require 'conexao.php';

$uid = $_SESSION['usuario_id'];

$celular = trim($_POST['celular'] ?? '');
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';
$senha2 = $_POST['senha_confirm'] ?? '';

// Valida e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: dados.php?erro=email_invalido');
    exit;
}

// Se digitou senha nova, valida
if (!empty($senha)) {
    if ($senha !== $senha2) {
        header('Location: dados.php?erro=senhas_diferentes');
        exit;
    }
    if (strlen($senha) < 8) {
        header('Location: dados.php?erro=senha_curta');
        exit;
    }
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET celular=?, email=?, senha=? WHERE id=?");
    $stmt->bind_param('sssi', $celular, $email, $hash, $uid);
} else {
    $stmt = $conn->prepare("UPDATE usuarios SET celular=?, email=? WHERE id=?");
    $stmt->bind_param('ssi', $celular, $email, $uid);
}

$stmt->execute();

header('Location: dados.php?salvo=1');
exit;
?>