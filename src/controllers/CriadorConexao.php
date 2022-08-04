<?php

namespace Deivz\ApiRestControleFinanceiro\controllers;

use PDO;

class CriadorConexao
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $name
    ) {
    }

    public function conectar(): PDO
    {
        return new PDO("pgsql:" . sprintf(
            "host=%s;port=%s;user=%s;password=%s;dbname=%s",
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->name
        ), $this->user, $this->password, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]);
    }
}
