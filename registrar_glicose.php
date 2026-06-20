<?php
session_start();
require 'conexao.php';

$usuario_id = $_SESSION['usuario_id'] ?? null;
$valor = intval($_POST['valor'] ?? 0);
$momento = $_POST['momento'] ?? 'Não informado';

if (!$usuario_id || $valor < 20 || $valor > 600) {
    header('Location: glicose.php?erro=1');
    exit;
}

$stmt = $conn->prepare("INSERT INTO glicose (usuario_id, valor, momento) VALUES (?, ?, ?)");
$stmt->bind_param('iis', $usuario_id, $valor, $momento);
$stmt->execute();

if ($valor < 70)
    $st = 'baixa, atenção';
elseif ($valor > 180)
    $st = 'alta, atenção';
else
    $st = 'normal';
$_SESSION['voz_msg'] = "Glicose registrada. Valor: $valor miligramas por decilitro. Status: $st.";

header('Location: glicose.php?ok=1');
exit;