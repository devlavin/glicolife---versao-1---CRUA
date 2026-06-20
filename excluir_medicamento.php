<?php
session_start();
require 'conexao.php';

$uid = $_SESSION['usuario_id'];
$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM medicamentos WHERE id=? AND usuario_id=?");
$stmt->bind_param('ii', $id, $uid);
$stmt->execute();

header('Location: medicamento.php');
exit;