<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM trufas WHERE id = :id AND ativo = 1");
            $stmt->execute([':id' => $_GET['id']]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode(['sucesso' => true, 'dados' => $resultado]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Trufa não encontrada.']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM trufas WHERE ativo = 1 ORDER BY sabor ASC");
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $resultado]);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['sabor'])) {
            $sql = "INSERT INTO trufas (sabor, descricao, preco, foto, ativo) 
                    VALUES (:sabor, :descricao, :preco, :foto, :ativo)";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':sabor'     => $dados['sabor'],
                    ':descricao' => $dados['descricao'] ?? null,
                    ':preco'     => $dados['preco'] ?? 0.00,
                    ':foto'      => $dados['foto'] ?? 'assets/img/default-trufa.png',
                    ':ativo'     => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Trufa cadastrada com sucesso!', 'id' => $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O campo sabor é obrigatório.']);
        }
        break;

    case 'PUT':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['id']) && !empty($dados['sabor'])) {
            $sql = "UPDATE trufas 
                    SET sabor = :sabor, descricao = :descricao, preco = :preco, 
                        foto = :foto, ativo = :ativo 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':id'        => $dados['id'],
                    ':sabor'     => $dados['sabor'],
                    ':descricao' => $dados['descricao'] ?? null,
                    ':preco'     => $dados['preco'] ?? 0.00,
                    ':foto'      => $dados['foto'] ?? 'assets/img/default-trufa.png',
                    ':ativo'     => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Trufa atualizada com sucesso!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: id e sabor.']);
        }
        break;

    case 'DELETE':
        $dados = json_decode(file_get_contents('php://input'), true);
        $id = $dados['id'] ?? $_GET['id'] ?? null;

        if ($id) {
            $stmt = $pdo->prepare("UPDATE trufas SET ativo = 0 WHERE id = :id");
            try {
                $stmt->execute([':id' => $id]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Trufa desativada com sucesso!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao desativar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Informe o ID para exclusão.']);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Método não suportado.']);
        break;
}