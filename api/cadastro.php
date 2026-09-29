<?php
header('Content-Type: application/json; charset=utf-8');
require '../config/db.php';

// Lê o corpo da requisição JSON
$input = file_get_contents('php://input');
$dados = json_decode($input, true);

// Verifica se os dados foram enviados corretamente
if (!$dados) {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Nenhum dado JSON foi enviado ou o formato é inválido.'
    ]);
    exit;
}

$nome  = isset($dados['nome']) ? trim($dados['nome']) : null;
$email = isset($dados['email']) ? filter_var($dados['email'], FILTER_VALIDATE_EMAIL) : null;
$senha = isset($dados['senha']) ? $dados['senha'] : null;

if ($nome && $email && !empty($senha)) {
    // Criptografa a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (:nome, :email, :senha)";
    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => $senhaHash
        ]);

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Usuário cadastrado com sucesso!'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'sucesso' => false,
            'mensagem' => 'Erro ao cadastrar. O e-mail informado já pode estar em uso.'
        ]);
    }
} else {
    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Preencha todos os campos corretamente (nome, email e senha).'
    ]);
}
?>