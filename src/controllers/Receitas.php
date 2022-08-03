<?php

namespace Deivz\ApiRestControleFinanceiro\controllers;

class Receitas
{
    public function processarRequisicao($metodo)
    {
        switch ($metodo) {
            case 'GET':
                var_dump($metodo);
                break;
            
            case 'POST':
                var_dump($metodo);
                break;
            
            default:
                echo "chamou o default";
                break;
        }
    }
}