<?php
session_start();
require 'conexao.php';

$uid = $_SESSION['usuario_id'];

$q = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
$q->bind_param('i', $uid);
$q->execute();
$foto = $q->get_result()->fetch_assoc()['foto'] ?? null;

if ($foto && file_exists(__DIR__ . '/' . $foto)) {
    unlink(__DIR__ . '/' . $foto);
}

$stmt = $conn->prepare("UPDATE usuarios SET foto = NULL WHERE id = ?");
$stmt->bind_param('i', $uid);
$stmt->execute();

header('Location: dados.php?foto=removida');
exit;
?>