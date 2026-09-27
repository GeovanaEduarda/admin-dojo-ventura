<?php
$host    = 'localhost';
$db      = 'dojo_ventura';
$user    = 'root';
$pass    = ''; // Altera a palavra-passe se o teu MySQL tiver uma definida
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false, 
        'message' => 'Falha na conexão com a base de dados: ' . $e->getMessage()
    ]);
    exit;
}
?>