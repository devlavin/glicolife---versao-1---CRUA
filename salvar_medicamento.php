<?php
session_start();
require_once 'conexao.php';

$nome = $_POST['nome_remedio']; 

$stmt = $conn->prepare("
    INSERT INTO medicamentos 
        (usuario_id, nome_remedio, dosagem, via_administracao, horario, frequencia)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->bind_param(
    "isssss",
    $_SESSION['usuario_id'],
    $nome,                       
    $_POST['dosagem'],
    $_POST['via_administracao'],
    $_POST['horario'],
    $_POST['frequencia']
);

$stmt->execute();

$_SESSION['voz_msg'] = "Medicamento $nome registrado com sucesso.";

header("Location: medicamento.php?ok=1");
exit;