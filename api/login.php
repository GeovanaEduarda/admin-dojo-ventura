<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require '../config/db.php';

// Lê o corpo da requisição JSON
$input = file_get_contents('php://input');
$dados = json_decode($input, true);

if (!$dados) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhum dado JSON foi enviado ou o formato é inválido.'
    ]);
    exit;
}

$email = isset($dados['email']) ? filter_var($dados['email'], FILTER_VALIDATE_EMAIL) : null;
$senha = isset($dados['senha']) ? $dados['senha'] : null;

if ($email && !empty($senha)) {
    $sql = "SELECT id, nome, senha FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica usuário e senha
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Login realizado com sucesso!',
            'usuario' => [
                'id' => $usuario['id'],
                'nome' => $usuario['nome']
            ]
        ]);
    } else {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'E-mail ou senha incorretos.'
        ]);
    }
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Informe o e-mail e a senha.'
    ]);
}
?>