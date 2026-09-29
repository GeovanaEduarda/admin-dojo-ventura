<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM atletas WHERE id = :id AND ativo = 1");
            $stmt->execute([':id' => $_GET['id']]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode(['sucesso' => true, 'dados' => $resultado]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Atleta não encontrado.']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM atletas WHERE ativo = 1 ORDER BY nome ASC");
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $resultado]);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['nome']) && !empty($dados['categoria'])) {
            $sql = "INSERT INTO atletas (nome, categoria, faixa, foto, bio, ativo) 
                    VALUES (:nome, :categoria, :faixa, :foto, :bio, :ativo)";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':nome'      => $dados['nome'],
                    ':categoria' => $dados['categoria'],
                    ':faixa'     => $dados['faixa'] ?? 'Branca',
                    ':foto'      => $dados['foto'] ?? 'assets/img/default-atleta.png',
                    ':bio'       => $dados['bio'] ?? null,
                    ':ativo'     => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Atleta cadastrado com sucesso!', 'id' => $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: nome e categoria.']);
        }
        break;

    case 'PUT':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['id']) && !empty($dados['nome']) && !empty($dados['categoria'])) {
            $sql = "UPDATE atletas 
                    SET nome = :nome, categoria = :categoria, faixa = :faixa, 
                        foto = :foto, bio = :bio, ativo = :ativo 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':id'        => $dados['id'],
                    ':nome'      => $dados['nome'],
                    ':categoria' => $dados['categoria'],
                    ':faixa'     => $dados['faixa'] ?? 'Branca',
                    ':foto'      => $dados['foto'] ?? 'assets/img/default-atleta.png',
                    ':bio'       => $dados['bio'] ?? null,
                    ':ativo'     => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Atleta atualizado com sucesso!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: id, nome e categoria.']);
        }
        break;

    case 'DELETE':
        $dados = json_decode(file_get_contents('php://input'), true);
        $id = $dados['id'] ?? $_GET['id'] ?? null;

        if ($id) {
            $stmt = $pdo->prepare("UPDATE atletas SET ativo = 0 WHERE id = :id");
            try {
                $stmt->execute([':id' => $id]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Atleta desativado com sucesso!']);
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