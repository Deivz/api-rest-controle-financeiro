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

    public function processarRequisicao(string $metodo, ?string $id): void
    {
        switch ($metodo) {
            case 'GET':
                if ($id === null) {
                    echo json_encode($this->getReceitas());
                    break;
                }

                if ($this->getReceitasById($id)) {
                    echo json_encode($this->getReceitasById($id));
                    break;
                }
                http_response_code(404);
                echo json_encode([
                    "mensagem" => "Receita não encontrada",
                    "codigo" => "404"
                ]);
                break;

            case 'POST':
                $reqData = (array) json_decode(file_get_contents("php://input"));

                if ($this->checarExistenciaNoBanco($reqData)) {
                    echo json_encode([
                        'mensagem' => 'Receita já cadastrada.'
                    ]);
                    break;
                }

                $id = $this->postReceitas($reqData);
                echo json_encode([
                    'id' => $id,
                    'mensagem' => 'Receita inserida com sucesso'
                ]);
                break;

            default:
                echo "chamou o default";
                break;
        }
    }

    public function getReceitas()
    {
        $sql = "SELECT * FROM receitas";
        $stmt = $this->conexao->query($sql);
        $receitas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $receitas[] = $row;
        }

        return $receitas;
    }

    public function getReceitasById($id)
    {
        $sql = "SELECT * FROM receitas WHERE id = :id;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function checarExistenciaNoBanco($reqData): bool
    {
        $sql = "SELECT * FROM receitas WHERE (descricao = :descricao) AND (data = :data);";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", $reqData['descricao'], PDO::PARAM_STR);
        $stmt->bindValue(":data", $reqData['data'], PDO::PARAM_STR);
        $stmt->execute();
        if ($stmt->rowCount() >= 1) {
            return true;
        }
        return false;
    }

    public function postReceitas($reqData): string
    {
        $sql = "INSERT INTO receitas (descricao, valor, data) VALUES(:descricao, :valor, :data);";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":descricao", $reqData['descricao'], PDO::PARAM_STR);
        $stmt->bindValue(":valor", $reqData['valor'], PDO::PARAM_STR);
        $stmt->bindValue(":data", $reqData['data'], PDO::PARAM_STR);
        $stmt->execute();

        return $this->conexao->lastInsertId();
    }
}
