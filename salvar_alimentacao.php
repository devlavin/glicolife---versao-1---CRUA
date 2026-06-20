<?php
session_start();
require 'conexao.php';

// salva em variável primeiro
$tipo = $_POST['tipo'];
$alimento = $_POST['alimento'];
$horario = $_POST['horario'];
$uid = $_SESSION['usuario_id'];

$stmt = $conn->prepare("INSERT INTO alimentacao (usuario_id, tipo, alimento, horario) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $uid, $tipo, $alimento, $horario);
$stmt->execute();

$tipoLabel = ['cafe' => 'Café da manhã', 'almoco' => 'Almoço', 'tarde' => 'Café da tarde', 'jantar' => 'Jantar'];
$label = $tipoLabel[$tipo] ?? $tipo;
$_SESSION['voz_msg'] = "$label registrado com sucesso.";

header("Location: alimentacao.php?ok=1");
exit;