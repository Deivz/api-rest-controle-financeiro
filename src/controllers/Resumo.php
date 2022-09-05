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

    private function getResumoByCategoria(string $ano, string $mes): array|false
    {
        $resumo = [];

        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Alimentação';";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);

        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Saúde';";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Moradia';";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Transporte';";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Educação';";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Lazer';";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Imprevistos';";
        $stmt = $this->conexao->prepare($sql);
        $stmt->bindValue(":ano", $ano, PDO::PARAM_INT);
        $stmt->bindValue(":mes", $mes, PDO::PARAM_INT);
        $stmt->execute();
        $resumo[] = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sql = "SELECT SUM(valor) FROM despesas WHERE EXTRACT(YEAR FROM data) = :ano AND EXTRACT(MONTH FROM data) = :mes AND categoria = 'Outras';";
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

        $resumoCategoria = $this->getResumoByCategoria($idOuAno, $mes);
        $valoresCategoria = array_column($resumoCategoria, 'sum');
        $categoriaAlimentacao = floatval($valoresCategoria[0]);
        $categoriaSaude = floatval($valoresCategoria[1]);
        $categoriaMoradia = floatval($valoresCategoria[2]);
        $categoriaTransporte = floatval($valoresCategoria[3]);
        $categoriaEducacao = floatval($valoresCategoria[4]);
        $categoriaLazer = floatval($valoresCategoria[5]);
        $categoriaImprevisto = floatval($valoresCategoria[6]);
        $categoriaOutras = floatval($valoresCategoria[7]);

        $resumo = [
            'total_receitas' => $resumoDasReceitas,
            'total_despesas' => $resumoDasDespesas,
            'saldo_final' => $resumoDasReceitas - $resumoDasDespesas,
            'total_alimentacao' => $categoriaAlimentacao,
            'total_saude' => $categoriaSaude,
            'total_moradia' => $categoriaMoradia,
            'total_transporte' => $categoriaTransporte,
            'total_educacao' => $categoriaEducacao,
            'total_lazer' => $categoriaLazer,
            'total_imprevisto' => $categoriaImprevisto,
            'total_outras' => $categoriaOutras,
        ];

        return $resumo;
    }
}