<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM redes_sociais WHERE id = :id AND ativo = 1");
            $stmt->execute([':id' => $_GET['id']]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode(['sucesso' => true, 'dados' => $resultado]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Rede social não encontrada.']);
            }
        } else {
            $stmt = $pdo->query("SELECT * FROM redes_sociais WHERE ativo = 1 ORDER BY ordem ASC, id DESC");
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $resultado]);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['nome']) && !empty($dados['url'])) {
            $sql = "INSERT INTO redes_sociais (nome, url, icone, ordem, ativo) 
                    VALUES (:nome, :url, :icone, :ordem, :ativo)";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':nome'  => $dados['nome'],
                    ':url'   => $dados['url'],
                    ':icone' => $dados['icone'] ?? 'bi-link',
                    ':ordem' => $dados['ordem'] ?? 0,
                    ':ativo' => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Rede social cadastrada!', 'id' => $pdo->lastInsertId()]);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: nome e url.']);
        }
        break;

    case 'PUT':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['id']) && !empty($dados['nome']) && !empty($dados['url'])) {
            $sql = "UPDATE redes_sociais 
                    SET nome = :nome, url = :url, icone = :icone, ordem = :ordem, ativo = :ativo 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':id'    => $dados['id'],
                    ':nome'  => $dados['nome'],
                    ':url'   => $dados['url'],
                    ':icone' => $dados['icone'] ?? 'bi-link',
                    ':ordem' => $dados['ordem'] ?? 0,
                    ':ativo' => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Rede social atualizada!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: id, nome e url.']);
        }
        break;

    case 'DELETE':
        $dados = json_decode(file_get_contents('php://input'), true);
        $id = $dados['id'] ?? $_GET['id'] ?? null;

        if ($id) {
            $stmt = $pdo->prepare("UPDATE redes_sociais SET ativo = 0 WHERE id = :id");
            try {
                $stmt->execute([':id' => $id]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Rede social desativada!']);
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