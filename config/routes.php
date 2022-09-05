<?php

use Deivz\ApiRestControleFinanceiro\controllers\Despesas;
use Deivz\ApiRestControleFinanceiro\controllers\Receitas;
use Deivz\ApiRestControleFinanceiro\controllers\Resumo;

$rotas = [
    'receitas' => Receitas::class,
    'despesas' => Despesas::class,
    'resumo' => Resumo::class
];

return $rotas;