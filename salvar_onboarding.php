<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

$uid = $_SESSION['usuario_id'];

// Garante que tipo_diabetes está na sessão
if (empty($_SESSION['tipo_diabetes'])) {
    $t = $conn->prepare("SELECT tipo_diabetes FROM usuarios WHERE id = ?");
    $t->bind_param('i', $uid);
    $t->execute();
    $_SESSION['tipo_diabetes'] = $t->get_result()->fetch_assoc()['tipo_diabetes'];
}

$tipo = $_SESSION['tipo_diabetes'];

$perguntas_por_tipo = [
    'tipo1' => ['aplicacao', 'hipo_noite', 'monitoramento'],
    'tipo2' => ['tratamento', 'atividade', 'alimentacao'],
    'gestacional' => ['semanas', 'acompanhamento', 'insulina'],
    'prediabetes' => ['alimentacao', 'historico', 'atividade'],
];

$campos = $perguntas_por_tipo[$tipo] ?? [];

// Salva resposta
$stmt = $conn->prepare("INSERT INTO onboarding_respostas (usuario_id, pergunta, resposta) VALUES (?, ?, ?)");

foreach ($campos as $campo) {
    $resposta = $_POST[$campo] ?? '';
    if ($resposta !== '') {
        $stmt->bind_param("iss", $uid, $campo, $resposta);
        $stmt->execute();
    }
}

// Marca como concluído
$upd = $conn->prepare("UPDATE usuarios SET onboarding_feito = 1 WHERE id = ?");
$upd->bind_param("i", $uid);
$upd->execute();

header('Location: home.php');
exit;
?>