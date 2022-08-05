<?php

namespace Deivz\ApiRestControleFinanceiro\helpers;

use ErrorException;
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

    public static function handleError(
        int $errNumber,
        string $errString,
        string $errFile,
        int $errLine
    )
    {
        throw new ErrorException($errString, 0, $errNumber, $errFile, $errLine);
    }
}