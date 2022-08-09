<?php

namespace Deivz\ApiRestControleFinanceiro\controllers;

use PDO;

class Despesas
{
    private PDO $conexao;

    public function __construct(CriadorConexao $conexao)
    {
        $this->conexao = $conexao->conectar();
    }

    public function processarRequisicao(string $metodo, ?string $idOuAno, ?string $mes, ?string $query): void
    {
        switch ($metodo) {
            case 'GET':
                if ($idOuAno === null && !isset($query) && !isset($mes)) {
                    echo json_encode($this->getDespesas());
                    break;
                }

                if ($idOuAno !== null && $this->getDespesasById($idOuAno) && !isset($mes)) {
                    echo json_encode($this->getDespesasById($idOuAno));
                    break;
                }

                if ($idOuAno !== null && !isset($query) && isset($mes)) {
                    echo json_encode($this->getDespesasByDate($idOuAno, $mes));
                    break;
                }

                $descricao = $_GET['descricao'];
                if ($this->getDespesasByDescricao($descricao)) {
                    echo json_encode($this->getDespesasByDescricao($descricao));
                    break;
                }

                http_response_code(404);
                echo json_encode([
                    "mensagem" => "Despesa não encontrada",
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
                        'mensagem' => 'Despesa já cadastrada.'
                    ]);
                    break;
                }

                $idRequisicao = $this->postDespesas($dadosRequisicao);
                http_response_code(201);
                echo json_encode([
                    'id' => $idRequisicao,
                    'mensagem' => 'Despesa inserida com sucesso'
                ]);
                break;

            case 'PUT':
                if ($idOuAno === null) {
                    http_response_code(404);
                    echo json_encode([
                        'mensagem' => 'Despesa não identificada'
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

                if ($this->checarExistenciaNoBanco($dadosRequisicao, $idOuAno)) {
                    http_response_code(422);
                    echo json_encode([
                        'mensagem' => 'Despesa já cadastrada.'
                    ]);
                    break;
                }

                $linha = $this->putDespesas($dadosRequisicao, $idOuAno);
                echo json_encode([
                    'linhas' => $linha,
                    'mensagem' => "Despesa {$idOuAno} atualizada com sucesso"
                ]);
                break;

            case 'DELETE':
                if ($idOuAno === null) {
                    http_response_code(404);
                    echo json_encode([
                        'mensagem' => 'Despesa não identificada'
                    ]);
                    break;
                }

                $linha = $this->deleteDespesas($idOuAno);
                echo json_encode([
                    'linhas' => $linha,
                    'mensagem' => "Despesa {$idOuAno} deletada com sucesso"
                ]);
                break;
        }
    }

    private function getDespesas(): array
    {
        $sql = "SELECT * FROM despesas";
        $stmt = $this->conexao->query($sql);
        $despesas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $despesas[] = $row;
        }

        return $despesas;
    }

    private function getDespesasById(string $id): array|false
    {
        $sql = "SELECT * FROM despesas WHERE id = :id;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getDespesasByDate(string $ano, string $mes): array|false
    {
        $sql = "SELECT * FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $despesas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $despesas[] = $row;
        }

        return $despesas;
    }

    private function getDespesasByDescricao(string $descricao): array|false
    {
        $sql = "SELECT * FROM despesas WHERE descricao LIKE :descricao;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", "%$descricao%", PDO::PARAM_INT);
        $stmt->execute();
        $despesas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $despesas[] = $row;
        }

        return $despesas;
    }

    private function checarExistenciaNoBanco(array $dadosRequisicao, string $id = null): bool
    {
        $sql = "SELECT * FROM despesas WHERE (descricao = :descricao) AND (data = :data);";
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
        $categorias = ["Alimentação", "Saúde", "Moradia", "Transporte", "Educação", "Lazer", "Imprevistos", "Outras"];

        if (empty($dadosRequisicao['descricao'])) {
            $erros[] = "O campo descrição é obrigatório";
        }

        if (empty($dadosRequisicao['valor'])) {
            $erros[] = "O campo valor é obrigatório";
        }

        if (empty($dadosRequisicao['data'])) {
            $erros[] = "O campo data é obrigatório";
        }

        if(!in_array($dadosRequisicao['categoria'], $categorias) && !empty($dadosRequisicao['categoria'])){
            $erros[] = "Categoria inválida";
        }

        return $erros;
    }

    private function postDespesas(array $dadosRequisicao): int
    {
        
        $sql = "INSERT INTO despesas (descricao, valor, data, categoria) VALUES(:descricao, :valor, :data, :categoria);";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", $dadosRequisicao['descricao'], PDO::PARAM_STR);
        $stmt->bindValue(":valor", $dadosRequisicao['valor'], PDO::PARAM_STR);
        $stmt->bindValue(":data", $dadosRequisicao['data'], PDO::PARAM_STR);

        if(empty($dadosRequisicao['categoria'])){
            $stmt->bindValue(":categoria", "Outras", PDO::PARAM_STR);
        }else{
            $stmt->bindValue(":categoria", $dadosRequisicao['categoria'], PDO::PARAM_STR);
        }

        $stmt->execute();

        return $this->conexao->lastInsertId();
    }

    private function putDespesas(array $dadosRequisicao, string $id): int
    {
        $sql = "UPDATE despesas
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

    private function deleteDespesas(string $id): int
    {
        $sql = "DELETE FROM despesas WHERE id = :id;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }
}