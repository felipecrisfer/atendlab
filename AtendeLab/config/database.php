<?php

$host = '127.0.0.1';
$porta = '3306';
<<<<<<< HEAD
$banco = 'atendelab';
=======
$banco = 'atendlab';
>>>>>>> 97f079d2e8feb20342dd35b2c44841d6ce78172a
$usuario = 'root';
$senha = '';

try {

    $pdo = new PDO(
        "mysql:host={$host};port={$porta};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha
    );

    $pdo->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    $pdo->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {
    exit('Erro ao conectar com o banco de dados.');
}