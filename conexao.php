<?php
require_once __DIR__ . '/config.php';

$conn = new mysqli(DB_HOST, DB_USUARIO, DB_SENHA, DB_BANCO);

if ($conn->connect_error) {
    die('Erro na conexão: ' . $conn->connect_error);
}