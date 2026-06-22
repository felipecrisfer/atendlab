<?php

$host = "localhost";
$banco = "atendlab";
$usuario = "root";
$senha = "";
    try {
       $pdo =new PDO(
        "mysql:host=$host;dbname=$banco;charset=utf8",
        $usuario,
        $senha
       );
        echo "Conexão deu certo";
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }