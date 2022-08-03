<?php

namespace Deivz\TratamentoArquivosCsv\infrastructure;

use PDO;

class Conexao
{
    public function __construct(
        private string $host,
        private string $name,
        private string $user,
        private string $password
    ) {
    }

    public function conectar(): PDO
    {
        $dsn = "pgsql:host={$this->host};
                          port=5432;
                          dbname={$this->name};
                          user={$this->user};
                          password={$this->password}";
        return new PDO($dsn, $this->user, $this->password);
    }
}
