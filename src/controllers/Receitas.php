<?php

namespace Deivz\ApiRestControleFinanceiro\controllers;

use PDO;

class Receitas
{
    private PDO $conexao;

    public function __construct(CriadorConexao $conexao)
    {
        $this->conexao = $conexao->conectar();
    }

    public function processarRequisicao(string $metodo, ?string $id, ?string $query): void
    {
        switch ($metodo) {
            case 'GET':
                if ($id === null && !isset($query)) {
                    echo json_encode($this->getReceitas());
                    break;
                }

                if ($id !== null && $this->getReceitasById($id)) {
                    echo json_encode($this->getReceitasById($id));
                    break;
                }

                $descricao = $_GET['descricao'];
                if ($this->getReceitasByDescricao($descricao)) {
                    echo json_encode($this->getReceitasByDescricao($descricao));
                    break;
                }

                http_response_code(404);
                echo json_encode([
                    "mensagem" => "Receita não encontrada",
                    "codigo" => "404"
                ]);
                break;

            case 'POST':
                $dadosRequisicao = (array) json_decode(file_get_contents("php://input"), true);

                $erros = $this->validarDados($dadosRequisicao);

                if (!empty($erros)) {
                    http_response_code(422);
                    echo json_encode(["erros" => $erros]);
                    break;
                }

                if ($this->checarExistenciaNoBanco($dadosRequisicao)) {
                    http_response_code(422);
                    echo json_encode([
                        'mensagem' => 'Receita já cadastrada.'
                    ]);
                    break;
                }

                $idRequisicao = $this->postReceitas($dadosRequisicao);
                http_response_code(201);
                echo json_encode([
                    'id' => $idRequisicao,
                    'mensagem' => 'Receita inserida com sucesso'
                ]);
                break;

            case 'PUT':
                if ($id === null) {
                    http_response_code(404);
                    echo json_encode([
                        'mensagem' => 'Receita não identificada'
                    ]);
                    break;
                }

                $dadosRequisicao = (array) json_decode(file_get_contents("php://input"), true);

                $erros = $this->validarDados($dadosRequisicao);

                if (!empty($erros)) {
                    http_response_code(422);
                    echo json_encode(["erros" => $erros]);
                    break;
                }

                if ($this->checarExistenciaNoBanco($dadosRequisicao, $id)) {
                    http_response_code(422);
                    echo json_encode([
                        'mensagem' => 'Receita já cadastrada.'
                    ]);
                    break;
                }

                $linha = $this->putReceitas($dadosRequisicao, $id);
                echo json_encode([
                    'linhas' => $linha,
                    'mensagem' => "Receita {$id} atualizada com sucesso"
                ]);
                break;

            case 'DELETE':
                if ($id === null) {
                    http_response_code(404);
                    echo json_encode([
                        'mensagem' => 'Receita não identificada'
                    ]);
                    break;
                }

                $linha = $this->deleteReceitas($id);
                echo json_encode([
                    'linhas' => $linha,
                    'mensagem' => "Receita {$id} deletada com sucesso"
                ]);
                break;
        }
    }

    private function getReceitas(): array
    {
        $sql = "SELECT * FROM receitas";
        $stmt = $this->conexao->query($sql);
        $receitas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $receitas[] = $row;
        }

        return $receitas;
    }

    private function getReceitasById(string $id): array|false
    {
        $sql = "SELECT * FROM receitas WHERE id = :id;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getReceitasByDescricao(string $descricao): array|false
    {
        $sql = "SELECT * FROM receitas WHERE descricao LIKE :descricao;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", "%$descricao%", PDO::PARAM_INT);
        $stmt->execute();
        $receitas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $receitas[] = $row;
        }

        return $receitas;
    }

    private function checarExistenciaNoBanco(array $dadosRequisicao, string $id = null): bool
    {
        $sql = "SELECT * FROM receitas WHERE (descricao = :descricao) AND (data = :data);";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", $dadosRequisicao['descricao'], PDO::PARAM_STR);
        $stmt->bindValue(":data", $dadosRequisicao['data'], PDO::PARAM_STR);
        $stmt->execute();

        if ($id === null) {
            if ($stmt->rowCount() >= 1) {
                return true;
            }
            return false;
        }

        if ($stmt->rowCount() >= 1) {
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row['id'] == $id) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    private function validarDados(array $dadosRequisicao): array
    {
        $erros = [];

        if (empty($dadosRequisicao['descricao'])) {
            $erros[] = "O campo descrição é obrigatório";
        }

        if (empty($dadosRequisicao['valor'])) {
            $erros[] = "O campo valor é obrigatório";
        }

        if (empty($dadosRequisicao['data'])) {
            $erros[] = "O campo data é obrigatório";
        }

        return $erros;
    }

    private function postReceitas(array $dadosRequisicao): int
    {
        $sql = "INSERT INTO receitas (descricao, valor, data) VALUES(:descricao, :valor, :data);";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", $dadosRequisicao['descricao'], PDO::PARAM_STR);
        $stmt->bindValue(":valor", $dadosRequisicao['valor'], PDO::PARAM_STR);
        $stmt->bindValue(":data", $dadosRequisicao['data'], PDO::PARAM_STR);
        $stmt->execute();

        return $this->conexao->lastInsertId();
    }

    private function putReceitas(array $dadosRequisicao, string $id): int
    {
        $sql = "UPDATE receitas
                SET descricao = :descricao, valor = :valor, data = :data
                WHERE id = :id;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", $dadosRequisicao['descricao'], PDO::PARAM_STR);
        $stmt->bindValue(":valor", $dadosRequisicao['valor'], PDO::PARAM_STR);
        $stmt->bindValue(":data", $dadosRequisicao['data'], PDO::PARAM_STR);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    private function deleteReceitas(string $id): int
    {
        $sql = "DELETE FROM receitas WHERE id = :id;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}