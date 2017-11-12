<?php

namespace Library;

class Orm
{
//utiliser les __get pour ne faire les requêtes pour les entités liées QUE lorsque celles-ci sont nécessaires.
// (Renvoyer une entité où on aurait réécrit le __get?

    public function __construct($publicConfig, $privateConfig)
    {
        var_dump($publicConfig);
        var_dump($privateConfig);
    }
}