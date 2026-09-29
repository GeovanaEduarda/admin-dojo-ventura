<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../config/db.php';

$metodo = $_SERVER['REQUEST_METHOD'];

switch ($metodo) {
    case 'GET':
        if (isset($_GET['id'])) {
            $sql = "SELECT * FROM competicoes WHERE id = :id AND ativo = 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $_GET['id']]);
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado) {
                echo json_encode(['sucesso' => true, 'dados' => $resultado]);
            } else {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Competição não encontrada.']);
            }
        } else {
            // Traz apenas os registros ativos no site
            $sql = "SELECT * FROM competicoes WHERE ativo = 1 ORDER BY id DESC";
            $stmt = $pdo->query($sql);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['sucesso' => true, 'dados' => $resultado]);
        }
        break;

    case 'POST':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['atleta_id']) && !empty($dados['torneio'])) {
            $sql = "INSERT INTO competicoes (atleta_id, torneio, colocacao, data_evento, ativo) 
                    VALUES (:atleta_id, :torneio, :colocacao, :data_evento, :ativo)";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':atleta_id'  => $dados['atleta_id'],
                    ':torneio'    => $dados['torneio'],
                    ':colocacao'  => $dados['colocacao'] ?? null,
                    ':data_evento' => $dados['data_evento'] ?? null,
                    ':ativo'      => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);

                echo json_encode([
                    'sucesso' => true, 
                    'mensagem' => 'Competição cadastrada com sucesso!',
                    'id' => $pdo->lastInsertId()
                ]);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao cadastrar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Campos obrigatórios: atleta_id e torneio.']);
        }
        break;

    case 'PUT':
        $dados = json_decode(file_get_contents('php://input'), true);

        if (!empty($dados['id'])) {
            $sql = "UPDATE competicoes 
                    SET atleta_id = :atleta_id, torneio = :torneio, colocacao = :colocacao, 
                        data_evento = :data_evento, ativo = :ativo 
                    WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            try {
                $stmt->execute([
                    ':id'          => $dados['id'],
                    ':atleta_id'   => $dados['atleta_id'],
                    ':torneio'     => $dados['torneio'],
                    ':colocacao'   => $dados['colocacao'] ?? null,
                    ':data_evento' => $dados['data_evento'] ?? null,
                    ':ativo'       => isset($dados['ativo']) ? $dados['ativo'] : 1
                ]);

                echo json_encode(['sucesso' => true, 'mensagem' => 'Competição atualizada com sucesso!']);
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
            // Soft Delete: Apenas desativa o registro alterando o campo 'ativo' para 0
            $sql = "UPDATE competicoes SET ativo = 0 WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            try {
                $stmt->execute([':id' => $id]);
                echo json_encode(['sucesso' => true, 'mensagem' => 'Competição desativada com sucesso!']);
            } catch (PDOException $e) {
                echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao desativar: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Informe o ID para exclusão.']);
        }
        break;

    default:
        echo json_encode(['sucesso' => false, 'mensagem' => 'Método HTTP não suportado.']);
        break;
}