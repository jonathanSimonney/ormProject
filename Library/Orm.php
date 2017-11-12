<?php

namespace Library;

use Doctrine\DBAL\Exception\ConnectionException;

class Orm
{
//utiliser les __get pour ne faire les requêtes pour les entités liées QUE lorsque celles-ci sont nécessaires.
// (Renvoyer une entité où on aurait réécrit le __get?

    private $dbalConn;

    public function __construct($publicConfig, $privateConfig)
    {
        if ($publicConfig['language'] !== 'mySql'){
            throw new \Exception("No other language than mySql is currently supported.");
        }
        if ($publicConfig['use_strict'] !== true){
            throw new \Exception("The orm can't currently be configured to automatically add entity or relation to the database.");
        }
        if ($publicConfig['entity_config'] !== 'annotation'){
            throw new \Exception("No other entity configuration can currently be done without annotation.");
        }
        if ($publicConfig['from_db_to_entity'] !== false){
            throw new \Exception("The db can't currently serve as model for the entities.");
        }

        //todo use the $publicConfig['entity_folder_path'] to fetch the entities data.

        $config = new \Doctrine\DBAL\Configuration();
//..
        $privateConfig['driver'] = 'pdo_mysql';//todo set this with the publicConfig language.

        $this->dbalConn = \Doctrine\DBAL\DriverManager::getConnection($privateConfig, $config);

        try{
            $this->dbalConn->connect();
        }catch (ConnectionException $exc){
            throw new \InvalidArgumentException("Invalid db connection parameters given");
        }
    }
}
