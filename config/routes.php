<?php

use Deivz\ApiRestControleFinanceiro\controllers\Despesas;
use Deivz\ApiRestControleFinanceiro\controllers\Receitas;

$rotas = [
    'receitas' => Receitas::class,
    'despesas' => Despesas::class
];

return $rotas;