<?php

namespace Library;

use Doctrine\Common\CommonException;
use Doctrine\DBAL\Exception\ConnectionException;

class Orm
{
// utiliser les __get pour ne faire les requêtes pour les entités liées QUE lorsque celles-ci sont nécessaires.
// (Renvoyer une entité où on aurait réécrit le __get?

    private $dbalConn;
    private $entitiesConfig;//array of entities with name, dbColumn, attributes and repository

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

        $this->setupEntitiesAnnotation($publicConfig['entity_folder_path']);
        $this->setupRepositoryAnnotations($publicConfig['repository_folder_path'], $publicConfig['entity_folder_path']);

        var_dump($this->entitiesConfig['entity\\Film']);

        $this->checkDbConn($privateConfig);
    }

    private function setupRepositoryAnnotations($repositoryFolder, $entityFolder)
    {
        $arrayRepository = scandir(__DIR__.'\\..\\'.$repositoryFolder);

        foreach ($arrayRepository as $repo){
            if ($this->endsWith($repo, '.php')) {
                $repoClass = $repositoryFolder.'\\'.substr($repo, 0, -4);
                $reflectionClass = new \ReflectionClass($repositoryFolder.'\\'.substr($repo, 0, -4));

                $classComment = $reflectionClass->getDocComment();
                $classAnnotation = $this->parseAnnotation($classComment);

                if (!isset($classAnnotation['Entity'])){
                    throw new \Exception('You must put an entity key for your repository class.');
                }

                $key = $entityFolder.'\\'.$classAnnotation['Entity'];
                if (!isset($this->entitiesConfig[$key])){
                    throw new \Exception('Class '.$classAnnotation['Entity'].' does not exist, please enter a valid class name for repository '.$repo);
                }

                $this->entitiesConfig[$key]['repository'] = $repoClass;
            }
        }
    }

    private function setupEntitiesAnnotation($entityFolder)
    {
        $arrayEntity = scandir(__DIR__.'\\..\\'.$entityFolder);

        foreach ($arrayEntity as $entity){
            if ($this->endsWith($entity, '.php')){
                $hasId = false;

                $singleEntityConfig = [];

                $reflectionClass = new \ReflectionClass($entityFolder.'\\'.substr($entity, 0, -4));
                $singleEntityConfig['name'] = $reflectionClass->getName();

                $classComment = $reflectionClass->getDocComment();
                $classAnnotation = $this->parseAnnotation($classComment);

                if (!isset($classAnnotation['Table'])){
                    throw new \Exception('The entities in your entity folder MUST be annotated with a @Table annotation.');
                }

                $singleEntityConfig['dbConfig'] = $classAnnotation['Table'];
                $singleEntityConfig['attributes'] = array();

                $properties = $reflectionClass->getProperties();
                foreach ($properties as $property){
                    $propComment = $property->getDocComment();
                    $attributeName = $property->getName();
                    $annotationData = $this->parseAnnotation($propComment);


                    if (!isset($annotationData['var'])){
                        continue;
                    }
                    try{
                        $attribute = new EntityAttribute($attributeName, $annotationData);
                        $singleEntityConfig['attributes'][$attributeName] = $attribute;

                        if ($attribute->getisId()){
                            $hasId = true;
                        }
                    }
                    catch (CommonException $c){
                        //do nothing, it is only temporary, and make sure the attribute isn't mapped..
                    }

                }

                if (!$hasId){
                    throw new \Exception('The entities MUST have at least an id field : no id field found for entity '.$singleEntityConfig['name']);
                }

                $singleEntityConfig['repository'] = BaseRepository::class;
                $this->entitiesConfig[$singleEntityConfig['name']] = $singleEntityConfig;
            }
        }
    }

    private function parseAnnotation(String $comment)
    {
        $final = [];
        $comment = preg_replace('/[\/\*]/', '', $comment);
        $ret = explode('@', $comment);
        unset($ret[0]);
        foreach ($ret as $elem){
            $elem = trim($elem);
            if (strpos($elem, ' ') !== false){
                $keyValue = explode(' ', $elem);
                $final[$keyValue[0]] = $keyValue[1];
            }else{
                $final[$elem] = '';
            }
        }

        return $final;
    }

    private function checkDbConn($privateConfig)
    {
        $config = new \Doctrine\DBAL\Configuration();

        $privateConfig['driver'] = 'pdo_mysql';//todo set this with the publicConfig language.

        $this->dbalConn = \Doctrine\DBAL\DriverManager::getConnection($privateConfig, $config);

        try{
            $this->dbalConn->connect();
        }catch (ConnectionException $exc){
            throw new \InvalidArgumentException("Invalid db connection parameters given");
        }
    }

    private function endsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return $length === 0 ||
        (substr($haystack, -$length) === $needle);
    }
}
