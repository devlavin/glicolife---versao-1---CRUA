<?php
session_start();
require 'conexao.php';

$uid = $_SESSION['usuario_id'];

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    header('Location: dados.php?erro=falha_upload');
    exit;
}

$file = $_FILES['foto'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$permitidos = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $permitidos)) {
    header('Location: dados.php?erro=formato_invalido');
    exit;
}

if ($file['size'] > 2 * 1024 * 1024) {
    header('Location: dados.php?erro=arquivo_grande');
    exit;
}

// Cria pasta se não existir
$pasta = __DIR__ . '/uploads/fotos/';
if (!is_dir($pasta))
    mkdir($pasta, 0755, true);

// Remove foto antiga se existir
$atualQ = $conn->prepare("SELECT foto FROM usuarios WHERE id = ?");
$atualQ->bind_param('i', $uid);
$atualQ->execute();
$fotoAtual = $atualQ->get_result()->fetch_assoc()['foto'] ?? null;
if ($fotoAtual && file_exists(__DIR__ . '/' . $fotoAtual)) {
    unlink(__DIR__ . '/' . $fotoAtual);
}

// Salva foto com nome único
$nomeArquivo = 'foto_' . $uid . '_' . time() . '.' . $ext;
$destino = $pasta . $nomeArquivo;

if (!move_uploaded_file($file['tmp_name'], $destino)) {
    header('Location: dados.php?erro=falha_salvar');
    exit;
}

$caminho = 'uploads/fotos/' . $nomeArquivo;
$stmt = $conn->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
$stmt->bind_param('si', $caminho, $uid);
$stmt->execute();

header('Location: dados.php?foto=atualizada');
exit;
?>