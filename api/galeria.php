<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM galeria WHERE id = :id AND ativo = 1");
            $stmt->execute([':id' => $_GET['id']]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode(['sucesso' => true, 'dados' => $resultado]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Imagem não encontrada.']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM galeria WHERE ativo = 1 ORDER BY id DESC");
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $resultado]);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['caminho_imagem'])) {
            $sql = "INSERT INTO galeria (titulo, caminho_imagem, ativo) 
                    VALUES (:titulo, :caminho_imagem, :ativo)";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':titulo'         => $dados['titulo'] ?? null,
                    ':caminho_imagem' => $dados['caminho_imagem'],
                    ':ativo'          => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Imagem cadastrada com sucesso!', 'id' => $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O campo caminho_imagem é obrigatório.']);
        }
        break;

    case 'PUT':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['id']) && !empty($dados['caminho_imagem'])) {
            $sql = "UPDATE galeria 
                    SET titulo = :titulo, caminho_imagem = :caminho_imagem, ativo = :ativo 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':id'             => $dados['id'],
                    ':titulo'         => $dados['titulo'] ?? null,
                    ':caminho_imagem' => $dados['caminho_imagem'],
                    ':ativo'          => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Imagem atualizada com sucesso!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: id e caminho_imagem.']);
        }
        break;

    case 'DELETE':
        $dados = json_decode(file_get_contents('php://input'), true);
        $id = $dados['id'] ?? $_GET['id'] ?? null;

        if ($id) {
            $stmt = $pdo->prepare("UPDATE galeria SET ativo = 0 WHERE id = :id");
            try {
                $stmt->execute([':id' => $id]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Imagem desativada com sucesso!']);
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