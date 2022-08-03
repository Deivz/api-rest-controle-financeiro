<?php

declare(strict_types=1);

// use Deivz\ApiRestControleFinanceiro\helpers\ErrorHandler;
use Deivz\TratamentoArquivosCsv\infrastructure\Conexao;

require __DIR__ . '/../vendor/autoload.php';

// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

set_exception_handler("Deivz\ApiRestControleFinanceiro\helpers\ErrorHandler::handleException");
echo "\n";
header('Content-type: application/json; charset = UTF-8');

$caminho = $_SERVER['PATH_INFO'];

$rotas = require __DIR__ . '/../config/routes.php';

if (!array_key_exists($caminho, $rotas)) {
   http_response_code(404);
   echo json_encode([
      'message' => 'Page not found',
      'code' => '404'
   ]);
}

$db = parse_url(getenv("DATABASE_URL"));
var_dump($db);
// $conexao = new Conexao(
//     $db["host"],
//     $db["port"],
//     $db["user"],
//     $db["pass"],
// );
// $conexao->conectar();

$classeControladora = $rotas[$caminho];
$controlador = new $classeControladora();
$controlador->processarRequisicao($_SERVER['REQUEST_METHOD']);
