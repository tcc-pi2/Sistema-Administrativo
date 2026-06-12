<?php
// Conexão com o banco MySQL.
// No XAMPP, normalmente o usuário é root e a senha fica vazia.

$host = 'localhost';
$banco = 'gastrotech_admin';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$banco};charset=utf8mb4",
        $usuario,
        $senha,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $erro) {
    die('Erro ao conectar no banco. Confira se o MySQL está ligado e se o database.sql foi importado.');
}
