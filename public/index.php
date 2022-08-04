<?php

declare(strict_types=1);

use Deivz\ApiRestControleFinanceiro\controllers\CriadorConexao;

require __DIR__ . '/../vendor/autoload.php';

set_exception_handler("Deivz\ApiRestControleFinanceiro\helpers\ErrorHandler::handleException");

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '\..');
$dotenv->load();

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

$db = parse_url($_ENV["DATABASE_URL"]);
$db["path"] = ltrim($db["path"], "/");
$conexao = new CriadorConexao($db["host"], $db["port"], $db["user"], $db["pass"], $db["path"]);

$classeControladora = $rotas[$caminho];
$controlador = new $classeControladora($conexao);
$controlador->processarRequisicao($_SERVER['REQUEST_METHOD']);
