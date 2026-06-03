<?php


$host = "localhost";
$banco = "aula_univille;"
$usuario = "root";
$senha = "";
    try{
        $spo = new PDO (
            "mysql:hoot=$host; dbname=$banco; charset=utf8",
            $usuario,
            $senha
        );
        echo "Conexão realizada com sucesso!"; 
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }