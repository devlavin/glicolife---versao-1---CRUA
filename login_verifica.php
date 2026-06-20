<?php
session_start();
require 'conexao.php';

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

$stmt = $conn->prepare("SELECT id, nome, senha, onboarding_feito, tipo_diabetes FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if ($usuario && password_verify($senha, $usuario['senha'])) {
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nome'] = $usuario['nome'];
    $_SESSION['tipo_diabetes'] = $usuario['tipo_diabetes'];

    if ($usuario['onboarding_feito'] == 0) {
        header('Location: onboarding.php');
    } else {
        header('Location: home.php');
    }
    exit;
} else {
    header('Location: login.php?erro=1');
    exit;
}
?>