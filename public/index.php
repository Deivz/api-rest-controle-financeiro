<?php

declare(strict_types=1);

use Deivz\ApiRestControleFinanceiro\controllers\CriadorConexao;

require __DIR__ . '/../vendor/autoload.php';

set_error_handler("Deivz\ApiRestControleFinanceiro\helpers\ErrorHandler::handleError");
set_exception_handler("Deivz\ApiRestControleFinanceiro\helpers\ErrorHandler::handleException");


if (file_exists(__DIR__ . '/../.env')) {
   $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ .'/..');
   $dotenv->load();
}

header('Content-type: application/json; charset = UTF-8');

$partes = explode('/', $_SERVER['REQUEST_URI']);

$url = explode('?', $partes[1]);
$rota = $url[0];
$query = $url[1] ?? null;
$idOuAno = $partes[2] ?? null;
$mes = $partes[3] ?? null;

$rotas = require __DIR__ . '/../config/routes.php';

if (!array_key_exists($rota, $rotas)) {
   http_response_code(404);
   echo json_encode([
      'message' => 'Page not found',
      'code' => '404'
   ]);
}

$db = parse_url($_ENV["DATABASE_URL"]);
$db["path"] = ltrim($db["path"], "/");
$conexao = new CriadorConexao($db["host"], $db["port"], $db["user"], $db["pass"], $db["path"]);

$classeControladora = $rotas[$rota];
$controlador = new $classeControladora($conexao);
$controlador->processarRequisicao($_SERVER['REQUEST_METHOD'], $idOuAno, $mes, $query);