<?php
session_start();
require 'conexao.php';

$token = $_POST['token'] ?? '';
$senha = $_POST['senha'] ?? '';
$confirmar = $_POST['confirmar'] ?? '';

// se as senhas coincidem
if ($senha !== $confirmar) {
    header("Location: redefinir_senha.php?token=$token&erro=senha");
    exit;
}

// Valida novamente
$stmt = $conn->prepare("
    SELECT id, user_id FROM recuperacao_senha
    WHERE token = ? AND usado = 0 AND expira_em > NOW()
    LIMIT 1
");
$stmt->bind_param('s', $token);
$stmt->execute();
$rec = $stmt->get_result()->fetch_assoc();

if (!$rec) {
    header('Location: esqueci_senha.php?erro=token');
    exit;
}

// Atualiza a senha
$nova_senha = password_hash($senha, PASSWORD_DEFAULT);
$upd = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
$upd->bind_param('si', $nova_senha, $rec['user_id']);
$upd->execute();

// Invalida o token
$inv = $conn->prepare("UPDATE recuperacao_senha SET usado = 1 WHERE id = ?");
$inv->bind_param('i', $rec['id']);
$inv->execute();

header('Location: login.php?senha_redefinida=1');
exit;
?>