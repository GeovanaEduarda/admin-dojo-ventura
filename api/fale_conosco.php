<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['id'])) {
            $sql = "SELECT * FROM fale_conosco WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode(['sucesso' => true, 'dados' => $resultado]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Mensagem não encontrada.']);
            }
        } else {
            $sql = "SELECT * FROM fale_conosco ORDER BY id DESC";
            $stmt = $pdo->query($sql);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $resultado]);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['nome']) && !empty($dados['whatsapp']) && !empty($dados['assunto']) && !empty($dados['mensagem'])) {
            $sql = "INSERT INTO fale_conosco (nome, whatsapp, assunto, mensagem, lido) 
                    VALUES (:nome, :whatsapp, :assunto, :mensagem, :lido)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':nome'     => $dados['nome'],
                    ':whatsapp' => $dados['whatsapp'],
                    ':assunto'  => $dados['assunto'],
                    ':mensagem' => $dados['mensagem'],
                    ':lido'     => isset($dados['lido']) ? $dados['lido'] : 0
                ]);

                echo json_encode([
                    'sucesso'  => true, 
                    'mensagem' => 'Mensagem cadastrada com sucesso!',
                    'id'       => $pdo->lastInsertId()
                ]);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: nome, whatsapp, assunto e mensagem.']);
        }
        break;

    case 'PUT':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['id'])) {
            $sql = "UPDATE fale_conosco 
                    SET nome = :nome, whatsapp = :whatsapp, assunto = :assunto, 
                        mensagem = :mensagem, lido = :lido 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':id'       => $dados['id'],
                    ':nome'     => $dados['nome'],
                    ':whatsapp' => $dados['whatsapp'],
                    ':assunto'  => $dados['assunto'],
                    ':mensagem' => $dados['mensagem'],
                    ':lido'     => isset($dados['lido']) ? $dados['lido'] : 0
                ]);

                echo json_encode(['sucesso' => true, 'mensagem' => 'Mensagem atualizada com sucesso!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao atualizar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'O campo id é obrigatório para atualização.']);
        }
        break;

    case 'DELETE':
        $dados = json_decode(file_get_contents('php://input'), true);
        $id = $dados['id'] ?? $_GET['id'] ?? null;

        if ($id) {
            $sql = "DELETE FROM fale_conosco WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            try {
                $stmt->execute([':id' => $id]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Mensagem excluída com sucesso!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Informe o ID para exclusão.']);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Método HTTP não suportado.']);
        break;
}