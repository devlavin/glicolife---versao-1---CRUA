<?php
session_start();
require 'conexao.php';

$uid = $_SESSION['usuario_id'];
$id = intval($_POST['id']);

$stmt = $conn->prepare("UPDATE medicamentos SET nome_remedio=?, dosagem=?, via_administracao=?, horario=?, frequencia=? WHERE id=? AND usuario_id=?");
$stmt->bind_param('sssssii', $_POST['nome_remedio'], $_POST['dosagem'], $_POST['via_administracao'], $_POST['horario'], $_POST['frequencia'], $id, $uid);
$stmt->execute();

header('Location: medicamento.php');
exit;
?>