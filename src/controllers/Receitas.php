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

    public function processarRequisicao($metodo)
    {
        switch ($metodo) {
            case 'GET':
                $receitas = $this->getReceitas();

                // echo json_encode([
                //     ""
                // ]);
            
                break;
            
            case 'POST':
                $reqData = (array) json_decode(file_get_contents("php://input"));
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
        $receitas = [];
        $sql = "SELECT * FROM receitas";
        $stmt = $this->conexao->query($sql);
        $receitas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $receitas;
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