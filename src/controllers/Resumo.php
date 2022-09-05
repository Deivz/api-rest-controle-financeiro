<?php

namespace Deivz\ApiRestControleFinanceiro\controllers;

use PDO;

class Resumo
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
                if ($idOuAno !== null && !isset($query) && isset($mes)) {
                    echo json_encode($this->formatarDadosDoResumo($idOuAno, $mes));
                    break;
                }

                http_response_code(404);
                echo json_encode([
                    "mensagem" => "Resumo não encontrado",
                    "codigo" => "404"
                ]);
                break;
        }
    }

    private function getResumoByDate(string $ano, string $mes): array|false
    {
        $sql = "SELECT SUM(valor) FROM receitas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo = [];
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes;";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);

        return $resumo;
    }

    private function formatarDadosDoResumo($idOuAno, $mes): array {
        $resumo = $this->getResumoByDate($idOuAno, $mes);
        $valoresResumo = array_column($resumo, 'sum');
        $resumoDasReceitas = floatval($valoresResumo[0]);
        $resumoDasDespesas = floatval($valoresResumo[1]);

        $resumo = [
            'total_receitas' => $resumoDasReceitas,
            'total_despesas' => $resumoDasDespesas,
            'saldo_final' => $resumoDasReceitas - $resumoDasDespesas
        ];

        return $resumo;
    }
}