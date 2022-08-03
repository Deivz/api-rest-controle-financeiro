<?php

namespace Deivz\ApiRestControleFinanceiro\helpers;

use Throwable;

class ErrorHandler
{
    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);
        echo json_encode([
            "código" => $exception->getCode(),
            "mensagem" => $exception->getMessage(),
            "arquivo" => $exception->getFile(),
            "linha" => $exception->getLine()
        ]);
    }
}